// Scripts gerais da GlowBella.

// Pede confirmação antes de ações perigosas (ex.: excluir).
// Uso: <button type="submit" data-confirmar="Excluir este produto?">Excluir</button>
document.addEventListener('click', function (evento) {
    const elemento = evento.target.closest('[data-confirmar]');

    if (elemento && !confirm(elemento.dataset.confirmar)) {
        evento.preventDefault();
    }
});

// Filtros locais para protótipos de listas. Não consultam nem alteram o banco.
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
        if (contador) contador.textContent = `${exibidos} ${exibidos === 1 ? 'item encontrado' : 'itens encontrados'} neste protótipo.`;
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
 */
class GlowApiErro extends Error {
    constructor(mensagem, status, campos) {
        super(mensagem);
        this.status = status;
        this.campos = campos || {};
    }
}

async function glowApi(caminho, opcoes = {}) {
    const meta = (nome) => document.querySelector(`meta[name="${nome}"]`)?.content || '';
    const metodo = (opcoes.method || 'GET').toUpperCase();

    const resposta = await fetch(meta('base-url') + caminho.replace(/^\//, ''), {
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
        throw new GlowApiErro(dados?.erro || 'Não foi possível concluir a operação.', resposta.status, dados?.campos);
    }

    return dados;
}
