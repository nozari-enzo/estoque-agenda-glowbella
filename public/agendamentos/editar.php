<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/agenda/agenda.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$agendamento = $id ? buscar_agendamento($pdo, $id) : null;

if (!$agendamento) {
    flash('Agendamento não encontrado.', 'danger');
    redirecionar('agendamentos/');
}

$titulo = 'Editar agendamento';
$menu = 'agendamentos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Editar agendamento</h1>
        <p>Altere os dados ou o status do horário.</p>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/agendamentos/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>