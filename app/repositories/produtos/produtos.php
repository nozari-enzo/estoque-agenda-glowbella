<?php
/**
 * Acesso à tabela produto. Todo SQL de produtos fica aqui.
 *
 * O estoque (qtd_produto) NÃO é alterado por estas funções, a não ser na
 * criação (estoque inicial). Depois disso, ele só muda por uma movimentação
 * de entrada/saída (tabela movimentacao), para manter o histórico.
 */

/** Colunas devolvidas pela API, com o nome da categoria e o alerta de estoque baixo. */
const PRODUTO_COLUNAS = '
    p.cd_produto, p.nm_produto, p.fg_finalidade, p.ds_lote,
    p.vl_compra, p.vl_produto, p.qtd_produto, p.qtd_estoque_minimo,
    p.dt_validade, p.cd_categoria, c.nm_categoria, p.fg_status,
    (p.qtd_produto < p.qtd_estoque_minimo) AS estoque_baixo
';

/**
 * Lista produtos com filtros opcionais:
 *   busca         → parte do nome ou do lote
 *   cd_categoria  → só de uma categoria
 *   fg_finalidade → 'venda' ou 'procedimento'
 *   fg_status     → 1 (padrão, ativos), 0 (inativos) ou 'todos'
 *   estoque_baixo → true para só os abaixo do mínimo
 */
function listar_produtos(PDO $pdo, array $filtros = []): array
{
    $where = [];
    $parametros = [];

    $status = $filtros['fg_status'] ?? 1;
    if ($status !== 'todos') {
        $where[] = 'p.fg_status = ?';
        $parametros[] = (int) $status;
    }

    if (!empty($filtros['busca'])) {
        $where[] = '(p.nm_produto LIKE ? OR p.ds_lote LIKE ?)';
        $termo = '%' . $filtros['busca'] . '%';
        array_push($parametros, $termo, $termo);
    }

    if (!empty($filtros['cd_categoria'])) {
        $where[] = 'p.cd_categoria = ?';
        $parametros[] = (int) $filtros['cd_categoria'];
    }

    if (!empty($filtros['fg_finalidade'])) {
        $where[] = 'p.fg_finalidade = ?';
        $parametros[] = $filtros['fg_finalidade'];
    }

    if (!empty($filtros['estoque_baixo'])) {
        $where[] = 'p.qtd_produto < p.qtd_estoque_minimo';
    }

    $sql = 'SELECT ' . PRODUTO_COLUNAS . '
            FROM produto p
            JOIN categoria c ON c.cd_categoria = p.cd_categoria'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY p.nm_produto';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return array_map('formatar_produto', $stmt->fetchAll());
}

/** Busca um produto pelo código, ou null se não existir. */
function buscar_produto(PDO $pdo, int $cdProduto): ?array
{
    $stmt = $pdo->prepare(
        'SELECT ' . PRODUTO_COLUNAS . '
         FROM produto p
         JOIN categoria c ON c.cd_categoria = p.cd_categoria
         WHERE p.cd_produto = ?'
    );
    $stmt->execute([$cdProduto]);
    $produto = $stmt->fetch();

    return $produto ? formatar_produto($produto) : null;
}

/**
 * Cria um produto. Se vier qtd_inicial > 0, registra também a movimentação
 * de entrada "Estoque inicial", na mesma transação.
 * Devolve o cd_produto criado.
 */
function criar_produto(PDO $pdo, array $dados, ?int $cdUsuario): int
{
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO produto
                (nm_produto, fg_finalidade, ds_lote, vl_compra, vl_produto,
                 qtd_produto, qtd_estoque_minimo, dt_validade, cd_categoria)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $dados['nm_produto'],
            $dados['fg_finalidade'],
            $dados['ds_lote'],
            $dados['vl_compra'],
            $dados['vl_produto'],
            $dados['qtd_inicial'],
            $dados['qtd_estoque_minimo'],
            $dados['dt_validade'],
            $dados['cd_categoria'],
        ]);
        $cdProduto = (int) $pdo->lastInsertId();

        if ($dados['qtd_inicial'] > 0) {
            $stmt = $pdo->prepare(
                "INSERT INTO movimentacao (cd_produto, cd_usuario, fg_tipo, qtd_movimentada, ds_motivo)
                 VALUES (?, ?, 'entrada', ?, 'Estoque inicial')"
            );
            $stmt->execute([$cdProduto, $cdUsuario, $dados['qtd_inicial']]);
        }

        $pdo->commit();
        return $cdProduto;
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}

/** Atualiza os dados cadastrais (não mexe no estoque). Devolve false se o produto não existe. */
function atualizar_produto(PDO $pdo, int $cdProduto, array $dados): bool
{
    $stmt = $pdo->prepare(
        'UPDATE produto SET
            nm_produto = ?, fg_finalidade = ?, ds_lote = ?, vl_compra = ?, vl_produto = ?,
            qtd_estoque_minimo = ?, dt_validade = ?, cd_categoria = ?, fg_status = ?
         WHERE cd_produto = ?'
    );
    $stmt->execute([
        $dados['nm_produto'],
        $dados['fg_finalidade'],
        $dados['ds_lote'],
        $dados['vl_compra'],
        $dados['vl_produto'],
        $dados['qtd_estoque_minimo'],
        $dados['dt_validade'],
        $dados['cd_categoria'],
        $dados['fg_status'],
        $cdProduto,
    ]);

    // rowCount() é 0 também quando nada mudou; por isso confere se existe
    return $stmt->rowCount() > 0 || produto_existe($pdo, $cdProduto);
}

/** Desativa o produto (fg_status = 0). Devolve false se não existe. */
function desativar_produto(PDO $pdo, int $cdProduto): bool
{
    $stmt = $pdo->prepare('UPDATE produto SET fg_status = 0 WHERE cd_produto = ?');
    $stmt->execute([$cdProduto]);

    return $stmt->rowCount() > 0 || produto_existe($pdo, $cdProduto);
}

function produto_existe(PDO $pdo, int $cdProduto): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM produto WHERE cd_produto = ?');
    $stmt->execute([$cdProduto]);

    return (bool) $stmt->fetchColumn();
}

/**
 * Valida e normaliza os dados vindos da API.
 * Devolve [dados_limpos, erros]. Se erros não estiver vazio, não salve.
 *
 * $criando = true  → aceita qtd_inicial (estoque inicial)
 * $criando = false → aceita fg_status (para reativar um produto)
 */
function validar_produto(PDO $pdo, array $entrada, bool $criando): array
{
    $erros = [];

    $dados = [
        'nm_produto'         => validar_texto($entrada, 'nm_produto', 120, true, $erros, 'o nome do produto'),
        'fg_finalidade'      => validar_opcao($entrada, 'fg_finalidade', ['venda', 'procedimento'], 'venda', $erros, 'a finalidade'),
        'ds_lote'            => validar_texto($entrada, 'ds_lote', 50, false, $erros, 'o lote'),
        'vl_compra'          => validar_valor($entrada, 'vl_compra', false, $erros, 'o valor de compra') ?? '0.00',
        'vl_produto'         => validar_valor($entrada, 'vl_produto', true, $erros, 'o valor de venda'),
        'qtd_estoque_minimo' => validar_inteiro($entrada, 'qtd_estoque_minimo', 0, false, $erros, 'o estoque mínimo') ?? 0,
        'dt_validade'        => validar_data($entrada, 'dt_validade', false, $erros, 'a validade'),
        'cd_categoria'       => validar_inteiro($entrada, 'cd_categoria', 1, true, $erros, 'a categoria'),
    ];

    if ($criando) {
        $dados['qtd_inicial'] = validar_inteiro($entrada, 'qtd_inicial', 0, false, $erros, 'a quantidade inicial') ?? 0;
    } else {
        $status = validar_opcao($entrada, 'fg_status', ['0', '1'], '1', $erros, 'a situação');
        $dados['fg_status'] = (int) $status;
    }

    if ($dados['cd_categoria'] !== null && !isset($erros['cd_categoria'])) {
        $stmt = $pdo->prepare('SELECT 1 FROM categoria WHERE cd_categoria = ? AND fg_status = 1');
        $stmt->execute([$dados['cd_categoria']]);

        if (!$stmt->fetchColumn()) {
            $erros['cd_categoria'] = 'Categoria não encontrada ou inativa.';
        }
    }

    return [$dados, $erros];
}

/** Converte os tipos para o JSON sair consistente (números como números). */
function formatar_produto(array $produto): array
{
    $produto['cd_produto']         = (int) $produto['cd_produto'];
    $produto['cd_categoria']       = (int) $produto['cd_categoria'];
    $produto['qtd_produto']        = (int) $produto['qtd_produto'];
    $produto['qtd_estoque_minimo'] = (int) $produto['qtd_estoque_minimo'];
    $produto['vl_compra']          = (float) $produto['vl_compra'];
    $produto['vl_produto']         = (float) $produto['vl_produto'];
    $produto['fg_status']          = (int) $produto['fg_status'];
    $produto['estoque_baixo']      = (bool) $produto['estoque_baixo'];

    return $produto;
}
