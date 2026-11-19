<?php
/**
 * API de esterilizações (ciclos de autoclave e os produtos de cada ciclo).
 *
 *   GET    api/esterilizacoes.php          → lista (filtros: situacao, cd_produto, de, ate, fg_status)
 *   GET    api/esterilizacoes.php?id=1     → um ciclo, com os produtos
 *   POST   api/esterilizacoes.php          → registra um ciclo com os produtos
 *   PUT    api/esterilizacoes.php?id=1     → edita (ex.: resultado do indicador biológico)
 *   DELETE api/esterilizacoes.php?id=1     → desativa (fg_status = 0)
 *
 * Detalhes e exemplos: docs/api/esterilizacoes.md
 */
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/support/api.php';
require __DIR__ . '/../../app/repositories/esterilizacoes/esterilizacoes.php';
require __DIR__ . '/../../app/services/esterilizacao.php';

exigir_login();
exigir_csrf();

switch (metodo()) {
    case 'GET':
        $id = id_da_url();

        if ($id !== null) {
            $ciclo = buscar_esterilizacao($pdo, $id);
            $ciclo ? responder($ciclo) : responder_erro('Ciclo de esterilização não encontrado.', 404);
        }

        responder(listar_esterilizacoes($pdo, [
            'situacao'   => filtro_opcao('situacao', ['valido', 'pendente', 'vencido', 'reprovado']),
            'cd_produto' => filtro_inteiro('cd_produto'),
            'de'         => filtro_data('de'),
            'ate'        => filtro_data('ate'),
            'fg_status'  => filtro_opcao('fg_status', ['0', '1', 'todos']) ?? '1',
        ]));

    case 'POST':
        [$dados, $erros] = validar_esterilizacao($pdo, ler_json(), true);

        if ($erros) {
            responder_erro('Confira os campos do ciclo.', 422, $erros);
        }

        responder(buscar_esterilizacao($pdo, salvar_esterilizacao($pdo, $dados)), 201);

    case 'PUT':
        $id = exigir_id();
        $atual = buscar_esterilizacao($pdo, $id) ?? responder_erro('Ciclo de esterilização não encontrado.', 404);

        $entrada = mesclar_edicao($atual, ler_json(), [
            'nm_metodo', 'dt_esterilizacao', 'dt_validade_esterilizacao',
            'fg_indicador_quimico', 'fg_indicador_biologico', 'fg_status',
        ]);
        [$dados, $erros] = validar_esterilizacao($pdo, $entrada, false);

        if ($erros) {
            responder_erro('Confira os campos do ciclo.', 422, $erros);
        }

        salvar_esterilizacao($pdo, $dados, $id);
        responder(buscar_esterilizacao($pdo, $id));

    case 'DELETE':
        $id = exigir_id();
        buscar_esterilizacao($pdo, $id) ?? responder_erro('Ciclo de esterilização não encontrado.', 404);

        desativar_esterilizacao($pdo, $id);
        responder(['ok' => true]);

    default:
        metodo_nao_permitido(['GET', 'POST', 'PUT', 'DELETE']);
}
