<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/categorias/categorias.php';

// Vindo do Estoque ("Cadastrar novo produto"): já traz o nome digitado e volta para lá ao salvar
$veioDoEstoque = ($_GET['origem'] ?? '') === 'estoque';
$nomeSugerido = is_string($_GET['nm_produto'] ?? null) ? mb_substr(trim($_GET['nm_produto']), 0, 120) : '';

$produto = $nomeSugerido !== '' ? ['nm_produto' => $nomeSugerido] : [];   // cadastro: formulário vazio
$voltarPara = $veioDoEstoque ? 'estoque/?produto_novo=1' : 'produtos/?salvo=1';
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
    <?php if ($veioDoEstoque): ?>
        <a href="<?= url('estoque/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao estoque</a>
    <?php else: ?>
        <a href="<?= url('produtos/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao catálogo</a>
    <?php endif; ?>
</div>

<?php if (!$categorias): ?>
    <div class="alert alert-warning" role="alert">
        Cadastre pelo menos uma categoria antes: <a href="<?= url('produtos/categorias.php') ?>">Gerenciar categorias</a>.
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../app/views/produtos/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
