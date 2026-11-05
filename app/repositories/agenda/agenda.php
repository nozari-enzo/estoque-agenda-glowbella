<?php
/**
 * Acesso à tabela agenda.
 */

const AGENDA_COLUNAS = '
    a.cd_agenda, a.cd_cliente, c.nm_cliente, c.nu_telefone,
    a.dt_agendamento, a.dt_fim, a.ds_procedimento, a.fg_status
';

/**
 * Lista agendamentos, do mais cedo para o mais tarde.
 *   data       → um dia (AAAA-MM-DD)
 *   de / ate   → um período (AAAA-MM-DD, inclusive)
 *   cd_cliente → só de uma cliente
 *   fg_status  → agendado, concluido ou cancelado (padrão: todos)
 */
function listar_agenda(PDO $pdo, array $filtros = []): array
{
    $where = [];
    $parametros = [];

    if (!empty($filtros['data'])) {
        $where[] = 'DATE(a.dt_agendamento) = ?';
        $parametros[] = $filtros['data'];
    }

    if (!empty($filtros['de'])) {
        $where[] = 'a.dt_agendamento >= ?';
        $parametros[] = $filtros['de'] . ' 00:00:00';
    }

    if (!empty($filtros['ate'])) {
        $where[] = 'a.dt_agendamento <= ?';
        $parametros[] = $filtros['ate'] . ' 23:59:59';
    }

    if (!empty($filtros['cd_cliente'])) {
        $where[] = 'a.cd_cliente = ?';
        $parametros[] = $filtros['cd_cliente'];
    }

    if (!empty($filtros['fg_status'])) {
        $where[] = 'a.fg_status = ?';
        $parametros[] = $filtros['fg_status'];
    }

    $sql = 'SELECT ' . AGENDA_COLUNAS . '
            FROM agenda a
            JOIN cliente c ON c.cd_cliente = a.cd_cliente'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY a.dt_agendamento';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return array_map('formatar_agendamento', $stmt->fetchAll());
}

function buscar_agendamento(PDO $pdo, int $cdAgenda): ?array
{
    $stmt = $pdo->prepare(
        'SELECT ' . AGENDA_COLUNAS . '
         FROM agenda a
         JOIN cliente c ON c.cd_cliente = a.cd_cliente
         WHERE a.cd_agenda = ?'
    );
    $stmt->execute([$cdAgenda]);
    $agendamento = $stmt->fetch();

    return $agendamento ? formatar_agendamento($agendamento) : null;
}

/**
 * Agendamentos não cancelados que se sobrepõem ao horário informado.
 * $ignorar: na edição, o próprio agendamento (não conflita com ele mesmo).
 */
function buscar_conflitos(PDO $pdo, string $inicio, string $fim, ?int $ignorar = null): array
{
    $stmt = $pdo->prepare(
        "SELECT " . AGENDA_COLUNAS . "
         FROM agenda a
         JOIN cliente c ON c.cd_cliente = a.cd_cliente
         WHERE a.fg_status <> 'cancelado'
           AND a.dt_agendamento < ?
           AND a.dt_fim > ?
           AND a.cd_agenda <> ?
         ORDER BY a.dt_agendamento"
    );
    $stmt->execute([$fim, $inicio, $ignorar ?? 0]);

    return array_map('formatar_agendamento', $stmt->fetchAll());
}

function inserir_agendamento(PDO $pdo, array $dados): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO agenda (cd_cliente, dt_agendamento, dt_fim, ds_procedimento, fg_status)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$dados['cd_cliente'], $dados['dt_agendamento'], $dados['dt_fim'], $dados['ds_procedimento'], $dados['fg_status']]);

    return (int) $pdo->lastInsertId();
}

function alterar_agendamento(PDO $pdo, int $cdAgenda, array $dados): void
{
    $stmt = $pdo->prepare(
        'UPDATE agenda SET cd_cliente = ?, dt_agendamento = ?, dt_fim = ?, ds_procedimento = ?, fg_status = ?
         WHERE cd_agenda = ?'
    );
    $stmt->execute([$dados['cd_cliente'], $dados['dt_agendamento'], $dados['dt_fim'], $dados['ds_procedimento'], $dados['fg_status'], $cdAgenda]);
}

function cancelar_agendamento(PDO $pdo, int $cdAgenda): void
{
    $stmt = $pdo->prepare("UPDATE agenda SET fg_status = 'cancelado' WHERE cd_agenda = ?");
    $stmt->execute([$cdAgenda]);
}

function formatar_agendamento(array $agendamento): array
{
    $agendamento['cd_agenda']  = (int) $agendamento['cd_agenda'];
    $agendamento['cd_cliente'] = (int) $agendamento['cd_cliente'];

    return $agendamento;
}
