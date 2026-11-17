<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/produtos/produtos.php';
require __DIR__ . '/../../app/repositories/categorias/categorias.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$produto = $id ? buscar_produto($pdo, $id) : null;

if (!$produto || $produto['fg_status'] !== 1) {
    flash('Produto não encontrado ou desativado.', 'danger');
    redirecionar('produtos/');
}

$categorias = listar_categorias($pdo);

$titulo = 'Editar produto';
$menu = 'produtos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Editar produto</h1>
        <p><?= e($produto['nm_produto']) ?></p>
    </div>
    <a href="<?= url('produtos/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao catálogo</a>
</div>

<?php require __DIR__ . '/../../app/views/produtos/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
