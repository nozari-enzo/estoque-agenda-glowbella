<?php
/**
 * Página para conferir se o ambiente está configurado corretamente.
 * Pode ser apagada antes do deploy.
 */
require __DIR__ . '/../includes/init.php';

$versaoMysql = $pdo->query('SELECT VERSION()')->fetchColumn();
$tabelas = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$titulo = 'Teste de conexão';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-4">Teste de conexão</h1>

<ul class="list-group mb-4">
    <li class="list-group-item d-flex justify-content-between">
        PHP <span class="badge text-bg-success"><?= e(PHP_VERSION) ?></span>
    </li>
    <li class="list-group-item d-flex justify-content-between">
        MySQL <span class="badge text-bg-success"><?= e($versaoMysql) ?></span>
    </li>
    <li class="list-group-item d-flex justify-content-between">
        Banco <span class="badge text-bg-success"><?= e(DB_NAME) ?></span>
    </li>
    <li class="list-group-item d-flex justify-content-between">
        Bootstrap <span class="badge text-bg-success">se este item está estilizado, está funcionando</span>
    </li>
</ul>

<h2 class="h5">Tabelas no banco (<?= count($tabelas) ?>)</h2>
<?php if ($tabelas): ?>
    <ul>
        <?php foreach ($tabelas as $tabela): ?>
            <li><?= e($tabela) ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="text-muted">Nenhuma tabela ainda. Elas serão criadas pelas migrations em <code>database/migrations/</code>.</p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
