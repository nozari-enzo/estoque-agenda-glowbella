// Scripts gerais da GlowBella.

// Pede confirmação antes de ações perigosas (ex.: excluir).
// Uso: <button type="submit" data-confirmar="Excluir este produto?">Excluir</button>
document.addEventListener('click', function (evento) {
    const elemento = evento.target.closest('[data-confirmar]');

    if (elemento && !confirm(elemento.dataset.confirmar)) {
        evento.preventDefault();
    }
});

// Busca e filtros das listas, feitos no navegador sobre as linhas que já estão na página.
// Uso: docs/front.md (seção "Busca e filtros").
document.querySelectorAll('[data-lista-filtro]').forEach(function (lista) {
    const campoBusca = document.getElementById(lista.dataset.busca);
    const filtroTipo = lista.dataset.tipo ? document.getElementById(lista.dataset.tipo) : null;
    const filtroStatus = lista.dataset.status ? document.getElementById(lista.dataset.status) : null;
    const itens = Array.from(lista.querySelectorAll('[data-item-lista]'));
    const mensagemVazia = lista.querySelector('[data-sem-resultados]');
    const contador = lista.querySelector('[data-contador-resultados]');

    function normalizar(texto) {
        return texto.toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function filtrarLista() {
        const termo = normalizar(campoBusca ? campoBusca.value.trim() : '');
        const tipo = filtroTipo ? filtroTipo.value : '';
        const status = filtroStatus ? filtroStatus.value : '';
        let exibidos = 0;

        itens.forEach(function (item) {
            const correspondeTexto = normalizar(item.textContent).includes(termo);
            const correspondeTipo = !tipo || item.dataset.tipoItem === tipo;
            const correspondeStatus = !status || item.dataset.statusItem === status;
            const visivel = correspondeTexto && correspondeTipo && correspondeStatus;

            item.classList.toggle('d-none', !visivel);
            if (visivel) exibidos += 1;
        });

        if (mensagemVazia) mensagemVazia.classList.toggle('d-none', exibidos > 0);
        if (contador) contador.textContent = `${exibidos} ${exibidos === 1 ? 'item encontrado' : 'itens encontrados'}.`;
    }

    if (campoBusca) campoBusca.addEventListener('input', filtrarLista);
    if (filtroTipo) filtroTipo.addEventListener('change', filtrarLista);
    if (filtroStatus) filtroStatus.addEventListener('change', filtrarLista);
    filtrarLista();
});

// Mostra os campos ilustrativos de lote e validade no cadastro de produto.
const controleLote = document.getElementById('controla-lote');
const controleValidade = document.getElementById('controla-validade');

if (controleLote && controleValidade) {
    const camposLote = document.getElementById('dados-lote');
    const campoValidade = document.getElementById('campo-validade');

    function atualizarCamposLote() {
        const loteAtivo = controleLote.checked;
        controleValidade.disabled = !loteAtivo;
        if (!loteAtivo) controleValidade.checked = false;
        camposLote.classList.toggle('d-none', !loteAtivo);
        campoValidade.classList.toggle('d-none', !loteAtivo || !controleValidade.checked);
    }

    controleLote.addEventListener('change', atualizarCamposLote);
    controleValidade.addEventListener('change', atualizarCamposLote);
    atualizarCamposLote();
}

/**
 * Chama uma API do sistema (public/api/) e devolve o JSON da resposta.
 * Já envia o token CSRF e trata o corpo como JSON.
 *
 *   const produtos = await glowApi('api/produtos.php?busca=argola');
 *   const novo = await glowApi('api/produtos.php', { method: 'POST', body: { nm_produto: 'Argola', vl_produto: 59.9, cd_categoria: 1 } });
 *   await glowApi('api/produtos.php?id=3', { method: 'DELETE' });
 *
 * Se a API responder com erro (4xx/5xx), lança um GlowApiErro com:
 *   erro.message → mensagem para mostrar à pessoa
 *   erro.status  → código HTTP (ex.: 422)
 *   erro.campos  → erros por campo (ex.: { nm_produto: 'Informe o nome do produto.' })
 *   erro.dados   → a resposta completa (ex.: erro.dados.conflitos na agenda)
 */
class GlowApiErro extends Error {
    constructor(mensagem, status, campos, dados) {
        super(mensagem);
        this.status = status;
        this.campos = campos || {};
        this.dados = dados || {};
    }
}

/**
 * Endereço completo de uma página do sistema (igual ao url() do PHP).
 *   window.location.href = glowUrl('clientes/?salvo=1');
 */
function glowUrl(caminho = '') {
    const base = document.querySelector('meta[name="base-url"]')?.content || '';
    return base + caminho.replace(/^\//, '');
}

async function glowApi(caminho, opcoes = {}) {
    const meta = (nome) => document.querySelector(`meta[name="${nome}"]`)?.content || '';
    const metodo = (opcoes.method || 'GET').toUpperCase();

    const resposta = await fetch(glowUrl(caminho), {
        method: metodo,
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            ...(opcoes.body !== undefined ? { 'Content-Type': 'application/json' } : {}),
            ...(metodo !== 'GET' ? { 'X-CSRF-Token': meta('csrf-token') } : {}),
        },
        body: opcoes.body !== undefined ? JSON.stringify(opcoes.body) : undefined,
    });

    const dados = await resposta.json().catch(() => null);

    if (!resposta.ok) {
        throw new GlowApiErro(dados?.erro || 'Não foi possível concluir a operação.', resposta.status, dados?.campos, dados);
    }

    return dados;
}

/**
 * Liga um <form> a uma API. Ao enviar: junta os campos pelo "name", chama a API
 * e, se der erro, mostra a mensagem embaixo de cada campo (padrão do Bootstrap).
 * Guia completo: docs/front.md
 *
 *   glowFormulario(document.getElementById('form-cliente'), {
 *       api: 'api/clientes.php',          // na edição: 'api/clientes.php?id=3'
 *       method: 'POST',                   // na edição: 'PUT'
 *       aoSalvar: (cliente) => { window.location.href = 'index.php?salvo=1'; },
 *   });
 *
 * Opcionais:
 *   dados:   (form) => ({ ... })   monta o corpo do jeito que quiser (padrão: todos os campos do form)
 *   aoErrar: (erro) => { ... }     roda depois de mostrar os erros (ex.: erro.dados.conflitos da agenda)
 *
 * A mensagem geral aparece no elemento com data-erro-formulario (se existir) ou num alert().
 */
function glowFormulario(form, opcoes) {
    const botao = form.querySelector('[type="submit"]');

    // Ao corrigir um campo, o aviso vermelho dele some
    form.addEventListener('input', function (evento) {
        evento.target.classList.remove('is-invalid');
    });

    form.addEventListener('submit', async function (evento) {
        evento.preventDefault();
        glowLimparErros(form);
        if (botao) botao.disabled = true;

        try {
            const corpo = opcoes.dados ? opcoes.dados(form) : glowDadosDoFormulario(form);
            const resposta = await glowApi(opcoes.api, { method: opcoes.method || 'POST', body: corpo });
            await opcoes.aoSalvar(resposta);
        } catch (erro) {
            const erroApi = erro instanceof GlowApiErro ? erro : new GlowApiErro('Não foi possível falar com o servidor.', 0);
            glowMostrarErros(form, erroApi);
            if (opcoes.aoErrar) opcoes.aoErrar(erroApi);
        } finally {
            if (botao) botao.disabled = false;
        }
    });
}

/** Campos do form como objeto: { nm_cliente: 'Ana', nu_telefone: '...' }. */
function glowDadosDoFormulario(form) {
    const dados = {};

    new FormData(form).forEach(function (valor, nome) {
        dados[nome] = typeof valor === 'string' ? valor.trim() : valor;
    });

    return dados;
}

/** Mostra os erros de um GlowApiErro no form: mensagem geral + erro de cada campo. */
function glowMostrarErros(form, erro) {
    const avisoGeral = form.querySelector('[data-erro-formulario]');
    let primeiroCampo = null;

    Object.entries(erro.campos).forEach(function ([nome, mensagem]) {
        const campo = form.querySelector(`[name="${nome}"]`);
        if (!campo) return;

        let aviso = campo.parentElement.querySelector(`.invalid-feedback[data-campo="${nome}"]`);
        if (!aviso) {
            aviso = document.createElement('div');
            aviso.className = 'invalid-feedback';
            aviso.dataset.campo = nome;
            campo.insertAdjacentElement('afterend', aviso);
        }

        aviso.textContent = mensagem;
        campo.classList.add('is-invalid');
        primeiroCampo = primeiroCampo || campo;
    });

    if (avisoGeral) {
        avisoGeral.textContent = erro.message;
        avisoGeral.classList.remove('d-none');
    } else {
        alert(erro.message);
    }

    if (primeiroCampo) primeiroCampo.focus();
}

/** Tira os avisos de erro do form. */
function glowLimparErros(form) {
    form.querySelectorAll('.is-invalid').forEach((campo) => campo.classList.remove('is-invalid'));

    const avisoGeral = form.querySelector('[data-erro-formulario]');
    if (avisoGeral) avisoGeral.classList.add('d-none');
}
