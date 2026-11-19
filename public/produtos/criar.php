<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/categorias/categorias.php';

$produto = [];   // cadastro: formulário vazio
$categorias = listar_categorias($pdo);

$titulo = 'Novo produto';
$menu = 'produtos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Novo produto</h1>
        <p>Defina a finalidade do item, os preços e o estoque inicial.</p>
    </div>
    <a href="<?= url('produtos/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao catálogo</a>
</div>

<?php if (!$categorias): ?>
    <div class="alert alert-warning" role="alert">
        Cadastre pelo menos uma categoria antes: <a href="<?= url('produtos/categorias.php') ?>">Gerenciar categorias</a>.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../app/views/produtos/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
