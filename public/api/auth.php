<?php
/**
 * API de login.
 *
 *   GET    api/auth.php   → quem está logado (401 se ninguém)
 *   POST   api/auth.php   → entrar   { "ds_email": "...", "ds_senha": "..." }
 *   DELETE api/auth.php   → sair
 */
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/support/api.php';

exigir_csrf();

switch (metodo()) {
    case 'GET':
        responder(exigir_login());

    case 'POST':
        $dados = ler_json();
        $email = is_string($dados['ds_email'] ?? null) ? $dados['ds_email'] : '';
        $senha = is_string($dados['ds_senha'] ?? null) ? $dados['ds_senha'] : '';

        if ($email === '' || $senha === '') {
            responder_erro('Informe e-mail e senha.', 422);
        }

        $usuario = fazer_login($pdo, $email, $senha);

        if (!$usuario) {
            responder_erro('E-mail ou senha incorretos.', 401);
        }

        // O id de sessão mudou no login: o token CSRF continua valendo (está na sessão)
        responder($usuario);

    case 'DELETE':
        fazer_logout();
        responder(['ok' => true]);

    default:
        metodo_nao_permitido(['GET', 'POST', 'DELETE']);
}
