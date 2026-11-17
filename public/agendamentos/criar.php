<?php
require __DIR__ . '/../../app/bootstrap/init.php';

// Cadastro vazio. Vindo da tela da cliente (?cd_cliente=3), ela já vem escolhida.
$agendamento = [];
$clienteDaUrl = filter_var($_GET['cd_cliente'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($clienteDaUrl) {
    $agendamento['cd_cliente'] = $clienteDaUrl;
}

$titulo = 'Novo agendamento';
$menu = 'agendamentos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Novo agendamento</h1>
        <p>Marque um horário para uma cliente.</p>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/agendamentos/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>