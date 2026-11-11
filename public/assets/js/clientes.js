// Tela de clientes (public/clientes/). Padrão das telas: docs/front.md

// Formulário de cadastro e edição
const formCliente = document.getElementById('form-cliente');

if (formCliente) {
    glowFormulario(formCliente, {
        api: formCliente.dataset.api,
        method: formCliente.dataset.metodo,
        aoSalvar: () => { window.location.href = glowUrl('clientes/?salvo=1'); },
    });
}

// Desativar (lista de ativas) e reativar (lista de desativadas)
async function alterarSituacaoCliente(botao, opcoes, pergunta) {
    if (!confirm(pergunta)) return;

    botao.disabled = true;

    try {
        await glowApi('api/clientes.php?id=' + (botao.dataset.desativar || botao.dataset.reativar), opcoes);
        // Recarrega a lista (sem o aviso de "salvo" de antes)
        const endereco = new URL(window.location.href);
        endereco.searchParams.delete('salvo');
        window.location.href = endereco.toString();
    } catch (erro) {
        alert(erro.message);
        botao.disabled = false;
    }
}

document.querySelectorAll('[data-desativar]').forEach((botao) => {
    botao.addEventListener('click', () => alterarSituacaoCliente(botao, { method: 'DELETE' },
        'Desativar esta cliente? Ela sai da lista e não pode receber agendamentos novos.'));
});

document.querySelectorAll('[data-reativar]').forEach((botao) => {
    botao.addEventListener('click', () => alterarSituacaoCliente(botao, { method: 'PUT', body: { fg_status: 1 } },
        'Reativar esta cliente?'));
});
