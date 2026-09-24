<?php
require __DIR__ . '/../includes/init.php';

$titulo = 'Início';
$menu = 'inicio';
require __DIR__ . '/../includes/header.php';
?>

<!-- Página provisória: a Home de verdade é a US02 (feature/home) -->
<div class="py-5 text-center">
    <h1 class="display-5 fw-bold text-primary"><i class="bi bi-gem"></i> GlowBella</h1>
    <p class="lead text-muted">Sistema de estoque e agendamentos.</p>
    <p class="text-muted">Setup funcionando! Confira a conexão com o banco em
        <a href="<?= url('teste-conexao.php') ?>">teste de conexão</a>.</p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
