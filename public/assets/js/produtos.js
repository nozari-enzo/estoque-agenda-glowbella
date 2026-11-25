// Telas de produtos e categorias (public/produtos/). Padrão das telas: docs/front.md

/** Recarrega a página atual sem o aviso de "salvo" de antes. */
function recarregarSemAviso() {
    const endereco = new URL(window.location.href);
    endereco.searchParams.delete('salvo');
    window.location.href = endereco.toString();
}

/** Chama a API depois de confirmar; em caso de erro, mostra a mensagem. */
async function acaoComConfirmacao(botao, pergunta, caminho, opcoes) {
    if (pergunta && !confirm(pergunta)) return;

    botao.disabled = true;

    try {
        await glowApi(caminho, opcoes);
        recarregarSemAviso();
    } catch (erro) {
        alert(erro.message);
        botao.disabled = false;
    }
}

// ---------- Produto: cadastro e edição ----------
const formProduto = document.getElementById('form-produto');

if (formProduto) {
    glowFormulario(formProduto, {
        api: formProduto.dataset.api,
        method: formProduto.dataset.metodo,
        aoSalvar: () => { window.location.href = glowUrl(formProduto.dataset.voltar || 'produtos/?salvo=1'); },
    });
}

// ---------- Produto: desativar e reativar (lista) ----------
document.querySelectorAll('[data-desativar-produto]').forEach((botao) => {
    botao.addEventListener('click', () => acaoComConfirmacao(botao,
        'Desativar este produto? Ele sai do catálogo e do estoque, mas o histórico continua guardado.',
        'api/produtos.php?id=' + botao.dataset.desativarProduto, { method: 'DELETE' }));
});

document.querySelectorAll('[data-reativar-produto]').forEach((botao) => {
    botao.addEventListener('click', () => acaoComConfirmacao(botao, 'Reativar este produto?',
        'api/produtos.php?id=' + botao.dataset.reativarProduto, { method: 'PUT', body: { fg_status: 1 } }));
});

// ---------- Categorias ----------
const formCategoria = document.getElementById('form-categoria');

if (formCategoria) {
    glowFormulario(formCategoria, {
        api: formCategoria.dataset.api,
        method: formCategoria.dataset.metodo,
        aoSalvar: () => recarregarSemAviso(),
    });
}

document.querySelectorAll('[data-renomear-categoria]').forEach((botao) => {
    botao.addEventListener('click', async () => {
        const nome = prompt('Novo nome da categoria:', botao.dataset.nome);
        if (nome === null || nome.trim() === '' || nome.trim() === botao.dataset.nome) return;

        await acaoComConfirmacao(botao, null, 'api/categorias.php?id=' + botao.dataset.renomearCategoria,
            { method: 'PUT', body: { nm_categoria: nome.trim() } });
    });
});

document.querySelectorAll('[data-desativar-categoria]').forEach((botao) => {
    botao.addEventListener('click', () => acaoComConfirmacao(botao, 'Desativar esta categoria?',
        'api/categorias.php?id=' + botao.dataset.desativarCategoria, { method: 'DELETE' }));
});

document.querySelectorAll('[data-reativar-categoria]').forEach((botao) => {
    botao.addEventListener('click', () => acaoComConfirmacao(botao, 'Reativar esta categoria?',
        'api/categorias.php?id=' + botao.dataset.reativarCategoria, { method: 'PUT', body: { fg_status: 1 } }));
});
