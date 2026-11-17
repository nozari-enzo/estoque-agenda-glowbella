<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/produtos/produtos.php';

$ciclo = [];   // cadastro: formulário com valores sugeridos
$produtos = listar_produtos($pdo);

$titulo = 'Registrar ciclo';
$menu = 'esterilizacao';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Registrar ciclo</h1>
        <p>Dados do processamento, indicadores e itens esterilizados.</p>
    </div>
    <a href="<?= url('esterilizacao/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar aos ciclos</a>
</div>

<?php require __DIR__ . '/../../app/views/esterilizacao/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
