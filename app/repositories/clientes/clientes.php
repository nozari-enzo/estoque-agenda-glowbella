<?php
/**
 * Acesso à tabela cliente.
 */

/**
 * Lista clientes.
 *   busca     → parte do nome ou do telefone
 *   fg_status → 1 (padrão, ativas), 0 (inativas) ou 'todos'
 */
function listar_clientes(PDO $pdo, array $filtros = []): array
{
    $where = [];
    $parametros = [];

    $status = $filtros['fg_status'] ?? '1';
    if ($status !== 'todos') {
        $where[] = 'fg_status = ?';
        $parametros[] = (int) $status;
    }

    if (!empty($filtros['busca'])) {
        // Busca pelo nome ou pelos dígitos do telefone (ex.: "90000" ou "(11) 9000")
        $digitos = preg_replace('/\D/', '', $filtros['busca']);
        $condicoes = ['nm_cliente LIKE ?'];
        $parametros[] = '%' . $filtros['busca'] . '%';

        if ($digitos !== '') {
            $condicoes[] = "REPLACE(REPLACE(REPLACE(REPLACE(nu_telefone, '(', ''), ')', ''), ' ', ''), '-', '') LIKE ?";
            $parametros[] = '%' . $digitos . '%';
        }

        $where[] = '(' . implode(' OR ', $condicoes) . ')';
    }

    $sql = 'SELECT cd_cliente, nm_cliente, dt_nascimento, nu_telefone, fg_status
            FROM cliente'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY nm_cliente';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return array_map('formatar_cliente', $stmt->fetchAll());
}

function buscar_cliente(PDO $pdo, int $cdCliente): ?array
{
    $stmt = $pdo->prepare(
        'SELECT cd_cliente, nm_cliente, dt_nascimento, nu_telefone, fg_status
         FROM cliente
         WHERE cd_cliente = ?'
    );
    $stmt->execute([$cdCliente]);
    $cliente = $stmt->fetch();

    return $cliente ? formatar_cliente($cliente) : null;
}

function criar_cliente(PDO $pdo, array $dados): int
{
    $stmt = $pdo->prepare('INSERT INTO cliente (nm_cliente, dt_nascimento, nu_telefone) VALUES (?, ?, ?)');
    $stmt->execute([$dados['nm_cliente'], $dados['dt_nascimento'], $dados['nu_telefone']]);

    return (int) $pdo->lastInsertId();
}

function atualizar_cliente(PDO $pdo, int $cdCliente, array $dados): void
{
    $stmt = $pdo->prepare(
        'UPDATE cliente SET nm_cliente = ?, dt_nascimento = ?, nu_telefone = ?, fg_status = ?
         WHERE cd_cliente = ?'
    );
    $stmt->execute([$dados['nm_cliente'], $dados['dt_nascimento'], $dados['nu_telefone'], $dados['fg_status'], $cdCliente]);
}

function desativar_cliente(PDO $pdo, int $cdCliente): void
{
    $stmt = $pdo->prepare('UPDATE cliente SET fg_status = 0 WHERE cd_cliente = ?');
    $stmt->execute([$cdCliente]);
}

/**
 * Padroniza o telefone para "(11) 90000-0000" (celular) ou "(11) 3000-0000" (fixo).
 * Devolve null se não tiver 10 ou 11 dígitos.
 */
function formatar_telefone(string $telefone): ?string
{
    $digitos = preg_replace('/\D/', '', $telefone);

    return match (strlen($digitos)) {
        11      => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7)),
        10      => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6)),
        default => null,
    };
}

/**
 * Valida os dados. Devolve [dados_limpos, erros].
 * $cdCliente: na edição, o código da própria cliente (para a checagem de telefone repetido).
 */
function validar_cliente(PDO $pdo, array $entrada, ?int $cdCliente = null): array
{
    $erros = [];

    $dados = [
        'nm_cliente'    => validar_texto($entrada, 'nm_cliente', 120, true, $erros, 'o nome da cliente'),
        'dt_nascimento' => validar_data($entrada, 'dt_nascimento', false, $erros, 'a data de nascimento'),
        'nu_telefone'   => validar_texto($entrada, 'nu_telefone', 20, true, $erros, 'o telefone'),
        'fg_status'     => (int) validar_opcao($entrada, 'fg_status', ['0', '1'], '1', $erros, 'a situação'),
    ];

    if ($dados['dt_nascimento'] !== null && $dados['dt_nascimento'] > date('Y-m-d')) {
        $erros['dt_nascimento'] = 'A data de nascimento não pode ser no futuro.';
    }

    if ($dados['nu_telefone'] !== null) {
        $telefone = formatar_telefone($dados['nu_telefone']);

        if ($telefone === null) {
            $erros['nu_telefone'] = 'O telefone precisa ter DDD + número (10 ou 11 dígitos).';
        } else {
            $dados['nu_telefone'] = $telefone;

            $stmt = $pdo->prepare('SELECT 1 FROM cliente WHERE nu_telefone = ? AND cd_cliente <> ?');
            $stmt->execute([$telefone, $cdCliente ?? 0]);

            if ($stmt->fetchColumn()) {
                $erros['nu_telefone'] = 'Já existe uma cliente com esse telefone.';
            }
        }
    }

    return [$dados, $erros];
}

function formatar_cliente(array $cliente): array
{
    $cliente['cd_cliente'] = (int) $cliente['cd_cliente'];
    $cliente['fg_status']  = (int) $cliente['fg_status'];

    return $cliente;
}
