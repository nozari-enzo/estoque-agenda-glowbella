<?php
/**
 * API de clientes.
 *
 *   GET    api/clientes.php          → lista (filtros: busca, fg_status)
 *   GET    api/clientes.php?id=1     → uma cliente
 *   POST   api/clientes.php          → cria   { "nm_cliente": "...", "nu_telefone": "...", "dt_nascimento": "AAAA-MM-DD" }
 *   PUT    api/clientes.php?id=1     → edita  (só os campos enviados; fg_status 1 reativa)
 *   DELETE api/clientes.php?id=1     → desativa (fg_status = 0)
 *
 * Detalhes e exemplos: docs/api.md
 */
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/support/api.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';

exigir_login();
exigir_csrf();

switch (metodo()) {
    case 'GET':
        $id = id_da_url();

        if ($id !== null) {
            $cliente = buscar_cliente($pdo, $id);
            $cliente ? responder($cliente) : responder_erro('Cliente não encontrada.', 404);
        }

        responder(listar_clientes($pdo, [
            'busca'     => filtro_texto('busca'),
            'fg_status' => filtro_opcao('fg_status', ['0', '1', 'todos']) ?? '1',
        ]));

    case 'POST':
        [$dados, $erros] = validar_cliente($pdo, ler_json());

        if ($erros) {
            responder_erro('Confira os campos da cliente.', 422, $erros);
        }

        try {
            $id = criar_cliente($pdo, $dados);
        } catch (PDOException $erro) {
            responder_erro_banco($erro);
        }

        responder(buscar_cliente($pdo, $id), 201);

    case 'PUT':
        $id = exigir_id();
        $atual = buscar_cliente($pdo, $id) ?? responder_erro('Cliente não encontrada.', 404);

        [$dados, $erros] = validar_cliente(
            $pdo,
            mesclar_edicao($atual, ler_json(), ['nm_cliente', 'dt_nascimento', 'nu_telefone', 'fg_status']),
            $id
        );

        if ($erros) {
            responder_erro('Confira os campos da cliente.', 422, $erros);
        }

        try {
            atualizar_cliente($pdo, $id, $dados);
        } catch (PDOException $erro) {
            responder_erro_banco($erro);
        }

        responder(buscar_cliente($pdo, $id));

    case 'DELETE':
        $id = exigir_id();
        buscar_cliente($pdo, $id) ?? responder_erro('Cliente não encontrada.', 404);

        desativar_cliente($pdo, $id);
        responder(['ok' => true]);

    default:
        metodo_nao_permitido(['GET', 'POST', 'PUT', 'DELETE']);
}
