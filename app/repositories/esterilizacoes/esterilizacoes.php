<?php
/**
 * Acesso às tabelas esterilizacao (ciclos) e esterilizacao_produto (produtos de cada ciclo).
 *
 * Para CRIAR ou EDITAR um ciclo use salvar_esterilizacao() de
 * app/services/esterilizacao.php: ela grava o ciclo e os produtos juntos.
 */

/**
 * Situação do ciclo, calculada no banco:
 *   reprovado → algum indicador reprovado
 *   vencido   → validade já passou
 *   pendente  → algum indicador ainda pendente
 *   valido    → indicadores aprovados e dentro da validade
 */
const ESTERILIZACAO_SELECT = "
    SELECT e.cd_esterilizacao, e.nm_metodo, e.dt_esterilizacao, e.dt_validade_esterilizacao,
           e.fg_indicador_quimico, e.fg_indicador_biologico, e.fg_status,
           CASE
               WHEN e.fg_indicador_quimico = 'reprovado' OR e.fg_indicador_biologico = 'reprovado' THEN 'reprovado'
               WHEN e.dt_validade_esterilizacao < CURDATE() THEN 'vencido'
               WHEN e.fg_indicador_quimico = 'pendente' OR e.fg_indicador_biologico = 'pendente' THEN 'pendente'
               ELSE 'valido'
           END AS situacao
    FROM esterilizacao e
";

/**
 * Lista ciclos (do mais recente para o mais antigo), cada um com seus produtos.
 *   situacao   → valido, pendente, vencido ou reprovado
 *   cd_produto → só ciclos que levaram esse produto
 *   de / ate   → período da esterilização (AAAA-MM-DD, inclusive)
 *   fg_status  → 1 (padrão, ativos), 0 (inativos) ou 'todos'
 */
function listar_esterilizacoes(PDO $pdo, array $filtros = []): array
{
    $where = [];
    $parametros = [];

    $status = $filtros['fg_status'] ?? '1';
    if ($status !== 'todos') {
        $where[] = 'fg_status = ?';
        $parametros[] = (int) $status;
    }

    if (!empty($filtros['situacao'])) {
        $where[] = 'situacao = ?';
        $parametros[] = $filtros['situacao'];
    }

    if (!empty($filtros['de'])) {
        $where[] = 'dt_esterilizacao >= ?';
        $parametros[] = $filtros['de'];
    }

    if (!empty($filtros['ate'])) {
        $where[] = 'dt_esterilizacao <= ?';
        $parametros[] = $filtros['ate'];
    }

    if (!empty($filtros['cd_produto'])) {
        $where[] = 'cd_esterilizacao IN (SELECT cd_esterilizacao FROM esterilizacao_produto WHERE cd_produto = ?)';
        $parametros[] = $filtros['cd_produto'];
    }

    $sql = 'SELECT * FROM (' . ESTERILIZACAO_SELECT . ') ciclos'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY dt_esterilizacao DESC, cd_esterilizacao DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return anexar_produtos_dos_ciclos($pdo, $stmt->fetchAll());
}

function buscar_esterilizacao(PDO $pdo, int $cdEsterilizacao): ?array
{
    $stmt = $pdo->prepare(ESTERILIZACAO_SELECT . ' WHERE e.cd_esterilizacao = ?');
    $stmt->execute([$cdEsterilizacao]);
    $ciclo = $stmt->fetch();

    return $ciclo ? anexar_produtos_dos_ciclos($pdo, [$ciclo])[0] : null;
}

/** Busca os produtos de vários ciclos numa consulta só e coloca em "produtos". */
function anexar_produtos_dos_ciclos(PDO $pdo, array $ciclos): array
{
    if (!$ciclos) {
        return [];
    }

    $ids = array_column($ciclos, 'cd_esterilizacao');
    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare(
        "SELECT ep.cd_esterilizacao, ep.cd_produto, p.nm_produto, ep.qtd_produto
         FROM esterilizacao_produto ep
         JOIN produto p ON p.cd_produto = ep.cd_produto
         WHERE ep.cd_esterilizacao IN ($marcadores)
         ORDER BY p.nm_produto"
    );
    $stmt->execute($ids);

    $porCiclo = [];
    foreach ($stmt->fetchAll() as $item) {
        $porCiclo[$item['cd_esterilizacao']][] = [
            'cd_produto'  => (int) $item['cd_produto'],
            'nm_produto'  => $item['nm_produto'],
            'qtd_produto' => (int) $item['qtd_produto'],
        ];
    }

    return array_map(function (array $ciclo) use ($porCiclo): array {
        $ciclo['cd_esterilizacao'] = (int) $ciclo['cd_esterilizacao'];
        $ciclo['fg_status'] = (int) $ciclo['fg_status'];
        $ciclo['produtos'] = $porCiclo[$ciclo['cd_esterilizacao']] ?? [];
        return $ciclo;
    }, $ciclos);
}

function inserir_esterilizacao(PDO $pdo, array $dados): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO esterilizacao
            (nm_metodo, dt_esterilizacao, dt_validade_esterilizacao, fg_indicador_quimico, fg_indicador_biologico, fg_status)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $dados['nm_metodo'], $dados['dt_esterilizacao'], $dados['dt_validade_esterilizacao'],
        $dados['fg_indicador_quimico'], $dados['fg_indicador_biologico'], $dados['fg_status'],
    ]);

    return (int) $pdo->lastInsertId();
}

function alterar_esterilizacao(PDO $pdo, int $cdEsterilizacao, array $dados): void
{
    $stmt = $pdo->prepare(
        'UPDATE esterilizacao SET
            nm_metodo = ?, dt_esterilizacao = ?, dt_validade_esterilizacao = ?,
            fg_indicador_quimico = ?, fg_indicador_biologico = ?, fg_status = ?
         WHERE cd_esterilizacao = ?'
    );
    $stmt->execute([
        $dados['nm_metodo'], $dados['dt_esterilizacao'], $dados['dt_validade_esterilizacao'],
        $dados['fg_indicador_quimico'], $dados['fg_indicador_biologico'], $dados['fg_status'],
        $cdEsterilizacao,
    ]);
}

/** Troca a lista de produtos do ciclo pela lista informada. */
function substituir_produtos_do_ciclo(PDO $pdo, int $cdEsterilizacao, array $produtos): void
{
    $pdo->prepare('DELETE FROM esterilizacao_produto WHERE cd_esterilizacao = ?')->execute([$cdEsterilizacao]);

    $stmt = $pdo->prepare('INSERT INTO esterilizacao_produto (cd_esterilizacao, cd_produto, qtd_produto) VALUES (?, ?, ?)');
    foreach ($produtos as $produto) {
        $stmt->execute([$cdEsterilizacao, $produto['cd_produto'], $produto['qtd_produto']]);
    }
}

function desativar_esterilizacao(PDO $pdo, int $cdEsterilizacao): void
{
    $stmt = $pdo->prepare('UPDATE esterilizacao SET fg_status = 0 WHERE cd_esterilizacao = ?');
    $stmt->execute([$cdEsterilizacao]);
}
