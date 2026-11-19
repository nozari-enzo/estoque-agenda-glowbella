<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/esterilizacoes/esterilizacoes.php';
require __DIR__ . '/../../app/repositories/produtos/produtos.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$ciclo = $id ? buscar_esterilizacao($pdo, $id) : null;

if (!$ciclo || $ciclo['fg_status'] !== 1) {
    flash('Ciclo não encontrado.', 'danger');
    redirecionar('esterilizacao/');
}

$produtos = listar_produtos($pdo);

$titulo = 'Editar ciclo';
$menu = 'esterilizacao';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Editar ciclo #<?= $ciclo['cd_esterilizacao'] ?></h1>
        <p>Atualize os indicadores quando sair o resultado, ou corrija os dados do ciclo.</p>
    </div>
    <a href="<?= url('esterilizacao/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar aos ciclos</a>
</div>

<?php require __DIR__ . '/../../app/views/esterilizacao/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
