<?php
/**
 * API de movimentações de estoque.
 *
 *   GET  api/movimentacoes.php           → histórico (filtros: cd_produto, fg_tipo, de, ate, limite)
 *   GET  api/movimentacoes.php?id=1      → uma movimentação
 *   POST api/movimentacoes.php           → registra entrada ou saída e atualiza o estoque
 *
 * Não há PUT nem DELETE: o histórico não é alterado. Para corrigir um erro,
 * registre uma movimentação contrária (ex.: entrada de ajuste).
 * Detalhes e exemplos: docs/api/movimentacoes.md
 */
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/support/api.php';
require __DIR__ . '/../../app/repositories/movimentacoes/movimentacoes.php';
require __DIR__ . '/../../app/services/estoque.php';

$usuario = exigir_login();
exigir_csrf();

switch (metodo()) {
    case 'GET':
        $id = id_da_url();

        if ($id !== null) {
            $movimentacao = buscar_movimentacao($pdo, $id);
            $movimentacao ? responder($movimentacao) : responder_erro('Movimentação não encontrada.', 404);
        }

        responder(listar_movimentacoes($pdo, [
            'cd_produto' => filtro_inteiro('cd_produto'),
            'fg_tipo'    => filtro_opcao('fg_tipo', ['entrada', 'saida']),
            'de'         => filtro_data('de'),
            'ate'        => filtro_data('ate'),
            'limite'     => filtro_inteiro('limite') ?? 100,
        ]));

    case 'POST':
        [$dados, $erros] = validar_movimentacao($pdo, ler_json());

        if ($erros) {
            responder_erro('Confira os campos da movimentação.', 422, $erros);
        }

        $resultado = registrar_movimentacao($pdo, $dados, $usuario['cd_usuario']);

        if (isset($resultado['estoque_insuficiente'])) {
            responder_erro(
                "Estoque insuficiente: há {$resultado['estoque_insuficiente']} unidade(s) disponível(is).",
                422,
                ['qtd_movimentada' => "Máximo disponível: {$resultado['estoque_insuficiente']}."]
            );
        }

        $movimentacao = buscar_movimentacao($pdo, $resultado['cd_movimentacao']);
        $movimentacao['qtd_produto_atual'] = $resultado['qtd_produto'];

        responder($movimentacao, 201);

    default:
        metodo_nao_permitido(['GET', 'POST']);
}
