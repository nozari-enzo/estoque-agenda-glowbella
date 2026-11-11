<?php
/**
 * Regras da agenda: validação dos horários e conflito entre agendamentos.
 * Usa as funções de app/repositories/agenda/agenda.php.
 */

/**
 * Valida e normaliza os dados de um agendamento. Devolve [dados_limpos, erros].
 *
 * O fim pode vir de dois jeitos:
 *   "dt_fim": "2026-11-10 10:30"   ou   "qtd_duracao_min": 30
 */
function validar_agendamento(PDO $pdo, array $entrada): array
{
    $erros = [];

    $dados = [
        'cd_cliente'      => validar_inteiro($entrada, 'cd_cliente', 1, true, $erros, 'a cliente'),
        'dt_agendamento'  => validar_data_hora($entrada, 'dt_agendamento', true, $erros, 'o início do agendamento'),
        'dt_fim'          => null,
        'ds_procedimento' => validar_texto($entrada, 'ds_procedimento', 150, true, $erros, 'o procedimento'),
        'fg_status'       => validar_opcao($entrada, 'fg_status', ['agendado', 'concluido', 'cancelado'], 'agendado', $erros, 'a situação'),
    ];

    // Fim: pela duração (se veio) ou pela data/hora de fim
    $temDuracao = isset($entrada['qtd_duracao_min']) && $entrada['qtd_duracao_min'] !== '';
    if ($temDuracao) {
        $duracao = validar_inteiro($entrada, 'qtd_duracao_min', 1, true, $erros, 'a duração em minutos');

        if ($duracao !== null && $dados['dt_agendamento'] !== null) {
            $dados['dt_fim'] = date('Y-m-d H:i:s', strtotime($dados['dt_agendamento'] . " +$duracao minutes"));
        }
    } else {
        $dados['dt_fim'] = validar_data_hora($entrada, 'dt_fim', false, $erros, 'o fim do agendamento');

        if ($dados['dt_fim'] === null && !isset($erros['dt_fim'])) {
            $erros['dt_fim'] = 'Informe o fim do agendamento (dt_fim) ou a duração (qtd_duracao_min).';
        }
    }

    if ($dados['dt_agendamento'] !== null && $dados['dt_fim'] !== null && $dados['dt_fim'] <= $dados['dt_agendamento']) {
        $erros['dt_fim'] = 'O fim precisa ser depois do início.';
    }

    if ($dados['cd_cliente'] !== null && !isset($erros['cd_cliente'])) {
        $stmt = $pdo->prepare('SELECT 1 FROM cliente WHERE cd_cliente = ? AND fg_status = 1');
        $stmt->execute([$dados['cd_cliente']]);

        if (!$stmt->fetchColumn()) {
            $erros['cd_cliente'] = 'Cliente não encontrada ou inativa.';
        }
    }

    return [$dados, $erros];
}

/**
 * Salva o agendamento (novo, se $cdAgenda for null) conferindo conflito de horário.
 * Devolve ['cd_agenda' => ...] ou ['conflitos' => [...]] se o horário estiver ocupado.
 *
 * A conferência e a gravação ficam na mesma transação, com bloqueio da tabela,
 * para dois cadastros ao mesmo tempo não ocuparem o mesmo horário.
 */
function salvar_agendamento(PDO $pdo, array $dados, ?int $cdAgenda = null): array
{
    $pdo->beginTransaction();

    try {
        if ($dados['fg_status'] !== 'cancelado') {
            // Trava as linhas do período até o fim da transação
            $pdo->prepare("SELECT cd_agenda FROM agenda WHERE fg_status <> 'cancelado' AND dt_agendamento < ? AND dt_fim > ? FOR UPDATE")
                ->execute([$dados['dt_fim'], $dados['dt_agendamento']]);

            $conflitos = buscar_conflitos($pdo, $dados['dt_agendamento'], $dados['dt_fim'], $cdAgenda);

            if ($conflitos) {
                $pdo->rollBack();
                return ['conflitos' => $conflitos];
            }
        }

        if ($cdAgenda === null) {
            $cdAgenda = inserir_agendamento($pdo, $dados);
        } else {
            alterar_agendamento($pdo, $cdAgenda, $dados);
        }

        $pdo->commit();
        return ['cd_agenda' => $cdAgenda];
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}
