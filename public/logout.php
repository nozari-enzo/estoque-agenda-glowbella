<?php
/**
 * Sair. Recebe o POST do botão "Sair" do menu (com token CSRF, para que um link
 * de outro site não consiga deslogar a pessoa).
 */
require __DIR__ . '/../app/bootstrap/init.php';
require_once __DIR__ . '/../app/services/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url());
    exit;
}

csrf_validar();
fazer_logout();

header('Location: ' . url('login.php'));
exit;
