<?php
require __DIR__ . '/../app/bootstrap/init.php';

require __DIR__ . '/../app/repositories/agenda/agenda.php';
require __DIR__ . '/../app/repositories/clientes/clientes.php';
require __DIR__ . '/../app/repositories/produtos/produtos.php';
require __DIR__ . '/../app/repositories/esterilizacoes/esterilizacoes.php';

// Validade "próxima" dos produtos: vence nos próximos 60 dias (mesmo prazo da tela de Estoque)
$hoje = date('Y-m-d');
$limiteValidade = date('Y-m-d', strtotime('+60 days'));

// Se o banco não estiver pronto (migrations não rodadas), a Home abre mesmo assim, sem os números
try {
    $agendaHoje = array_values(array_filter(
        listar_agenda($pdo, ['data' => $hoje]),
        fn ($a) => $a['fg_status'] !== 'cancelado'
    ));
    $totalClientes = count(listar_clientes($pdo));
    $produtos = listar_produtos($pdo);
    $ciclos = listar_esterilizacoes($pdo);
    $dadosDisponiveis = true;
} catch (PDOException $erro) {
    $agendaHoje = $produtos = $ciclos = [];
    $totalClientes = null;
    $dadosDisponiveis = false;
}

// Produtos acabando: abaixo do mínimo, do que falta mais para o que falta menos
$produtosAcabando = array_values(array_filter($produtos, fn ($p) => $p['estoque_baixo']));
usort($produtosAcabando, fn ($a, $b) => ($b['qtd_estoque_minimo'] - $b['qtd_produto']) <=> ($a['qtd_estoque_minimo'] - $a['qtd_produto']));

// Produtos vencidos ou vencendo (com estoque)
$produtosVencendo = array_values(array_filter($produtos, fn ($p) => $p['dt_validade'] && $p['dt_validade'] <= $limiteValidade && $p['qtd_produto'] > 0));
usort($produtosVencendo, fn ($a, $b) => strcmp($a['dt_validade'], $b['dt_validade']));

// Esterilização: ciclos com problema (vencido/reprovado) e aguardando indicador
$ciclosProblema = array_values(array_filter($ciclos, fn ($c) => in_array($c['situacao'], ['vencido', 'reprovado'], true)));
$ciclosPendentes = array_values(array_filter($ciclos, fn ($c) => $c['situacao'] === 'pendente'));

$totalAvisos = count($produtosAcabando) + count($produtosVencendo) + count($ciclosProblema) + count($ciclosPendentes);

$atalhos = [
    ['titulo' => 'Agenda', 'descricao' => 'Marque e acompanhe os horários de atendimento.', 'icone' => 'bi-calendar-heart', 'link' => 'agendamentos/',
     'total' => $dadosDisponiveis ? count($agendaHoje) : null, 'rotulo' => ['agendamento hoje', 'agendamentos hoje']],
    ['titulo' => 'Clientes', 'descricao' => 'Cadastro e contato das clientes.', 'icone' => 'bi-people', 'link' => 'clientes/',
     'total' => $totalClientes, 'rotulo' => ['cliente cadastrada', 'clientes cadastradas']],
    ['titulo' => 'Produtos', 'descricao' => 'Catálogo de brincos, piercings e acessórios.', 'icone' => 'bi-gem', 'link' => 'produtos/',
     'total' => $dadosDisponiveis ? count($produtos) : null, 'rotulo' => ['produto ativo', 'produtos ativos']],
    ['titulo' => 'Estoque', 'descricao' => 'Entradas, saídas e produtos para repor.', 'icone' => 'bi-box-seam', 'link' => 'estoque/',
     'total' => $dadosDisponiveis ? count($produtosAcabando) : null, 'rotulo' => ['produto para repor', 'produtos para repor']],
];

/** Até 5 itens de uma lista, para os avisos não ficarem enormes. */
$primeiros = fn (array $lista) => array_slice($lista, 0, 5);

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
        <?php if ($dadosDisponiveis): ?>
            <span class="badge <?= $totalAvisos > 0 ? 'text-bg-warning' : 'text-bg-success' ?>">
                <?= $totalAvisos ?> <?= $totalAvisos === 1 ? 'aviso' : 'avisos' ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if (!$dadosDisponiveis): ?>
        <article class="alerta-home-item alerta-home-indisponivel">
            <span class="alerta-home-icone" aria-hidden="true"><i class="bi bi-database-exclamation"></i></span>
            <div class="alerta-home-conteudo">
                <h3>Banco de dados indisponível</h3>
                <p>Não foi possível consultar os dados. Confira a conexão e se as migrations foram executadas (<code>database/migrar.php</code>).</p>
            </div>
        </article>
    <?php else: ?>
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <article class="alerta-home-item h-100 <?= $produtosAcabando ? 'alerta-home-atencao' : 'alerta-home-ok' ?>">
                    <span class="alerta-home-icone" aria-hidden="true"><i class="bi <?= $produtosAcabando ? 'bi-exclamation-triangle' : 'bi-check-circle' ?>"></i></span>
                    <div class="alerta-home-conteudo">
                        <h3>Estoque acabando</h3>
                        <?php if (!$produtosAcabando): ?>
                            <p>Nenhum produto está abaixo do estoque mínimo.</p>
                        <?php else: ?>
                            <p><?= count($produtosAcabando) ?> <?= count($produtosAcabando) === 1 ? 'produto está' : 'produtos estão' ?> abaixo do mínimo:</p>
                            <ul class="alerta-home-lista">
                                <?php foreach ($primeiros($produtosAcabando) as $produto): ?>
                                    <li><strong><?= e($produto['nm_produto']) ?></strong><span><?= $produto['qtd_produto'] === 0 ? 'sem estoque' : $produto['qtd_produto'] . ' / mínimo ' . $produto['qtd_estoque_minimo'] ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a href="<?= url('estoque/') ?>" class="alerta-home-link">Ver estoque <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
            </div>
            <div class="col-12 col-lg-4">
                <article class="alerta-home-item h-100 <?= $produtosVencendo ? 'alerta-home-atencao' : 'alerta-home-ok' ?>">
                    <span class="alerta-home-icone" aria-hidden="true"><i class="bi <?= $produtosVencendo ? 'bi-calendar2-x' : 'bi-check-circle' ?>"></i></span>
                    <div class="alerta-home-conteudo">
                        <h3>Validade dos produtos</h3>
                        <?php if (!$produtosVencendo): ?>
                            <p>Nenhum produto vence nos próximos 60 dias.</p>
                        <?php else: ?>
                            <p>Vencidos ou vencendo em até 60 dias:</p>
                            <ul class="alerta-home-lista">
                                <?php foreach ($primeiros($produtosVencendo) as $produto): ?>
                                    <li><strong><?= e($produto['nm_produto']) ?></strong><span><?= $produto['dt_validade'] < $hoje ? 'venceu ' : '' ?><?= e(date('d/m/y', strtotime($produto['dt_validade']))) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a href="<?= url('estoque/') ?>" class="alerta-home-link">Ver estoque <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
            </div>
            <div class="col-12 col-lg-4">
                <article class="alerta-home-item h-100 <?= $ciclosProblema || $ciclosPendentes ? 'alerta-home-atencao' : 'alerta-home-ok' ?>">
                    <span class="alerta-home-icone" aria-hidden="true"><i class="bi <?= $ciclosProblema || $ciclosPendentes ? 'bi-shield-exclamation' : 'bi-shield-check' ?>"></i></span>
                    <div class="alerta-home-conteudo">
                        <h3>Esterilização</h3>
                        <?php if (!$ciclosProblema && !$ciclosPendentes): ?>
                            <p>Todos os ciclos ativos estão válidos.</p>
                        <?php else: ?>
                            <ul class="alerta-home-lista">
                                <?php foreach ($primeiros(array_merge($ciclosProblema, $ciclosPendentes)) as $ciclo): ?>
                                    <li><strong>Ciclo #<?= $ciclo['cd_esterilizacao'] ?> · <?= e(date('d/m', strtotime($ciclo['dt_esterilizacao']))) ?></strong>
                                        <span><?= ['vencido' => 'vencido', 'reprovado' => 'reprovado', 'pendente' => 'pendente'][$ciclo['situacao']] ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a href="<?= url('esterilizacao/') ?>" class="alerta-home-link">Abrir esterilização <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($dadosDisponiveis): ?>
    <section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-agenda-hoje">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h2 class="h4 mb-1" id="titulo-agenda-hoje">Agenda de hoje</h2>
                <p class="text-muted mb-0"><?= count($agendaHoje) === 0 ? 'Nenhum atendimento marcado para hoje.' : count($agendaHoje) . (count($agendaHoje) === 1 ? ' atendimento marcado.' : ' atendimentos marcados.') ?></p>
            </div>
            <a class="btn btn-sm btn-outline-primary" href="<?= url('agendamentos/criar.php') ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo agendamento</a>
        </div>
        <?php if ($agendaHoje): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($agendaHoje as $agendamento): ?>
                    <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0 bg-transparent">
                        <div>
                            <strong><?= e(date('H:i', strtotime($agendamento['dt_agendamento']))) ?>–<?= e(date('H:i', strtotime($agendamento['dt_fim']))) ?></strong>
                            · <?= e($agendamento['nm_cliente']) ?>
                            <span class="d-block small text-muted"><?= e($agendamento['ds_procedimento']) ?> · <?= e($agendamento['nu_telefone']) ?></span>
                        </div>
                        <span class="badge status-<?= e($agendamento['fg_status']) ?>"><?= $agendamento['fg_status'] === 'concluido' ? 'Concluído' : 'Agendado' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php endif; ?>

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
