<?php
/**
 * Acesso à tabela categoria.
 */

/**
 * Lista categorias, com a quantidade de produtos ativos de cada uma.
 *   busca     → parte do nome
 *   fg_status → 1 (padrão, ativas), 0 (inativas) ou 'todos'
 */
function listar_categorias(PDO $pdo, array $filtros = []): array
{
    $where = [];
    $parametros = [];

    $status = $filtros['fg_status'] ?? '1';
    if ($status !== 'todos') {
        $where[] = 'c.fg_status = ?';
        $parametros[] = (int) $status;
    }

    if (!empty($filtros['busca'])) {
        $where[] = 'c.nm_categoria LIKE ?';
        $parametros[] = '%' . $filtros['busca'] . '%';
    }

    $sql = 'SELECT c.cd_categoria, c.nm_categoria, c.fg_status,
                   (SELECT COUNT(*) FROM produto p WHERE p.cd_categoria = c.cd_categoria AND p.fg_status = 1) AS qtd_produtos_ativos
            FROM categoria c'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY c.nm_categoria';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return array_map('formatar_categoria', $stmt->fetchAll());
}

function buscar_categoria(PDO $pdo, int $cdCategoria): ?array
{
    $stmt = $pdo->prepare(
        'SELECT c.cd_categoria, c.nm_categoria, c.fg_status,
                (SELECT COUNT(*) FROM produto p WHERE p.cd_categoria = c.cd_categoria AND p.fg_status = 1) AS qtd_produtos_ativos
         FROM categoria c
         WHERE c.cd_categoria = ?'
    );
    $stmt->execute([$cdCategoria]);
    $categoria = $stmt->fetch();

    return $categoria ? formatar_categoria($categoria) : null;
}

function criar_categoria(PDO $pdo, array $dados): int
{
    $stmt = $pdo->prepare('INSERT INTO categoria (nm_categoria) VALUES (?)');
    $stmt->execute([$dados['nm_categoria']]);

    return (int) $pdo->lastInsertId();
}

function atualizar_categoria(PDO $pdo, int $cdCategoria, array $dados): void
{
    $stmt = $pdo->prepare('UPDATE categoria SET nm_categoria = ?, fg_status = ? WHERE cd_categoria = ?');
    $stmt->execute([$dados['nm_categoria'], $dados['fg_status'], $cdCategoria]);
}

function desativar_categoria(PDO $pdo, int $cdCategoria): void
{
    $stmt = $pdo->prepare('UPDATE categoria SET fg_status = 0 WHERE cd_categoria = ?');
    $stmt->execute([$cdCategoria]);
}

/**
 * Valida os dados. Devolve [dados_limpos, erros].
 * $cdCategoria: na edição, o código da própria categoria (para a checagem de nome repetido).
 */
function validar_categoria(PDO $pdo, array $entrada, ?int $cdCategoria = null): array
{
    $erros = [];

    $dados = [
        'nm_categoria' => validar_texto($entrada, 'nm_categoria', 60, true, $erros, 'o nome da categoria'),
        'fg_status'    => (int) validar_opcao($entrada, 'fg_status', ['0', '1'], '1', $erros, 'a situação'),
    ];

    if ($dados['nm_categoria'] !== null) {
        $stmt = $pdo->prepare('SELECT 1 FROM categoria WHERE nm_categoria = ? AND cd_categoria <> ?');
        $stmt->execute([$dados['nm_categoria'], $cdCategoria ?? 0]);

        if ($stmt->fetchColumn()) {
            $erros['nm_categoria'] = 'Já existe uma categoria com esse nome.';
        }
    }

    return [$dados, $erros];
}

function formatar_categoria(array $categoria): array
{
    $categoria['cd_categoria']        = (int) $categoria['cd_categoria'];
    $categoria['fg_status']           = (int) $categoria['fg_status'];
    $categoria['qtd_produtos_ativos'] = (int) $categoria['qtd_produtos_ativos'];

    return $categoria;
}
