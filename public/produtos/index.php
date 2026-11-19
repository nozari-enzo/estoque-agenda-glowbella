<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/produtos/produtos.php';

// ?status=inativos mostra os produtos desativados (para reativar)
$verInativos = ($_GET['status'] ?? '') === 'inativos';
$produtos = listar_produtos($pdo, ['fg_status' => $verInativos ? '0' : '1']);

// Números do resumo (só dos ativos)
$ativos = $verInativos ? listar_produtos($pdo) : $produtos;
$totalProcedimento = count(array_filter($ativos, fn ($p) => $p['fg_finalidade'] === 'procedimento'));
$totalVenda = count(array_filter($ativos, fn ($p) => $p['fg_finalidade'] === 'venda'));
$totalEstoqueBaixo = count(array_filter($ativos, fn ($p) => $p['estoque_baixo']));

$finalidades = ['procedimento' => 'Uso em procedimento', 'venda' => 'Venda / aplicação'];

$titulo = 'Produtos';
$menu = 'produtos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Produtos</h1>
        <p>Catálogo de itens usados em procedimentos e disponíveis para venda ou aplicação.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="<?= url('produtos/categorias.php') ?>">
            <i class="bi bi-tags" aria-hidden="true"></i> Categorias
        </a>
        <a class="btn btn-primary" href="<?= url('produtos/criar.php') ?>">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Novo produto
        </a>
    </div>
</div>

<?php if (isset($_GET['salvo'])): ?>
    <div class="alert alert-success" role="alert">Produto salvo com sucesso!</div>
<?php endif; ?>

<section class="row g-3 mb-4" aria-label="Resumo do catálogo">
    <div class="col-12 col-md-4">
        <article class="card-glow p-3 h-100">
            <p class="text-muted small mb-1">Uso em procedimento</p>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="fs-4"><?= $totalProcedimento ?></strong>
                <span class="icone-circulo"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
            </div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="card-glow p-3 h-100">
            <p class="text-muted small mb-1">Venda / aplicação</p>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="fs-4"><?= $totalVenda ?></strong>
                <span class="icone-circulo"><i class="bi bi-gem" aria-hidden="true"></i></span>
            </div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="card-glow p-3 h-100">
            <p class="text-muted small mb-1">Abaixo do estoque mínimo</p>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="fs-4"><?= $totalEstoqueBaixo ?></strong>
                <span class="icone-circulo"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
            </div>
        </article>
    </div>
</section>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-catalogo" data-lista-filtro data-busca="busca-produtos" data-tipo="tipo-produtos">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-catalogo"><?= $verInativos ? 'Produtos desativados' : 'Catálogo' ?></h2>
            <p class="text-muted mb-0">Pesquise pelo nome, categoria ou lote.</p>
        </div>
        <div class="btn-group" role="group" aria-label="Mostrar produtos">
            <a href="<?= url('produtos/') ?>" class="btn btn-sm <?= $verInativos ? 'btn-outline-primary' : 'btn-primary' ?>"<?= $verInativos ? '' : ' aria-current="page"' ?>>Ativos</a>
            <a href="<?= url('produtos/?status=inativos') ?>" class="btn btn-sm <?= $verInativos ? 'btn-primary' : 'btn-outline-primary' ?>"<?= $verInativos ? ' aria-current="page"' : '' ?>>Desativados</a>
        </div>
    </div>

    <?php if (!$produtos): ?>
        <div class="estado-vazio">
            <i class="bi bi-gem" aria-hidden="true"></i>
            <h2><?= $verInativos ? 'Nenhum produto desativado' : 'Nenhum produto cadastrado' ?></h2>
            <?php if (!$verInativos): ?>
                <p class="mb-0">Clique em <strong>Novo produto</strong> para cadastrar o primeiro.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-lg-8">
                <label class="visually-hidden" for="busca-produtos">Buscar produto</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input class="form-control" id="busca-produtos" type="search" placeholder="Buscar por nome, categoria ou lote">
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <label class="visually-hidden" for="tipo-produtos">Filtrar finalidade</label>
                <select class="form-select" id="tipo-produtos">
                    <option value="">Todas as finalidades</option>
                    <?php foreach ($finalidades as $chave => $rotulo): ?>
                        <option value="<?= $chave ?>"><?= e($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

        <div class="table-responsive tabela-celular-wrapper">
            <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Catálogo de produtos.</caption>
                <thead>
                    <tr>
                        <th scope="col">Produto</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Finalidade</th>
                        <th scope="col" class="text-end">Preço</th>
                        <th scope="col" class="text-end">Estoque</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <tr data-item-lista data-tipo-item="<?= e($produto['fg_finalidade']) ?>"<?= $produto['estoque_baixo'] ? ' class="estoque-baixo"' : '' ?>>
                            <td data-label="Produto">
                                <strong><?= e($produto['nm_produto']) ?></strong>
                                <?php if ($produto['ds_lote'] || $produto['dt_validade']): ?>
                                    <span class="d-block small text-muted">
                                        <?= $produto['ds_lote'] ? 'Lote ' . e($produto['ds_lote']) : '' ?>
                                        <?= $produto['ds_lote'] && $produto['dt_validade'] ? '·' : '' ?>
                                        <?= $produto['dt_validade'] ? 'Validade ' . e(date('d/m/Y', strtotime($produto['dt_validade']))) : '' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Categoria"><?= e($produto['nm_categoria']) ?></td>
                            <td data-label="Finalidade">
                                <span class="badge <?= $produto['fg_finalidade'] === 'procedimento' ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= e($finalidades[$produto['fg_finalidade']]) ?></span>
                            </td>
                            <td data-label="Preço" class="text-md-end text-nowrap">R$ <?= number_format($produto['vl_produto'], 2, ',', '.') ?></td>
                            <td data-label="Estoque" class="text-md-end">
                                <strong><?= $produto['qtd_produto'] ?></strong>
                                <?php if ($produto['estoque_baixo']): ?>
                                    <span class="badge text-bg-warning">Estoque baixo</span>
                                <?php endif; ?>
                                <span class="d-block small text-muted">mínimo <?= $produto['qtd_estoque_minimo'] ?></span>
                            </td>
                            <td data-label="Ações" class="acoes">
                                <div class="d-inline-flex flex-wrap gap-2">
                                    <?php if ($verInativos): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-reativar-produto="<?= $produto['cd_produto'] ?>">Reativar</button>
                                    <?php else: ?>
                                        <a href="<?= url('produtos/editar.php?id=' . $produto['cd_produto']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-desativar-produto="<?= $produto['cd_produto'] ?>">Desativar</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultados class="d-none">
                        <td colspan="6" class="text-center text-muted py-4">Nenhum produto corresponde à busca.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script src="<?= url('assets/js/produtos.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
