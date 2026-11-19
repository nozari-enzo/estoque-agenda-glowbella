// Tela de estoque (public/estoque/). Padrão das telas: docs/front.md

const formMovimentacao = document.getElementById('form-movimentacao');

if (formMovimentacao) {
    glowFormulario(formMovimentacao, {
        api: 'api/movimentacoes.php',
        method: 'POST',
        dados: (form) => ({
            cd_produto: form.cd_produto.value,
            fg_tipo: form.querySelector('[name="fg_tipo"]:checked').value,
            qtd_movimentada: form.qtd_movimentada.value,
            ds_motivo: form.ds_motivo.value.trim(),
        }),
        aoSalvar: () => {
            const endereco = new URL(window.location.href);
            endereco.searchParams.set('salvo', '1');
            endereco.hash = '';
            window.location.href = endereco.toString();
        },
    });

    // Botões "Entrada" / "Saída" da lista: preenchem o formulário e levam até ele
    document.querySelectorAll('[data-movimentar]').forEach((botao) => {
        botao.addEventListener('click', () => {
            formMovimentacao.cd_produto.value = botao.dataset.movimentar;
            document.getElementById(botao.dataset.tipo === 'saida' ? 'tipo-saida' : 'tipo-entrada').checked = true;
            document.getElementById('movimentar').scrollIntoView({ behavior: 'smooth', block: 'start' });
            formMovimentacao.qtd_movimentada.focus({ preventScroll: true });
        });
    });
}
