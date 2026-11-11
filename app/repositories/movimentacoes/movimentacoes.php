<?php
/**
 * Acesso à tabela movimentacao (histórico de entradas e saídas de estoque).
 *
 * Para REGISTRAR uma movimentação use registrar_movimentacao() de
 * app/services/estoque.php: ela também atualiza o estoque do produto.
 */

const MOVIMENTACAO_COLUNAS = '
    m.cd_movimentacao, m.cd_produto, p.nm_produto, m.fg_tipo, m.qtd_movimentada,
    m.ds_motivo, m.dt_movimentacao, m.cd_usuario, u.nm_usuario
';

/**
 * Lista movimentações, das mais recentes para as mais antigas.
 *   cd_produto → só de um produto
 *   fg_tipo    → 'entrada' ou 'saida'
 *   de / ate   → período (AAAA-MM-DD, inclusive)
 *   limite     → quantidade máxima (padrão 100, máximo 500)
 */
function listar_movimentacoes(PDO $pdo, array $filtros = []): array
{
    $where = [];
    $parametros = [];

    if (!empty($filtros['cd_produto'])) {
        $where[] = 'm.cd_produto = ?';
        $parametros[] = $filtros['cd_produto'];
    }

    if (!empty($filtros['fg_tipo'])) {
        $where[] = 'm.fg_tipo = ?';
        $parametros[] = $filtros['fg_tipo'];
    }

    if (!empty($filtros['de'])) {
        $where[] = 'm.dt_movimentacao >= ?';
        $parametros[] = $filtros['de'] . ' 00:00:00';
    }

    if (!empty($filtros['ate'])) {
        $where[] = 'm.dt_movimentacao <= ?';
        $parametros[] = $filtros['ate'] . ' 23:59:59';
    }

    $limite = min(max((int) ($filtros['limite'] ?? 100), 1), 500);

    $sql = 'SELECT ' . MOVIMENTACAO_COLUNAS . '
            FROM movimentacao m
            JOIN produto p ON p.cd_produto = m.cd_produto
            LEFT JOIN admin u ON u.cd_usuario = m.cd_usuario'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . " ORDER BY m.dt_movimentacao DESC, m.cd_movimentacao DESC LIMIT $limite";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return array_map('formatar_movimentacao', $stmt->fetchAll());
}

function buscar_movimentacao(PDO $pdo, int $cdMovimentacao): ?array
{
    $stmt = $pdo->prepare(
        'SELECT ' . MOVIMENTACAO_COLUNAS . '
         FROM movimentacao m
         JOIN produto p ON p.cd_produto = m.cd_produto
         LEFT JOIN admin u ON u.cd_usuario = m.cd_usuario
         WHERE m.cd_movimentacao = ?'
    );
    $stmt->execute([$cdMovimentacao]);
    $movimentacao = $stmt->fetch();

    return $movimentacao ? formatar_movimentacao($movimentacao) : null;
}

function inserir_movimentacao(PDO $pdo, array $dados, ?int $cdUsuario): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO movimentacao (cd_produto, cd_usuario, fg_tipo, qtd_movimentada, ds_motivo)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$dados['cd_produto'], $cdUsuario, $dados['fg_tipo'], $dados['qtd_movimentada'], $dados['ds_motivo']]);

    return (int) $pdo->lastInsertId();
}

function formatar_movimentacao(array $movimentacao): array
{
    $movimentacao['cd_movimentacao'] = (int) $movimentacao['cd_movimentacao'];
    $movimentacao['cd_produto']      = (int) $movimentacao['cd_produto'];
    $movimentacao['qtd_movimentada'] = (int) $movimentacao['qtd_movimentada'];
    $movimentacao['cd_usuario']      = $movimentacao['cd_usuario'] === null ? null : (int) $movimentacao['cd_usuario'];

    return $movimentacao;
}
