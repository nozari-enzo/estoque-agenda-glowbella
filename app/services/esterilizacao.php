<?php
/**
 * Regras da esterilização: validação do ciclo e dos produtos, e gravação
 * dos dois juntos (mesma transação).
 * Usa as funções de app/repositories/esterilizacoes/esterilizacoes.php.
 */

const INDICADORES = ['pendente', 'aprovado', 'reprovado'];

/**
 * Valida um ciclo. Devolve [dados_limpos, erros].
 * $exigirProdutos: no POST a lista é obrigatória; no PUT só é validada se vier.
 */
function validar_esterilizacao(PDO $pdo, array $entrada, bool $exigirProdutos): array
{
    $erros = [];

    $dados = [
        'nm_metodo'                 => validar_texto($entrada, 'nm_metodo', 50, true, $erros, 'o método'),
        'dt_esterilizacao'          => validar_data($entrada, 'dt_esterilizacao', true, $erros, 'a data da esterilização'),
        'dt_validade_esterilizacao' => validar_data($entrada, 'dt_validade_esterilizacao', true, $erros, 'a validade'),
        'fg_indicador_quimico'      => validar_opcao($entrada, 'fg_indicador_quimico', INDICADORES, 'pendente', $erros, 'o indicador químico'),
        'fg_indicador_biologico'    => validar_opcao($entrada, 'fg_indicador_biologico', INDICADORES, 'pendente', $erros, 'o indicador biológico'),
        'fg_status'                 => (int) validar_opcao($entrada, 'fg_status', ['0', '1'], '1', $erros, 'a situação'),
        'produtos'                  => null,
    ];

    if ($dados['dt_esterilizacao'] && $dados['dt_validade_esterilizacao'] && $dados['dt_validade_esterilizacao'] < $dados['dt_esterilizacao']) {
        $erros['dt_validade_esterilizacao'] = 'A validade não pode ser antes da data da esterilização.';
    }

    if ($dados['dt_esterilizacao'] && $dados['dt_esterilizacao'] > date('Y-m-d')) {
        $erros['dt_esterilizacao'] = 'A data da esterilização não pode ser no futuro.';
    }

    if (array_key_exists('produtos', $entrada) || $exigirProdutos) {
        $dados['produtos'] = validar_produtos_do_ciclo($pdo, $entrada['produtos'] ?? null, $erros);
    }

    return [$dados, $erros];
}

/**
 * Valida a lista de produtos: [{ "cd_produto": 5, "qtd_produto": 2 }, ...]
 * qtd_produto é opcional (padrão 1). Devolve a lista limpa.
 */
function validar_produtos_do_ciclo(PDO $pdo, mixed $lista, array &$erros): array
{
    if (!is_array($lista) || $lista === []) {
        $erros['produtos'] = 'Informe pelo menos um produto do ciclo.';
        return [];
    }

    $produtos = [];

    foreach (array_values($lista) as $posicao => $item) {
        $numero = $posicao + 1;
        $item = is_array($item) ? $item : [];
        $errosItem = [];

        $cdProduto = validar_inteiro($item, 'cd_produto', 1, true, $errosItem, 'o produto');
        $quantidade = validar_inteiro($item, 'qtd_produto', 1, false, $errosItem, 'a quantidade') ?? 1;

        if ($errosItem) {
            $erros['produtos'] = "Produto $numero da lista: " . implode(' ', $errosItem);
            return [];
        }

        if (isset($produtos[$cdProduto])) {
            $erros['produtos'] = "O produto $cdProduto aparece mais de uma vez. Some as quantidades numa linha só.";
            return [];
        }

        $produtos[$cdProduto] = ['cd_produto' => $cdProduto, 'qtd_produto' => $quantidade];
    }

    // Todos precisam existir e estar ativos
    $marcadores = implode(',', array_fill(0, count($produtos), '?'));
    $stmt = $pdo->prepare("SELECT cd_produto FROM produto WHERE fg_status = 1 AND cd_produto IN ($marcadores)");
    $stmt->execute(array_keys($produtos));
    $encontrados = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    $faltando = array_diff(array_keys($produtos), $encontrados);

    if ($faltando) {
        $erros['produtos'] = 'Produto(s) não encontrado(s) ou inativo(s): ' . implode(', ', $faltando) . '.';
        return [];
    }

    return array_values($produtos);
}

/**
 * Grava o ciclo (novo, se $cdEsterilizacao for null) e, se vierem, os produtos.
 * Devolve o cd_esterilizacao.
 */
function salvar_esterilizacao(PDO $pdo, array $dados, ?int $cdEsterilizacao = null): int
{
    $pdo->beginTransaction();

    try {
        if ($cdEsterilizacao === null) {
            $cdEsterilizacao = inserir_esterilizacao($pdo, $dados);
        } else {
            alterar_esterilizacao($pdo, $cdEsterilizacao, $dados);
        }

        if ($dados['produtos'] !== null) {
            substituir_produtos_do_ciclo($pdo, $cdEsterilizacao, $dados['produtos']);
        }

        $pdo->commit();
        return $cdEsterilizacao;
    } catch (Throwable $erro) {
        $pdo->rollBack();
        throw $erro;
    }
}
