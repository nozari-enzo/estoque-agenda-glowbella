<?php
require __DIR__ . '/../app/bootstrap/init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona direto para a página inicial
if (isset($_SESSION['cd_usuario'])) {
    header('Location: index.php');
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($login === '' || $senha === '') {
        $erro = 'Por favor, preencha o e-mail ou nome de usuário e a senha.';
    } else {
        try {
            $sql = 'SELECT cd_usuario, nm_usuario, ds_email, ds_senha 
                    FROM admin 
                    WHERE ds_email = :email OR nm_usuario = :nome 
                    LIMIT 1';
            
            $consulta = $pdo->prepare($sql);
            $consulta->bindValue(':email', $login);
            $consulta->bindValue(':nome', $login);
            $consulta->execute();
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($senha, $usuario['ds_senha'])) {
                session_regenerate_id(true);

                $_SESSION['cd_usuario'] = $usuario['cd_usuario'];
                $_SESSION['nm_usuario'] = $usuario['nm_usuario'];
                $_SESSION['ds_email']   = $usuario['ds_email'];

                header('Location: index.php');
                exit;
            } else {
                $erro = 'Usuário, e-mail ou senha inválidos.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao conectar com o banco de dados. Tente novamente.';
        }
    }
}

$titulo = 'Login';
$menu = 'login';
require __DIR__ . '/../app/views/partials/estilologin.php';
?>

<div class="card" style="min-height: 700px; display: flex; justify-content: center; align-items: center; border:none;">
  <div class="card p-4 d-flex justify-content: center shadow-sm" style="min-height: 400px; width: 100%; max-width: 400px;">
    <div class="card-body d-flex flex-column justify-content-center gap-3">
      
      <h2 class="h5 text-center mb-2">Acesse sua conta GlowBella</h2>

      <?php if ($erro !== null): ?>
        <div class="alert alert-danger py-2 text-center" role="alert">
          <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="d-flex flex-column gap-3">
        <div>
          <label for="login" class="form-label">E-mail ou Nome de usuário</label>
          <input 
            type="text" 
            class="form-control" 
            id="login" 
            name="login" 
            value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" 
            required 
            autofocus
          >
        </div>

        <div>
          <label for="senha" class="form-label">Senha</label>
          <input 
            type="password" 
            class="form-control" 
            id="senha" 
            name="senha" 
            required
          >
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2">Entrar</button>
      </form>

    </div>
  </div>
</div>

</body>
</html>
