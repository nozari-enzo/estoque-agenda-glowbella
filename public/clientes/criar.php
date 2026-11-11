<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$cliente = [];   // cadastro: formulário vazio

$titulo = 'Nova cliente';
$menu = 'clientes';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Nova cliente</h1>
        <p>Preencha os dados de contato da cliente.</p>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/clientes/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
