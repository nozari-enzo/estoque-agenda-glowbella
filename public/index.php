<?php
require __DIR__ . '/../app/bootstrap/init.php';

/**
 * Conta os registros de uma tabela para mostrar nos atalhos.
 * Se a tabela ainda não existir (migrations não rodadas), retorna null
 * e a Home continua funcionando, só sem o número.
 */
function contar(PDO $pdo, string $sql): ?int
{
    try {
        $consulta = $pdo->prepare($sql);
        $consulta->execute();

        return (int) $consulta->fetchColumn();
    } catch (PDOException $erro) {
        return null;
    }
}

/** Busca o total e uma lista curta; null indica que o banco/tabela não está disponível. */
function listarProdutosAbaixoMinimo(PDO $pdo): ?array
{
    try {
        $consultaTotal = $pdo->prepare(
            'SELECT COUNT(*)
             FROM produtos
             WHERE ativo = 1 AND quantidade < estoque_minimo'
        );
        $consultaTotal->execute();

        $consultaItens = $pdo->prepare(
            'SELECT nome, quantidade, estoque_minimo
             FROM produtos
             WHERE ativo = 1 AND quantidade < estoque_minimo
             ORDER BY (estoque_minimo - quantidade) DESC, nome
             LIMIT 5'
        );
        $consultaItens->execute();

        return [
            'total' => (int) $consultaTotal->fetchColumn(),
            'itens' => $consultaItens->fetchAll(PDO::FETCH_ASSOC),
        ];
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

$produtosAbaixoMinimo = listarProdutosAbaixoMinimo($pdo);

$titulo = 'Início';
$menu = 'inicio';
require __DIR__ . '/../app/views/partials/header.php';
?>

<section class="boas-vindas card-glow">
    <div>
        <p class="boas-vindas-data"><?= e(ucfirst(data_por_extenso())) ?></p>
        <h1><?= e(saudacao()) ?>! <span class="text-primary">✦</span></h1>
        <p class="mb-0">Bem-vinda ao sistema da <strong>GlowBella</strong>. O que vamos fazer hoje?</p>
    </div>
    <i class="bi bi-gem boas-vindas-icone" aria-hidden="true"></i>
</section>

<section class="alertas-home card-glow" aria-labelledby="titulo-alertas-home">
    <div class="alertas-home-cabecalho">
        <div>
            <p class="alertas-home-sobretitulo">Acompanhamento</p>
            <h2 id="titulo-alertas-home">Avisos importantes</h2>
            <p class="text-muted mb-0">Pontos que podem precisar de atenção antes dos próximos atendimentos.</p>
        </div>
        <?php if ($produtosAbaixoMinimo !== null): ?>
            <span class="badge <?= $produtosAbaixoMinimo['total'] > 0 ? 'text-bg-warning' : 'text-bg-success' ?>">
                <?= e((string) $produtosAbaixoMinimo['total']) ?> <?= $produtosAbaixoMinimo['total'] === 1 ? 'item abaixo do mínimo' : 'itens abaixo do mínimo' ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <article class="alerta-home-item <?= $produtosAbaixoMinimo === null ? 'alerta-home-indisponivel' : ($produtosAbaixoMinimo['total'] > 0 ? 'alerta-home-atencao' : 'alerta-home-ok') ?>">
                <span class="alerta-home-icone" aria-hidden="true"><i class="bi <?= $produtosAbaixoMinimo === null ? 'bi-database-exclamation' : ($produtosAbaixoMinimo['total'] > 0 ? 'bi-exclamation-triangle' : 'bi-check-circle') ?>"></i></span>
                <div class="alerta-home-conteudo">
                    <h3>Reposição de estoque</h3>
                    <?php if ($produtosAbaixoMinimo === null): ?>
                        <p>Não foi possível consultar o estoque. Confira a conexão e se as migrations foram executadas.</p>
                    <?php elseif ($produtosAbaixoMinimo['total'] === 0): ?>
                        <p>Nenhum produto ativo está abaixo do estoque mínimo cadastrado.</p>
                    <?php else: ?>
                        <p><?= e((string) $produtosAbaixoMinimo['total']) ?> <?= $produtosAbaixoMinimo['total'] === 1 ? 'produto está' : 'produtos estão' ?> abaixo do mínimo:</p>
                        <ul class="alerta-home-lista">
                            <?php foreach ($produtosAbaixoMinimo['itens'] as $produto): ?>
                                <li><strong><?= e($produto['nome']) ?></strong><span><?= e((string) $produto['quantidade']) ?> / mínimo <?= e((string) $produto['estoque_minimo']) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if ($produtosAbaixoMinimo['total'] > count($produtosAbaixoMinimo['itens'])): ?>
                            <p class="small mb-2">Exibindo <?= e((string) count($produtosAbaixoMinimo['itens'])) ?> dos <?= e((string) $produtosAbaixoMinimo['total']) ?> itens.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?= url('estoque/') ?>" class="alerta-home-link">Ver estoque <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
            </article>
        </div>
        <div class="col-12 col-lg-5">
            <article class="alerta-home-item alerta-home-prototipo">
                <span class="alerta-home-icone" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                <div class="alerta-home-conteudo">
                    <h3>Esterilização e validade</h3>
                    <p>O histórico e os prazos ainda são demonstrativos. A Home não consegue confirmar ciclos vencidos ou próximos do vencimento até essa informação ser integrada ao banco.</p>
                    <a href="<?= url('esterilizacao/') ?>" class="alerta-home-link">Abrir esterilização <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
            </article>
        </div>
    </div>
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
                        <strong><?= e((string) $atalho['total']) ?></strong>
                        <?= e($atalho['total'] === 1 ? $atalho['rotulo'][0] : $atalho['rotulo'][1]) ?>
                    </span>
                <?php endif; ?>

                <span class="card-atalho-link">Acessar <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../app/views/partials/footer.php'; ?>
