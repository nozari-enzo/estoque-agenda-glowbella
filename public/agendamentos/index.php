<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$titulo = 'Agenda';
$menu = 'agendamentos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Agenda</h1>
        <p>Agendamentos de serviços.</p>
    </div>
</div>

<!-- Página provisória: substituir pelo conteúdo real da US10 – Agendamento de serviços -->
<div class="card-glow">
    <div class="estado-vazio">
        <i class="bi bi-tools"></i>
        <h2>Em construção</h2>
        <p class="mb-0">Esta página será desenvolvida na <strong>US10 – Agendamento de serviços</strong>.</p>
        <p class="mt-3">O protótipo Financeiro demonstra como os valores de atendimentos e joias poderão se relacionar com a Agenda.</p>
        <a class="btn btn-outline-primary mt-2" href="<?= url('financeiro/') ?>"><i class="bi bi-cash-coin" aria-hidden="true"></i> Ver protótipo Financeiro</a>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
