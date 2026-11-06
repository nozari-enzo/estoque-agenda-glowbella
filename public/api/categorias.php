<?php
/**
 * API de categorias.
 *
 *   GET    api/categorias.php          → lista (filtros: busca, fg_status)
 *   GET    api/categorias.php?id=2     → uma categoria
 *   POST   api/categorias.php          → cria   { "nm_categoria": "..." }
 *   PUT    api/categorias.php?id=2     → edita  (só os campos enviados; fg_status 1 reativa)
 *   DELETE api/categorias.php?id=2     → desativa (bloqueado se houver produtos ativos nela)
 *
 * Detalhes e exemplos: docs/api.md
 */
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/support/api.php';
require __DIR__ . '/../../app/repositories/categorias/categorias.php';

exigir_login();
exigir_csrf();

switch (metodo()) {
    case 'GET':
        $id = id_da_url();

        if ($id !== null) {
            $categoria = buscar_categoria($pdo, $id);
            $categoria ? responder($categoria) : responder_erro('Categoria não encontrada.', 404);
        }

        responder(listar_categorias($pdo, [
            'busca'     => filtro_texto('busca'),
            'fg_status' => filtro_opcao('fg_status', ['0', '1', 'todos']) ?? '1',
        ]));

    case 'POST':
        [$dados, $erros] = validar_categoria($pdo, ler_json());

        if ($erros) {
            responder_erro('Confira os campos da categoria.', 422, $erros);
        }

        try {
            $id = criar_categoria($pdo, $dados);
        } catch (PDOException $erro) {
            responder_erro_banco($erro);
        }

        responder(buscar_categoria($pdo, $id), 201);

    case 'PUT':
        $id = exigir_id();
        $atual = buscar_categoria($pdo, $id) ?? responder_erro('Categoria não encontrada.', 404);

        [$dados, $erros] = validar_categoria($pdo, mesclar_edicao($atual, ler_json(), ['nm_categoria', 'fg_status']), $id);

        if ($erros) {
            responder_erro('Confira os campos da categoria.', 422, $erros);
        }

        if ($dados['fg_status'] === 0 && $atual['qtd_produtos_ativos'] > 0) {
            responder_erro('Não é possível desativar: a categoria tem produtos ativos.', 409);
        }

        try {
            atualizar_categoria($pdo, $id, $dados);
        } catch (PDOException $erro) {
            responder_erro_banco($erro);
        }

        responder(buscar_categoria($pdo, $id));

    case 'DELETE':
        $id = exigir_id();
        $atual = buscar_categoria($pdo, $id) ?? responder_erro('Categoria não encontrada.', 404);

        if ($atual['qtd_produtos_ativos'] > 0) {
            responder_erro("Não é possível desativar: a categoria tem {$atual['qtd_produtos_ativos']} produto(s) ativo(s).", 409);
        }

        desativar_categoria($pdo, $id);
        responder(['ok' => true]);

    default:
        metodo_nao_permitido(['GET', 'POST', 'PUT', 'DELETE']);
}
