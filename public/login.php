<?php
/**
 * Tela de login. Usa fazer_login() de app/services/auth.php, a mesma regra da API
 * (public/api/auth.php), então quem entra por aqui também consegue usar as APIs.
 */
require __DIR__ . '/../app/bootstrap/init.php';
require_once __DIR__ . '/../app/services/auth.php';

// Já está logada: vai direto para o início
if (usuario_logado() !== null) {
    header('Location: ' . url());
    exit;
}

$erro = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['ds_email'] ?? ''));
    $senha = (string) ($_POST['ds_senha'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        $erro = 'A página ficou muito tempo aberta. Tente entrar de novo.';
    } elseif ($email === '' || $senha === '') {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        try {
            if (fazer_login($pdo, $email, $senha) !== null) {
                header('Location: ' . url());
                exit;
            }
            $erro = 'E-mail ou senha incorretos.';
        } catch (PDOException $e) {
            $erro = 'Erro ao conectar com o banco de dados. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | GlowBella</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<main class="login-pagina">
    <div class="card shadow-sm login-cartao">
        <div class="card-body p-4 d-flex flex-column gap-3">
            <div class="text-center">
                <div class="login-marca"><i class="bi bi-gem"></i> GlowBella</div>
                <h1 class="h5 mt-2 mb-0">Acesse sua conta</h1>
            </div>

            <?php if ($erro !== null): ?>
                <div class="alert alert-danger py-2 text-center mb-0" role="alert"><?= e($erro) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= url('login.php') ?>" class="d-flex flex-column gap-3">
                <?= csrf_campo() ?>

                <div>
                    <label for="ds_email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="ds_email" name="ds_email"
                           value="<?= e($email) ?>" autocomplete="username" required autofocus>
                </div>

                <div>
                    <label for="ds_senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="ds_senha" name="ds_senha"
                           autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">Entrar</button>
            </form>
        </div>
    </div>
</main>

</body>
</html>
