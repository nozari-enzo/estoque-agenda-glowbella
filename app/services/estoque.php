<?php
/**
 * Regras de estoque: toda mudança em produto.qtd_produto passa por aqui,
 * junto com o registro em movimentacao (na mesma transação).
 * Usa as funções de app/repositories/movimentacoes/movimentacoes.php.
 */

/** Valida uma movimentação vinda da API. Devolve [dados_limpos, erros]. */
function validar_movimentacao(PDO $pdo, array $entrada): array
{
    $erros = [];

    $dados = [
        'cd_produto'      => validar_inteiro($entrada, 'cd_produto', 1, true, $erros, 'o produto'),
        'fg_tipo'         => validar_opcao($entrada, 'fg_tipo', ['entrada', 'saida'], null, $erros, 'o tipo'),
        'qtd_movimentada' => validar_inteiro($entrada, 'qtd_movimentada', 1, true, $erros, 'a quantidade'),
        'ds_motivo'       => validar_texto($entrada, 'ds_motivo', 150, false, $erros, 'o motivo'),
    ];

    if ($dados['cd_produto'] !== null && !isset($erros['cd_produto'])) {
        $stmt = $pdo->prepare('SELECT 1 FROM produto WHERE cd_produto = ? AND fg_status = 1');
        $stmt->execute([$dados['cd_produto']]);

        if (!$stmt->fetchColumn()) {
            $erros['cd_produto'] = 'Produto não encontrado ou inativo.';
        }
    }

    return [$dados, $erros];
}

/**
 * Registra a movimentação e atualiza o estoque do produto.
 * Devolve ['cd_movimentacao' => ..., 'qtd_produto' => novo estoque]
 * ou ['estoque_insuficiente' => estoque atual] se a saída for maior que o estoque.
 */
function registrar_movimentacao(PDO $pdo, array $dados, ?int $cdUsuario): array
{
    $pdo->beginTransaction();

    try {
        if ($dados['fg_tipo'] === 'entrada') {
            $stmt = $pdo->prepare('UPDATE produto SET qtd_produto = qtd_produto + ? WHERE cd_produto = ?');
            $stmt->execute([$dados['qtd_movimentada'], $dados['cd_produto']]);
        } else {
            // A condição qtd_produto >= ? impede o estoque de ficar negativo,
            // mesmo com duas saídas ao mesmo tempo
            $stmt = $pdo->prepare('UPDATE produto SET qtd_produto = qtd_produto - ? WHERE cd_produto = ? AND qtd_produto >= ?');
            $stmt->execute([$dados['qtd_movimentada'], $dados['cd_produto'], $dados['qtd_movimentada']]);

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return ['estoque_insuficiente' => estoque_atual($pdo, $dados['cd_produto'])];
            }
        }

        $cdMovimentacao = inserir_movimentacao($pdo, $dados, $cdUsuario);
        $pdo->commit();

        return ['cd_movimentacao' => $cdMovimentacao, 'qtd_produto' => estoque_atual($pdo, $dados['cd_produto'])];
    } catch (Throwable $erro) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $erro;
    }
}

function estoque_atual(PDO $pdo, int $cdProduto): int
{
    $stmt = $pdo->prepare('SELECT qtd_produto FROM produto WHERE cd_produto = ?');
    $stmt->execute([$cdProduto]);

    return (int) $stmt->fetchColumn();
}
