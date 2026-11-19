// Tela de esterilização (public/esterilizacao/). Padrão das telas: docs/front.md

const formCiclo = document.getElementById('form-ciclo');

if (formCiclo) {
    const listaItens = document.getElementById('itens-ciclo');

    // Primeira linha serve de modelo para as novas
    const modeloItem = listaItens.querySelector('[data-item-ciclo]').cloneNode(true);

    function atualizarBotoesRemover() {
        const linhas = listaItens.querySelectorAll('[data-item-ciclo]');
        linhas.forEach((linha) => { linha.querySelector('[data-remover-item]').disabled = linhas.length === 1; });
    }

    document.getElementById('adicionar-item').addEventListener('click', () => {
        const nova = modeloItem.cloneNode(true);
        nova.querySelector('[data-campo="cd_produto"]').value = '';
        nova.querySelector('[data-campo="qtd_produto"]').value = '1';
        listaItens.appendChild(nova);
        atualizarBotoesRemover();
        nova.querySelector('select').focus();
    });

    listaItens.addEventListener('click', (evento) => {
        const botao = evento.target.closest('[data-remover-item]');
        if (botao) {
            botao.closest('[data-item-ciclo]').remove();
            atualizarBotoesRemover();
        }
    });

    atualizarBotoesRemover();

    glowFormulario(formCiclo, {
        api: formCiclo.dataset.api,
        method: formCiclo.dataset.metodo,
        dados: (form) => ({
            nm_metodo: form.nm_metodo.value.trim(),
            dt_esterilizacao: form.dt_esterilizacao.value,
            dt_validade_esterilizacao: form.dt_validade_esterilizacao.value,
            fg_indicador_quimico: form.fg_indicador_quimico.value,
            fg_indicador_biologico: form.fg_indicador_biologico.value,
            // Linhas sem produto escolhido são ignoradas
            produtos: Array.from(listaItens.querySelectorAll('[data-item-ciclo]'))
                .map((linha) => ({
                    cd_produto: linha.querySelector('[data-campo="cd_produto"]').value,
                    qtd_produto: linha.querySelector('[data-campo="qtd_produto"]').value,
                }))
                .filter((item) => item.cd_produto !== ''),
        }),
        aoSalvar: () => { window.location.href = glowUrl('esterilizacao/?salvo=1'); },
    });
}

// Excluir ciclo da lista (DELETE desativa; o registro continua no banco)
document.querySelectorAll('[data-desativar-ciclo]').forEach((botao) => {
    botao.addEventListener('click', async () => {
        if (!confirm('Excluir este ciclo da lista?')) return;
        botao.disabled = true;

        try {
            await glowApi('api/esterilizacoes.php?id=' + botao.dataset.desativarCiclo, { method: 'DELETE' });
            const endereco = new URL(window.location.href);
            endereco.searchParams.delete('salvo');
            window.location.href = endereco.toString();
        } catch (erro) {
            alert(erro.message);
            botao.disabled = false;
        }
    });
});
