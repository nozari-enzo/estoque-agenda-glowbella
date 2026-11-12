<?php
/**
 * API da agenda.
 *
 *   GET    api/agenda.php                  → lista (filtros: data, de, ate, cd_cliente, fg_status)
 *   GET    api/agenda.php?id=1             → um agendamento
 *   POST   api/agenda.php                  → agenda (confere conflito de horário)
 *   PUT    api/agenda.php?id=1             → edita / conclui (só os campos enviados)
 *   DELETE api/agenda.php?id=1             → cancela (fg_status = 'cancelado')
 *
 * Conflito de horário → 409 com a lista dos agendamentos que ocupam o horário.
 * Detalhes e exemplos: docs/api.md
 */
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/support/api.php';
require __DIR__ . '/../../app/repositories/agenda/agenda.php';
require __DIR__ . '/../../app/services/agenda.php';

exigir_login();
exigir_csrf();

/** Responde 409 com os agendamentos que ocupam o horário. */
function responder_conflito(array $conflitos): void
{
    responder([
        'erro'      => 'Já existe agendamento nesse horário.',
        'campos'    => ['dt_agendamento' => 'Horário ocupado. Escolha outro horário.'],
        'conflitos' => $conflitos,
    ], 409);
}

switch (metodo()) {
    case 'GET':
        $id = id_da_url();

        if ($id !== null) {
            $agendamento = buscar_agendamento($pdo, $id);
            $agendamento ? responder($agendamento) : responder_erro('Agendamento não encontrado.', 404);
        }

        responder(listar_agenda($pdo, [
            'data'       => filtro_data('data'),
            'de'         => filtro_data('de'),
            'ate'        => filtro_data('ate'),
            'cd_cliente' => filtro_inteiro('cd_cliente'),
            'fg_status'  => filtro_opcao('fg_status', ['agendado', 'concluido', 'cancelado']),
        ]));

    case 'POST':
        [$dados, $erros] = validar_agendamento($pdo, ler_json());

        if ($erros) {
            responder_erro('Confira os campos do agendamento.', 422, $erros);
        }

        $resultado = salvar_agendamento($pdo, $dados);

        if (isset($resultado['conflitos'])) {
            responder_conflito($resultado['conflitos']);
        }

        responder(buscar_agendamento($pdo, $resultado['cd_agenda']), 201);

    case 'PUT':
        $id = exigir_id();
        $atual = buscar_agendamento($pdo, $id) ?? responder_erro('Agendamento não encontrado.', 404);
        $entrada = ler_json();

        // Mudou só o início com duração? Mantém a duração atual se não vier fim nem duração
        if (isset($entrada['dt_agendamento']) && !isset($entrada['dt_fim']) && !isset($entrada['qtd_duracao_min'])) {
            $entrada['qtd_duracao_min'] = (int) ((strtotime($atual['dt_fim']) - strtotime($atual['dt_agendamento'])) / 60);
        }

        $campos = ['cd_cliente', 'dt_agendamento', 'ds_procedimento', 'vl_procedimento', 'fg_status'];
        if (!isset($entrada['qtd_duracao_min'])) {
            $campos[] = 'dt_fim';
        }

        [$dados, $erros] = validar_agendamento($pdo, mesclar_edicao($atual, $entrada, $campos));

        if ($erros) {
            responder_erro('Confira os campos do agendamento.', 422, $erros);
        }

        $resultado = salvar_agendamento($pdo, $dados, $id);

        if (isset($resultado['conflitos'])) {
            responder_conflito($resultado['conflitos']);
        }

        responder(buscar_agendamento($pdo, $id));

    case 'DELETE':
        $id = exigir_id();
        buscar_agendamento($pdo, $id) ?? responder_erro('Agendamento não encontrado.', 404);

        cancelar_agendamento($pdo, $id);
        responder(['ok' => true]);

    default:
        metodo_nao_permitido(['GET', 'POST', 'PUT', 'DELETE']);
}
