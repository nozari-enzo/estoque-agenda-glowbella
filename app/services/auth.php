<?php
/**
 * Login e sessão. Usado pela tela de login (public/login.php), pela API
 * (public/api/auth.php) e pelo cabeçalho das páginas:
 *
 *   if (fazer_login($pdo, $_POST['ds_email'] ?? '', $_POST['ds_senha'] ?? '')) { ... }
 *   $usuario = exigir_login_pagina();   // manda para o login quem não estiver logado
 *
 * Quem está logado fica em $_SESSION['usuario'] = ['cd_usuario' => ..., 'nm_usuario' => ...].
 * Use SEMPRE essas funções: as APIs (exigir_login()) também olham essa mesma chave.
 */

/**
 * Confere e-mail e senha. Se estiverem certos, inicia a sessão e devolve os
 * dados do usuário; se não, devolve null.
 */
function fazer_login(PDO $pdo, string $email, string $senha): ?array
{
    $stmt = $pdo->prepare(
        'SELECT cd_usuario, nm_usuario, ds_senha
         FROM admin
         WHERE ds_email = ? AND fg_status = 1'
    );
    $stmt->execute([trim($email)]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senha, $usuario['ds_senha'])) {
        return null;
    }

    // Novo id de sessão a cada login (evita sequestro de sessão)
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'cd_usuario' => (int) $usuario['cd_usuario'],
        'nm_usuario' => $usuario['nm_usuario'],
    ];

    return $_SESSION['usuario'];
}

/** Dados de quem está logado, ou null. */
function usuario_logado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/**
 * Para páginas (não APIs): quem não estiver logado vai para a tela de login.
 * Já é chamada pelo app/views/partials/header.php, então toda página com o menu está protegida.
 */
function exigir_login_pagina(): array
{
    $usuario = usuario_logado();

    if ($usuario === null) {
        header('Location: ' . url('login.php'));
        exit;
    }

    return $usuario;
}

/** Encerra a sessão. */
function fazer_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
    }

    session_destroy();
}
