<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/esterilizacoes/esterilizacoes.php';

$ciclos = listar_esterilizacoes($pdo);

$situacoes = [
    'valido'    => ['Válido', 'text-bg-success'],
    'pendente'  => ['Pendente', 'text-bg-warning'],
    'vencido'   => ['Vencido', 'text-bg-danger'],
    'reprovado' => ['Reprovado', 'text-bg-danger'],
];
$indicadores = ['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'reprovado' => 'Reprovado'];

$contagem = array_fill_keys(array_keys($situacoes), 0);
foreach ($ciclos as $ciclo) {
    $contagem[$ciclo['situacao']]++;
}

$titulo = 'Esterilização';
$menu = 'esterilizacao';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Ciclos de esterilização</h1>
        <p>Histórico de processamento, indicadores e validade dos itens esterilizados.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('esterilizacao/criar.php') ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar ciclo</a>
</div>

<?php if (isset($_GET['salvo'])): ?>
    <div class="alert alert-success" role="alert">Ciclo salvo com sucesso!</div>
<?php endif; ?>

<section class="row g-3 mb-4" aria-label="Resumo dos ciclos">
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow p-3 h-100">
            <div class="d-flex align-items-center gap-3"><span class="icone-circulo"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></span><div><p class="small text-muted mb-1">Ciclos registrados</p><strong class="fs-4"><?= count($ciclos) ?></strong></div></div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow p-3 h-100">
            <div class="d-flex align-items-center gap-3"><span class="icone-circulo"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><p class="small text-muted mb-1">Válidos</p><strong class="fs-4"><?= $contagem['valido'] ?></strong></div></div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow p-3 h-100">
            <div class="d-flex align-items-center gap-3"><span class="icone-circulo"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span><div><p class="small text-muted mb-1">Aguardando indicador</p><strong class="fs-4"><?= $contagem['pendente'] ?></strong></div></div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow p-3 h-100">
            <div class="d-flex align-items-center gap-3"><span class="icone-circulo"><i class="bi bi-calendar-x" aria-hidden="true"></i></span><div><p class="small text-muted mb-1">Vencidos ou reprovados</p><strong class="fs-4"><?= $contagem['vencido'] + $contagem['reprovado'] ?></strong></div></div>
        </article>
    </div>
</section>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-historico-ciclos" data-lista-filtro data-busca="busca-ciclos" data-status="situacao-ciclos">
    <div class="mb-3">
        <h2 class="h4 mb-1" id="titulo-historico-ciclos">Histórico</h2>
        <p class="text-muted mb-0">Do ciclo mais recente para o mais antigo. Busque pelo método ou por um produto.</p>
    </div>

    <?php if (!$ciclos): ?>
        <div class="estado-vazio">
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <h2>Nenhum ciclo registrado</h2>
            <p class="mb-0">Clique em <strong>Registrar ciclo</strong> para cadastrar o primeiro.</p>
        </div>
    <?php else: ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-lg-8">
                <label class="visually-hidden" for="busca-ciclos">Buscar ciclo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input class="form-control" id="busca-ciclos" type="search" placeholder="Buscar por método ou produto">
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <label class="visually-hidden" for="situacao-ciclos">Filtrar situação</label>
                <select class="form-select" id="situacao-ciclos">
                    <option value="">Todas as situações</option>
                    <?php foreach ($situacoes as $chave => [$rotulo]): ?>
                        <option value="<?= $chave ?>"><?= $rotulo ?> (<?= $contagem[$chave] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

        <div class="table-responsive tabela-celular-wrapper">
            <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Ciclos de esterilização.</caption>
                <thead>
                    <tr>
                        <th scope="col">Ciclo</th>
                        <th scope="col">Validade</th>
                        <th scope="col">Indicadores</th>
                        <th scope="col">Produtos</th>
                        <th scope="col">Situação</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ciclos as $ciclo): ?>
                        <?php [$rotuloSituacao, $classeSituacao] = $situacoes[$ciclo['situacao']]; ?>
                        <tr data-item-lista data-status-item="<?= e($ciclo['situacao']) ?>">
                            <td data-label="Ciclo">
                                <strong>#<?= $ciclo['cd_esterilizacao'] ?> · <?= e($ciclo['nm_metodo']) ?></strong>
                                <span class="d-block small text-muted"><?= e(date('d/m/Y', strtotime($ciclo['dt_esterilizacao']))) ?></span>
                            </td>
                            <td data-label="Validade"><?= e(date('d/m/Y', strtotime($ciclo['dt_validade_esterilizacao']))) ?></td>
                            <td data-label="Indicadores">
                                <span class="d-block small">Químico: <strong><?= e($indicadores[$ciclo['fg_indicador_quimico']]) ?></strong></span>
                                <span class="d-block small">Biológico: <strong><?= e($indicadores[$ciclo['fg_indicador_biologico']]) ?></strong></span>
                            </td>
                            <td data-label="Produtos">
                                <?php foreach ($ciclo['produtos'] as $item): ?>
                                    <span class="d-block small"><?= $item['qtd_produto'] ?>× <?= e($item['nm_produto']) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td data-label="Situação"><span class="badge <?= $classeSituacao ?>"><?= $rotuloSituacao ?></span></td>
                            <td data-label="Ações" class="acoes">
                                <div class="d-inline-flex flex-wrap gap-2">
                                    <a href="<?= url('esterilizacao/editar.php?id=' . $ciclo['cd_esterilizacao']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-desativar-ciclo="<?= $ciclo['cd_esterilizacao'] ?>">Excluir</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultados class="d-none">
                        <td colspan="6" class="text-center text-muted py-4">Nenhum ciclo corresponde à busca.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle" aria-hidden="true"></i> O registro organiza o controle interno; os critérios e prazos sanitários devem seguir as normas da vigilância sanitária.</p>

<script src="<?= url('assets/js/esterilizacao.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
