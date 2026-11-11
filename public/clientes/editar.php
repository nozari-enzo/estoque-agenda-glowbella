<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$cliente = $id ? buscar_cliente($pdo, $id) : null;

if (!$cliente || $cliente['fg_status'] !== 1) {
    flash('Cliente não encontrada ou desativada.', 'danger');
    redirecionar('clientes/');
}

$titulo = 'Editar cliente';
$menu = 'clientes';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Editar cliente</h1>
        <p><?= e($cliente['nm_cliente']) ?></p>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/clientes/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
