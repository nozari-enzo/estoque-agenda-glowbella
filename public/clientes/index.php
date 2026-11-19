<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';

// ?status=inativas mostra as clientes desativadas (para reativar)
$verInativas = ($_GET['status'] ?? '') === 'inativas';
$clientes = listar_clientes($pdo, ['fg_status' => $verInativas ? '0' : '1']);

$titulo = 'Clientes';
$menu = 'clientes';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Clientes</h1>
        <p>Cadastro e contato das clientes.</p>
    </div>
    <a href="<?= url('clientes/criar.php') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Nova cliente
    </a>
</div>

<?php if (isset($_GET['salvo'])): ?>
    <div class="alert alert-success" role="alert">Cliente salva com sucesso!</div>
<?php endif; ?>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-lista-clientes" data-lista-filtro data-busca="busca-clientes">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-lista-clientes"><?= $verInativas ? 'Clientes desativadas' : 'Clientes ativas' ?></h2>
            <p class="text-muted mb-0">Pesquise pelo nome ou pelo telefone.</p>
        </div>
        <div class="btn-group" role="group" aria-label="Mostrar clientes">
            <a href="<?= url('clientes/') ?>" class="btn btn-sm <?= $verInativas ? 'btn-outline-primary' : 'btn-primary' ?>"<?= $verInativas ? '' : ' aria-current="page"' ?>>Ativas</a>
            <a href="<?= url('clientes/?status=inativas') ?>" class="btn btn-sm <?= $verInativas ? 'btn-primary' : 'btn-outline-primary' ?>"<?= $verInativas ? ' aria-current="page"' : '' ?>>Desativadas</a>
        </div>
    </div>

    <?php if (!$clientes): ?>
        <div class="estado-vazio">
            <i class="bi bi-people" aria-hidden="true"></i>
            <h2><?= $verInativas ? 'Nenhuma cliente desativada' : 'Nenhuma cliente cadastrada' ?></h2>
            <?php if (!$verInativas): ?>
                <p class="mb-0">Clique em <strong>Nova cliente</strong> para cadastrar a primeira.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <label class="visually-hidden" for="busca-clientes">Buscar cliente</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-clientes" type="search" placeholder="Buscar por nome ou telefone">
            </div>
        </div>

        <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

        <div class="table-responsive tabela-celular-wrapper">
            <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Lista de clientes.</caption>
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Telefone</th>
                        <th scope="col">Nascimento</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr data-item-lista>
                            <td data-label="Nome"><strong><?= e($cliente['nm_cliente']) ?></strong></td>
                            <td data-label="Telefone"><?= e($cliente['nu_telefone']) ?></td>
                            <td data-label="Nascimento">
                                <?= $cliente['dt_nascimento'] ? e(date('d/m/Y', strtotime($cliente['dt_nascimento']))) : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td data-label="Ações" class="acoes">
                                <div class="d-inline-flex flex-wrap gap-2">
                                <?php if ($verInativas): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-reativar="<?= $cliente['cd_cliente'] ?>">Reativar</button>
                                <?php else: ?>
                                    <a href="<?= url('clientes/editar.php?id=' . $cliente['cd_cliente']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-desativar="<?= $cliente['cd_cliente'] ?>">Desativar</button>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultados class="d-none">
                        <td colspan="4" class="text-center text-muted py-4">Nenhuma cliente encontrada com essa busca.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script src="<?= url('assets/js/clientes.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
