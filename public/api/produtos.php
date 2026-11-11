<?php
/**
 * API de produtos.
 *
 *   GET    api/produtos.php                 → lista (filtros: busca, cd_categoria, fg_finalidade, fg_status, estoque_baixo)
 *   GET    api/produtos.php?id=3            → um produto
 *   POST   api/produtos.php                 → cria   (JSON no corpo; aceita qtd_inicial)
 *   PUT    api/produtos.php?id=3            → edita  (JSON só com os campos que mudaram; não altera o estoque)
 *   DELETE api/produtos.php?id=3            → desativa (fg_status = 0)
 *
 * Todas as rotas exigem login. POST, PUT e DELETE exigem o cabeçalho X-CSRF-Token.
 * Detalhes e exemplos: docs/api.md
 */
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/support/api.php';
require __DIR__ . '/../../app/repositories/produtos/produtos.php';

$usuario = exigir_login();
exigir_csrf();

switch (metodo()) {
    case 'GET':
        $id = id_da_url();

        if ($id !== null) {
            $produto = buscar_produto($pdo, $id);
            $produto ? responder($produto) : responder_erro('Produto não encontrado.', 404);
        }

        responder(listar_produtos($pdo, [
            'busca'         => is_string($_GET['busca'] ?? null) ? trim($_GET['busca']) : '',
            'cd_categoria'  => $_GET['cd_categoria'] ?? null,
            'fg_finalidade' => in_array($_GET['fg_finalidade'] ?? '', ['venda', 'procedimento'], true) ? $_GET['fg_finalidade'] : null,
            'fg_status'     => in_array($_GET['fg_status'] ?? '1', ['0', '1', 'todos'], true) ? ($_GET['fg_status'] ?? '1') : '1',
            'estoque_baixo' => ($_GET['estoque_baixo'] ?? '') === '1',
        ]));

    case 'POST':
        [$dados, $erros] = validar_produto($pdo, ler_json(), true);

        if ($erros) {
            responder_erro('Confira os campos do produto.', 422, $erros);
        }

        try {
            $cdProduto = criar_produto($pdo, $dados, $usuario['cd_usuario']);
        } catch (PDOException $erro) {
            responder_erro_banco($erro);
        }

        responder(buscar_produto($pdo, $cdProduto), 201);

    case 'PUT':
        $id = exigir_id();
        $atual = buscar_produto($pdo, $id) ?? responder_erro('Produto não encontrado.', 404);

        // Campos que não vierem no JSON mantêm o valor atual (dá para enviar só o que mudou)
        $editaveis = array_intersect_key($atual, array_flip([
            'nm_produto', 'fg_finalidade', 'ds_lote', 'vl_compra', 'vl_produto',
            'qtd_estoque_minimo', 'dt_validade', 'cd_categoria', 'fg_status',
        ]));
        [$dados, $erros] = validar_produto($pdo, array_merge($editaveis, ler_json()), false);

        if ($erros) {
            responder_erro('Confira os campos do produto.', 422, $erros);
        }

        try {
            $existe = atualizar_produto($pdo, $id, $dados);
        } catch (PDOException $erro) {
            responder_erro_banco($erro);
        }

        $existe ? responder(buscar_produto($pdo, $id)) : responder_erro('Produto não encontrado.', 404);

    case 'DELETE':
        $id = exigir_id();

        desativar_produto($pdo, $id)
            ? responder(['ok' => true])
            : responder_erro('Produto não encontrado.', 404);

    default:
        metodo_nao_permitido(['GET', 'POST', 'PUT', 'DELETE']);
}
