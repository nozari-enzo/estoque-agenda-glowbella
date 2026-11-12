const formAgenda = document.getElementById('form-agenda');
const alertaConflitos = document.getElementById('alerta-conflitos');

if (formAgenda) {
    glowFormulario(formAgenda, {
        api: formAgenda.dataset.api,
        method: formAgenda.dataset.metodo,
        dados: (form) => {
            const payload = {
                cd_cliente: form.cd_cliente.value,
                dt_agendamento: form.dt_agendamento.value,
                qtd_duracao_min: Number(form.qtd_duracao_min.value),
                ds_procedimento: form.ds_procedimento.value.trim(),
                vl_procedimento: form.vl_procedimento.value ? Number(form.vl_procedimento.value) : null
            };
            if (form.fg_status) {
                payload.fg_status = form.fg_status.value;
            }
            return payload;
        },
        aoSalvar: () => { window.location.href = glowUrl('agendamentos/?salvo=1'); },
        aoErrar: (erro) => {
            if (erro.status === 409 && erro.dados && erro.dados.conflitos) {
                const lista = document.getElementById('lista-conflitos');
                lista.replaceChildren(...erro.dados.conflitos.map((c) => {
                    const item = document.createElement('li');
                    item.textContent = `${c.nm_cliente}: ${c.dt_agendamento.slice(11, 16)} às ${c.dt_fim.slice(11, 16)}`;
                    return item;
                }));
                alertaConflitos.classList.remove('d-none');
            } else {
                alertaConflitos.classList.add('d-none');
            }
        }
    });
}

// Botão de cancelar rápido na lista (DELETE desativa/cancela)
async function cancelarAgendamento(botao) {
    if (!confirm('Deseja realmente cancelar este agendamento?')) return;
    botao.disabled = true;

    try {
        await glowApi('api/agenda.php?id=' + botao.dataset.cancelar, { method: 'DELETE' });
        window.location.reload();
    } catch (erro) {
        alert(erro.message);
        botao.disabled = false;
    }
}

document.querySelectorAll('[data-cancelar]').forEach((botao) => {
    botao.addEventListener('click', () => cancelarAgendamento(botao));
});