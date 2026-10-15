<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$titulo = 'Produtos';
$menu = 'produtos';
require __DIR__ . '/../../app/views/partials/header.php';

$produtosDemo = [
    ['nome' => 'Luva estéril · tamanho M', 'categoria' => 'Insumos', 'tipo' => 'Uso e consumo / procedimento', 'tipo_chave' => 'consumo', 'rastreamento' => 'Lote e validade', 'estoque' => '18 pares'],
    ['nome' => 'Gaze estéril · 7,5 cm', 'categoria' => 'Insumos', 'tipo' => 'Uso e consumo / procedimento', 'tipo_chave' => 'consumo', 'rastreamento' => 'Lote e validade', 'estoque' => '32 pacotes'],
    ['nome' => 'Brinco argola lisa · aço cirúrgico', 'categoria' => 'Brincos', 'tipo' => 'Venda / aplicação', 'tipo_chave' => 'venda', 'rastreamento' => 'Definir regra de lote', 'estoque' => '12 unidades'],
    ['nome' => 'Cateter · calibre 18G', 'categoria' => 'Insumos', 'tipo' => 'Uso e consumo / procedimento', 'tipo_chave' => 'consumo', 'rastreamento' => 'Lote e validade', 'estoque' => '40 unidades'],
];
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Produtos</h1>
        <p>Catálogo de itens usados em procedimentos e disponíveis para venda ou aplicação.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('produtos/criar.php') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Novo produto
    </a>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-info-circle-fill mt-1" aria-hidden="true"></i>
    <div><strong>Protótipo visual.</strong> Os registros são demonstrativos; cadastro e edição ainda não gravam dados. A cliente escolhe a joia no dia do atendimento.</div>
</div>

<section class="row g-3 mb-4" aria-label="Resumo demonstrativo do catálogo">
    <div class="col-12 col-md-4">
        <article class="card-glow p-3 h-100">
            <p class="text-muted small mb-1">Uso e consumo / procedimento</p>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="fs-4">12</strong>
                <span class="icone-circulo"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
            </div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="card-glow p-3 h-100">
            <p class="text-muted small mb-1">Venda / aplicação</p>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="fs-4">6</strong>
                <span class="icone-circulo"><i class="bi bi-gem" aria-hidden="true"></i></span>
            </div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="card-glow p-3 h-100">
            <p class="text-muted small mb-1">Kits de procedimento</p>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="fs-4">1</strong>
                <span class="icone-circulo"><i class="bi bi-boxes" aria-hidden="true"></i></span>
            </div>
        </article>
    </div>
</section>

<section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-catalogo" data-lista-filtro data-busca="busca-produtos" data-tipo="tipo-produtos">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-catalogo">Catálogo</h2>
            <p class="text-muted mb-0">Classifique cada item pela forma como é usado no negócio.</p>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-12 col-lg-8">
            <label class="visually-hidden" for="busca-produtos">Buscar produto</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-produtos" type="search" placeholder="Buscar por nome ou categoria">
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <label class="visually-hidden" for="tipo-produtos">Filtrar finalidade</label>
            <select class="form-select" id="tipo-produtos">
                <option value="">Todas as finalidades</option>
                <option value="consumo">Uso e consumo / procedimento</option>
                <option value="venda">Venda / aplicação</option>
            </select>
        </div>
    </div>

    <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

    <div class="table-responsive tabela-celular-wrapper">
        <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
            <caption class="visually-hidden">Catálogo demonstrativo de produtos.</caption>
            <thead>
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Finalidade</th>
                    <th scope="col">Rastreamento</th>
                    <th scope="col" class="text-end">Estoque</th>
                    <th scope="col" class="acoes">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtosDemo as $produto): ?>
                    <tr data-item-lista data-tipo-item="<?= e($produto['tipo_chave']) ?>">
                        <td data-label="Produto"><strong><?= e($produto['nome']) ?></strong></td>
                        <td data-label="Categoria"><?= e($produto['categoria']) ?></td>
                        <td data-label="Finalidade"><span class="badge <?= $produto['tipo_chave'] === 'consumo' ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= e($produto['tipo']) ?></span></td>
                        <td data-label="Rastreamento"><?= e($produto['rastreamento']) ?></td>
                        <td data-label="Estoque" class="text-md-end"><?= e($produto['estoque']) ?></td>
                        <td data-label="Ação" class="acoes"><button class="btn btn-sm btn-outline-primary" type="button">Editar</button></td>
                    </tr>
                <?php endforeach; ?>
                <tr data-sem-resultados class="d-none">
                    <td colspan="6" class="text-center text-muted py-4">Nenhum produto corresponde à busca.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-kits">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-kits">Kits de procedimento</h2>
            <p class="text-muted mb-0">Composição demonstrativa para agrupar insumos recorrentes.</p>
        </div>
        <button class="btn btn-outline-primary" type="button"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo kit</button>
    </div>
    <div class="kit-prototipo p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div><span class="badge text-bg-light">Exemplo para validação</span><h3 class="h5 mt-2 mb-1">Kit Furo Lóbulo</h3></div>
            <button class="btn btn-sm btn-outline-primary" type="button">Editar composição</button>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between gap-3 px-0 bg-transparent"><span>Luvas estéreis</span><strong>2 pares</strong></li>
            <li class="list-group-item d-flex justify-content-between gap-3 px-0 bg-transparent"><span>Cateteres</span><strong>2 unidades</strong></li>
            <li class="list-group-item d-flex justify-content-between gap-3 px-0 bg-transparent"><span>Pacotes de gaze</span><strong>3 pacotes</strong></li>
        </ul>
        <p class="small text-muted mt-3 mb-0">Composição retirada do exemplo fornecido; validar com a equipe antes da integração.</p>
    </div>
</section>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
