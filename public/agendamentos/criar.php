<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$agendamento = []; // Vazio para o cadastro

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