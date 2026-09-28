<?php
require __DIR__ . '/../includes/init.php';

/**
 * Conta os registros de uma tabela para mostrar nos atalhos.
 * Se a tabela ainda não existir (migrations não rodadas), retorna null
 * e a Home continua funcionando, só sem o número.
 */
function contar(PDO $pdo, string $sql): ?int
{
    try {
        return (int) $pdo->query($sql)->fetchColumn();
    } catch (PDOException $erro) {
        return null;
    }
}

$atalhos = [
    [
        'titulo'    => 'Agenda',
        'descricao' => 'Marque e acompanhe os horários de atendimento.',
        'icone'     => 'bi-calendar-heart',
        'link'      => 'agendamentos/',
        'total'     => contar($pdo, "SELECT COUNT(*) FROM agendamentos WHERE DATE(inicio) = CURDATE() AND status <> 'cancelado'"),
        'rotulo'    => ['agendamento hoje', 'agendamentos hoje'],
    ],
    [
        'titulo'    => 'Clientes',
        'descricao' => 'Cadastro e contato das clientes.',
        'icone'     => 'bi-people',
        'link'      => 'clientes/',
        'total'     => contar($pdo, 'SELECT COUNT(*) FROM clientes'),
        'rotulo'    => ['cliente cadastrada', 'clientes cadastradas'],
    ],
    [
        'titulo'    => 'Produtos',
        'descricao' => 'Catálogo de brincos, piercings e acessórios.',
        'icone'     => 'bi-gem',
        'link'      => 'produtos/',
        'total'     => contar($pdo, 'SELECT COUNT(*) FROM produtos WHERE ativo = 1'),
        'rotulo'    => ['produto ativo', 'produtos ativos'],
    ],
    [
        'titulo'    => 'Estoque',
        'descricao' => 'Entradas, saídas e produtos para repor.',
        'icone'     => 'bi-box-seam',
        'link'      => 'estoque/',
        'total'     => contar($pdo, 'SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND quantidade < estoque_minimo'),
        'rotulo'    => ['produto para repor', 'produtos para repor'],
    ],
];

$titulo = 'Início';
$menu = 'inicio';
require __DIR__ . '/../includes/header.php';
?>

<section class="boas-vindas card-glow">
    <div>
        <p class="boas-vindas-data"><?= e(ucfirst(data_por_extenso())) ?></p>
        <h1><?= e(saudacao()) ?>! <span class="text-primary">✦</span></h1>
        <p class="mb-0">Bem-vinda ao sistema da <strong>GlowBella</strong>. O que vamos fazer hoje?</p>
    </div>
    <i class="bi bi-gem boas-vindas-icone" aria-hidden="true"></i>
</section>

<h2 class="h5 mb-3">Acesso rápido</h2>

<div class="row g-3 g-lg-4">
    <?php foreach ($atalhos as $atalho): ?>
        <div class="col-sm-6 col-xl-3">
            <a href="<?= url($atalho['link']) ?>" class="card-atalho card-glow">
                <span class="icone-circulo"><i class="bi <?= e($atalho['icone']) ?>"></i></span>
                <h3><?= e($atalho['titulo']) ?></h3>
                <p><?= e($atalho['descricao']) ?></p>

                <?php if ($atalho['total'] !== null): ?>
                    <span class="card-atalho-total">
                        <strong><?= $atalho['total'] ?></strong>
                        <?= e($atalho['total'] === 1 ? $atalho['rotulo'][0] : $atalho['rotulo'][1]) ?>
                    </span>
                <?php endif; ?>

                <span class="card-atalho-link">Acessar <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
