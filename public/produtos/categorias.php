<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/categorias/categorias.php';

$categorias = listar_categorias($pdo, ['fg_status' => 'todos']);

$titulo = 'Categorias';
$menu = 'produtos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Categorias</h1>
        <p>Grupos usados para organizar o catálogo de produtos.</p>
    </div>
    <a href="<?= url('produtos/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao catálogo</a>
</div>

<section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-nova-categoria">
    <h2 class="h4 mb-3" id="titulo-nova-categoria">Nova categoria</h2>
    <form id="form-categoria" novalidate data-api="api/categorias.php" data-metodo="POST">
        <div class="alert alert-danger d-none" role="alert" data-erro-formulario></div>
        <div class="row g-2 align-items-start">
            <div class="col-12 col-md-8">
                <label class="visually-hidden" for="nm_categoria">Nome da categoria</label>
                <input class="form-control" id="nm_categoria" name="nm_categoria" maxlength="60" autocomplete="off" placeholder="Ex.: Colares">
            </div>
            <div class="col-12 col-md-4 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Adicionar</button>
            </div>
        </div>
    </form>
</section>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-lista-categorias">
    <h2 class="h4 mb-3" id="titulo-lista-categorias">Categorias cadastradas</h2>

    <?php if (!$categorias): ?>
        <div class="estado-vazio">
            <i class="bi bi-tags" aria-hidden="true"></i>
            <h2>Nenhuma categoria cadastrada</h2>
        </div>
    <?php else: ?>
        <div class="table-responsive tabela-celular-wrapper">
            <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Categorias de produtos.</caption>
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col" class="text-end">Produtos ativos</th>
                        <th scope="col">Situação</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td data-label="Nome"><strong><?= e($categoria['nm_categoria']) ?></strong></td>
                            <td data-label="Produtos ativos" class="text-md-end"><?= $categoria['qtd_produtos_ativos'] ?></td>
                            <td data-label="Situação">
                                <span class="badge <?= $categoria['fg_status'] ? 'status-concluido' : 'status-cancelado' ?>"><?= $categoria['fg_status'] ? 'Ativa' : 'Desativada' ?></span>
                            </td>
                            <td data-label="Ações" class="acoes">
                                <div class="d-inline-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-renomear-categoria="<?= $categoria['cd_categoria'] ?>" data-nome="<?= e($categoria['nm_categoria']) ?>">Renomear</button>
                                    <?php if ($categoria['fg_status']): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-desativar-categoria="<?= $categoria['cd_categoria'] ?>">Desativar</button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-reativar-categoria="<?= $categoria['cd_categoria'] ?>">Reativar</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="small text-muted mt-3 mb-0">Uma categoria só pode ser desativada quando não tem produtos ativos.</p>
    <?php endif; ?>
</section>

<script src="<?= url('assets/js/produtos.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
