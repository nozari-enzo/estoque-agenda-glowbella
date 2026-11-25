// Tela de estoque (public/estoque/). Padrão das telas: docs/front.md

const formMovimentacao = document.getElementById('form-movimentacao');

if (formMovimentacao) {
    // ---------- Campo "Produto": digitar para buscar ----------
    // Entrada e saída só aceitam produto já cadastrado. Na entrada, se o nome digitado
    // não existir, aparece o atalho "Cadastrar novo produto" (com o nome já preenchido).
    const produtos = JSON.parse(document.getElementById('dados-produtos').textContent);
    const campoBusca = document.getElementById('busca-produto');
    const listaSugestoes = document.getElementById('sugestoes-produto');
    const textoEscolhido = document.getElementById('produto-escolhido');
    const avisoNaoCadastrado = document.getElementById('produto-nao-cadastrado');
    const linkNovoProduto = document.getElementById('link-novo-produto');
    const MAX_SUGESTOES = 8;

    let produtoEscolhido = null;
    let valorEditadoAMao = false;
    let sugestoes = [];
    let destacada = -1;

    const normalizar = (texto) => texto.toLocaleLowerCase('pt-BR').normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
    const tipoAtual = () => formMovimentacao.querySelector('[name="fg_tipo"]:checked').value;
    const unidades = (qtd) => `${qtd} ${qtd === 1 ? 'unidade' : 'unidades'} em estoque`;

    function escolherProduto(produto) {
        produtoEscolhido = produto;
        campoBusca.value = produto ? produto.nome : campoBusca.value;
        campoBusca.classList.remove('is-invalid');
        textoEscolhido.textContent = produto ? unidades(produto.qtd) : '';
        fecharSugestoes();
        atualizarAvisoNaoCadastrado();
        valorEditadoAMao = false;
        atualizarValorVenda();
    }

    function fecharSugestoes() {
        listaSugestoes.classList.add('d-none');
        listaSugestoes.replaceChildren();
        campoBusca.setAttribute('aria-expanded', 'false');
        sugestoes = [];
        destacada = -1;
    }

    function produtosQueCombinam(termo) {
        const busca = normalizar(termo);
        if (!busca) return produtos.slice(0, MAX_SUGESTOES);
        return produtos.filter((produto) => normalizar(produto.nome).includes(busca)).slice(0, MAX_SUGESTOES);
    }

    function mostrarSugestoes() {
        const saida = tipoAtual() === 'saida';
        sugestoes = produtosQueCombinam(campoBusca.value);
        destacada = -1;
        listaSugestoes.replaceChildren();

        sugestoes.forEach((produto, indice) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action d-flex justify-content-between gap-2';
            item.setAttribute('role', 'option');
            item.dataset.indice = indice;
            // Na saída, produto sem estoque não pode ser escolhido
            item.disabled = saida && produto.qtd === 0;

            const nome = document.createElement('span');
            nome.textContent = produto.nome;
            const qtd = document.createElement('small');
            qtd.className = 'text-muted text-nowrap';
            qtd.textContent = produto.qtd === 0 ? 'sem estoque' : `${produto.qtd} em estoque`;
            item.append(nome, qtd);

            // mousedown (e não click) para escolher antes do campo perder o foco
            item.addEventListener('mousedown', (evento) => {
                evento.preventDefault();
                if (!item.disabled) escolherProduto(produto);
            });
            listaSugestoes.append(item);
        });

        if (!sugestoes.length) {
            // Na entrada, quem avisa é o "Produto não cadastrado" (com o link) logo abaixo do campo
            if (!saida) {
                fecharSugestoes();
                return;
            }
            const vazio = document.createElement('div');
            vazio.className = 'list-group-item text-muted small';
            vazio.textContent = 'Nenhum produto encontrado.';
            listaSugestoes.append(vazio);
        }

        listaSugestoes.classList.remove('d-none');
        campoBusca.setAttribute('aria-expanded', 'true');
    }

    function destacar(indice) {
        const itens = Array.from(listaSugestoes.querySelectorAll('button'));
        const habilitados = itens.filter((item) => !item.disabled);
        if (!habilitados.length) return;

        const posicaoAtual = habilitados.findIndex((item) => Number(item.dataset.indice) === destacada);
        const proxima = (posicaoAtual + indice + habilitados.length) % habilitados.length;
        destacada = Number(habilitados[proxima].dataset.indice);

        itens.forEach((item) => item.classList.toggle('active', Number(item.dataset.indice) === destacada));
        habilitados[proxima].scrollIntoView({ block: 'nearest' });
    }

    /** Se o texto digitado é exatamente o nome de um produto, considera escolhido. */
    function produtoPeloNomeDigitado() {
        const digitado = normalizar(campoBusca.value);
        const iguais = produtos.filter((produto) => normalizar(produto.nome) === digitado);
        return iguais.length === 1 ? iguais[0] : null;
    }

    function atualizarAvisoNaoCadastrado() {
        const digitado = campoBusca.value.trim();
        const mostrar = tipoAtual() === 'entrada' && !produtoEscolhido && digitado.length >= 2
            && produtosQueCombinam(digitado).length === 0;

        avisoNaoCadastrado.classList.toggle('d-none', !mostrar);
        if (mostrar) {
            linkNovoProduto.href = glowUrl('produtos/criar.php?origem=estoque&nm_produto=' + encodeURIComponent(digitado));
        }
    }

    campoBusca.addEventListener('input', () => {
        produtoEscolhido = null;
        textoEscolhido.textContent = '';
        atualizarValorVenda();
        mostrarSugestoes();
        atualizarAvisoNaoCadastrado();
    });

    campoBusca.addEventListener('focus', mostrarSugestoes);

    campoBusca.addEventListener('blur', () => {
        if (!produtoEscolhido) {
            const exato = produtoPeloNomeDigitado();
            if (exato) escolherProduto(exato);
        }
        fecharSugestoes();
    });

    campoBusca.addEventListener('keydown', (evento) => {
        const aberta = !listaSugestoes.classList.contains('d-none');

        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            evento.preventDefault();
            if (!aberta) mostrarSugestoes();
            destacar(evento.key === 'ArrowDown' ? 1 : -1);
        } else if (evento.key === 'Enter' && aberta && destacada >= 0) {
            evento.preventDefault();   // escolhe o produto em vez de enviar o formulário
            escolherProduto(sugestoes[destacada]);
        } else if (evento.key === 'Escape' && aberta) {
            fecharSugestoes();
        }
    });

    // ---------- Motivo e valor da venda ----------
    // Cada tipo tem a sua lista de motivos (vem do PHP: MOTIVOS_MOVIMENTACAO em app/services/estoque.php).
    // "Venda" (saída) pede o valor, que vai para o Financeiro.
    const campoMotivo = formMovimentacao.ds_motivo;
    const motivosPorTipo = JSON.parse(campoMotivo.dataset.motivos);
    const blocoMotivoOutro = document.getElementById('campo-motivo-outro');
    const blocoValorVenda = document.getElementById('campo-valor-venda');
    const campoValorVenda = formMovimentacao.vl_total;
    const MOTIVO_VENDA = 'Venda';

    function montarMotivos() {
        const anterior = campoMotivo.value;
        const opcoes = [['', 'Selecione...'], ...motivosPorTipo[tipoAtual()].map((motivo) => [motivo, motivo]), ['outro', 'Outro...']];

        campoMotivo.replaceChildren(...opcoes.map(([valor, texto]) => new Option(texto, valor)));
        // Mantém o motivo escolhido se ele também existe no novo tipo (ex.: "Ajuste de inventário")
        campoMotivo.value = opcoes.some(([valor]) => valor === anterior) ? anterior : '';
        atualizarCamposMotivo();
    }

    function ehVenda() {
        return tipoAtual() === 'saida' && campoMotivo.value === MOTIVO_VENDA;
    }

    function atualizarCamposMotivo() {
        blocoMotivoOutro.classList.toggle('d-none', campoMotivo.value !== 'outro');
        blocoValorVenda.classList.toggle('d-none', !ehVenda());
        atualizarValorVenda();
    }

    /** Sugere quantidade × preço de venda (até a pessoa mudar o valor à mão, ex.: desconto). */
    function atualizarValorVenda() {
        if (valorEditadoAMao || !ehVenda()) return;

        const quantidade = parseInt(formMovimentacao.qtd_movimentada.value, 10);
        const produto = produtoEscolhido || produtoPeloNomeDigitado();
        campoValorVenda.value = produto && quantidade > 0 ? (quantidade * produto.preco).toFixed(2) : '';
    }

    campoMotivo.addEventListener('change', atualizarCamposMotivo);
    formMovimentacao.qtd_movimentada.addEventListener('input', atualizarValorVenda);
    campoValorVenda.addEventListener('input', () => { valorEditadoAMao = campoValorVenda.value !== ''; });

    // Trocar entrada/saída: troca a lista de motivos, refaz o aviso e confere se o escolhido ainda pode sair
    formMovimentacao.querySelectorAll('[name="fg_tipo"]').forEach((opcao) => {
        opcao.addEventListener('change', () => {
            if (tipoAtual() === 'saida' && produtoEscolhido && produtoEscolhido.qtd === 0) {
                textoEscolhido.textContent = 'Este produto está sem estoque: não dá para registrar saída.';
            }
            montarMotivos();
            atualizarAvisoNaoCadastrado();
        });
    });

    // ---------- Envio ----------
    glowFormulario(formMovimentacao, {
        api: 'api/movimentacoes.php',
        method: 'POST',
        dados: (form) => {
            const produto = produtoEscolhido || produtoPeloNomeDigitado();

            if (!produto) {
                const mensagem = form.cd_produto.value.trim() === ''
                    ? 'Digite o nome do produto e escolha na lista.'
                    : (tipoAtual() === 'entrada'
                        ? 'Produto não cadastrado. Escolha um da lista ou cadastre um novo produto.'
                        : 'Escolha um produto da lista.');
                throw new GlowApiErro('Confira os campos destacados.', 422, { cd_produto: mensagem });
            }

            const outroMotivo = campoMotivo.value === 'outro';
            if (outroMotivo && form.ds_motivo_outro.value.trim() === '') {
                throw new GlowApiErro('Confira os campos destacados.', 422, { ds_motivo_outro: 'Escreva o motivo.' });
            }

            return {
                cd_produto: produto.id,
                fg_tipo: tipoAtual(),
                qtd_movimentada: form.qtd_movimentada.value,
                ds_motivo: outroMotivo ? form.ds_motivo_outro.value.trim() : campoMotivo.value,
                ...(ehVenda() ? { vl_total: campoValorVenda.value } : {}),
            };
        },
        aoSalvar: () => {
            const endereco = new URL(window.location.href);
            endereco.searchParams.set('salvo', '1');
            endereco.searchParams.delete('produto_novo');
            endereco.hash = '';
            window.location.href = endereco.toString();
        },
    });

    // Botões "Entrada" / "Saída" da lista: preenchem o formulário e levam até ele
    document.querySelectorAll('[data-movimentar]').forEach((botao) => {
        botao.addEventListener('click', () => {
            document.getElementById(botao.dataset.tipo === 'saida' ? 'tipo-saida' : 'tipo-entrada').checked = true;
            montarMotivos();
            escolherProduto(produtos.find((produto) => String(produto.id) === botao.dataset.movimentar) || null);
            document.getElementById('movimentar').scrollIntoView({ behavior: 'smooth', block: 'start' });
            formMovimentacao.qtd_movimentada.focus({ preventScroll: true });
        });
    });
}
