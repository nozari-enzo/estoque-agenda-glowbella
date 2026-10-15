<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$titulo = 'Estoque';
$menu = 'estoque';
require __DIR__ . '/../../app/views/partials/header.php';

$itensDemo = [
    [
        'nome' => 'Luva estéril descartável · tamanho M',
        'tipo' => 'Uso e consumo / procedimento',
        'tipo_chave' => 'consumo',
        'lote' => 'LU-26-041',
        'validade' => '15/02/2027',
        'quantidade' => '18 pares',
        'situacao' => 'Estoque baixo',
        'status_chave' => 'baixo',
        'badge' => 'text-bg-warning',
        'detalhe' => 'Mínimo: 24 pares',
    ],
    [
        'nome' => 'Gaze estéril · 7,5 cm',
        'tipo' => 'Uso e consumo / procedimento',
        'tipo_chave' => 'consumo',
        'lote' => 'GA-26-021',
        'validade' => '22/10/2026',
        'quantidade' => '32 pacotes',
        'situacao' => 'Validade próxima',
        'status_chave' => 'validade',
        'badge' => 'text-bg-warning',
        'detalhe' => 'Conferir integridade da embalagem',
    ],
    [
        'nome' => 'Álcool 70% · 1 L',
        'tipo' => 'Uso e consumo / procedimento',
        'tipo_chave' => 'consumo',
        'lote' => 'AL-26-004',
        'validade' => '18/12/2026',
        'quantidade' => '5 unidades',
        'situacao' => 'Regular',
        'status_chave' => 'regular',
        'badge' => 'text-bg-success',
        'detalhe' => 'Mínimo: 3 unidades',
    ],
    [
        'nome' => 'Cateter · calibre 18G',
        'tipo' => 'Uso e consumo / procedimento',
        'tipo_chave' => 'consumo',
        'lote' => 'CA-26-018',
        'validade' => '10/03/2027',
        'quantidade' => '40 unidades',
        'situacao' => 'Regular',
        'status_chave' => 'regular',
        'badge' => 'text-bg-success',
        'detalhe' => 'Mínimo: 20 unidades',
    ],
    [
        'nome' => 'Brinco argola lisa · aço cirúrgico',
        'tipo' => 'Venda / aplicação',
        'tipo_chave' => 'venda',
        'lote' => '—',
        'validade' => '—',
        'quantidade' => '12 unidades',
        'situacao' => 'Regular',
        'status_chave' => 'regular',
        'badge' => 'text-bg-success',
        'detalhe' => 'Joia para escolha no atendimento',
    ],
];

$itensBaixos = count(array_filter($itensDemo, fn(array $item): bool => $item['status_chave'] === 'baixo'));
$itensVencendo = count(array_filter($itensDemo, fn(array $item): bool => $item['status_chave'] === 'validade'));
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Estoque</h1>
        <p>Consulte quantidades, lotes e condições dos itens.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="<?= url('esterilizacao/') ?>">
            <i class="bi bi-shield-check" aria-hidden="true"></i> Esterilização
        </a>
        <button class="btn btn-primary" type="button">
            <i class="bi bi-arrow-left-right" aria-hidden="true"></i> Registrar movimentação
        </button>
    </div>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-info-circle-fill mt-1" aria-hidden="true"></i>
    <div><strong>Protótipo visual.</strong> Os dados são demonstrativos. Os botões não registram movimentações nem alteram o estoque.</div>
</div>

<section class="row g-3 mb-4" aria-label="Resumo demonstrativo do estoque">
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Itens demonstrativos</p><strong class="fs-4">5</strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Abaixo do mínimo</p><strong class="fs-4"><?= $itensBaixos ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Validade próxima</p><strong class="fs-4"><?= $itensVencendo ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Lotes identificados</p><strong class="fs-4">4</strong></div>
            </div>
        </article>
    </div>
</section>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-itens-estoque" data-lista-filtro data-busca="busca-estoque" data-tipo="tipo-estoque" data-status="status-estoque">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-itens-estoque">Itens e lotes</h2>
            <p class="text-muted mb-0">Busque pelo nome, tipo ou número do lote.</p>
        </div>
        <button class="btn btn-outline-primary" type="button">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Adicionar lote
        </button>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-12 col-lg-6">
            <label class="visually-hidden" for="busca-estoque">Buscar item ou lote</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-estoque" type="search" placeholder="Buscar item ou lote">
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <label class="visually-hidden" for="tipo-estoque">Filtrar por tipo</label>
            <select class="form-select" id="tipo-estoque">
                <option value="">Todos os tipos</option>
                <option value="consumo">Uso e consumo / procedimento</option>
                <option value="venda">Venda / aplicação</option>
            </select>
        </div>
        <div class="col-6 col-lg-3">
            <label class="visually-hidden" for="status-estoque">Filtrar por situação</label>
            <select class="form-select" id="status-estoque">
                <option value="">Todas as situações</option>
                <option value="baixo">Estoque baixo</option>
                <option value="validade">Validade próxima</option>
                <option value="regular">Regular</option>
            </select>
        </div>
    </div>

    <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

    <div class="table-responsive tabela-celular-wrapper">
        <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
            <caption class="visually-hidden">Lista demonstrativa de produtos, lotes, quantidades e situações.</caption>
            <thead>
                <tr>
                    <th scope="col">Item</th>
                    <th scope="col">Lote</th>
                    <th scope="col">Validade fabricante</th>
                    <th scope="col" class="text-end">Quantidade</th>
                    <th scope="col">Situação</th>
                    <th scope="col" class="acoes">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itensDemo as $item): ?>
                    <tr data-item-lista data-tipo-item="<?= e($item['tipo_chave']) ?>" data-status-item="<?= e($item['status_chave']) ?>">
                        <td data-label="Item">
                            <strong><?= e($item['nome']) ?></strong>
                            <span class="d-block small text-muted"><?= e($item['tipo']) ?></span>
                        </td>
                        <td data-label="Lote"><?= e($item['lote']) ?></td>
                        <td data-label="Validade fabricante"><?= e($item['validade']) ?></td>
                        <td data-label="Quantidade" class="text-md-end"><?= e($item['quantidade']) ?>
                            <span class="d-block small text-muted"><?= e($item['detalhe']) ?></span>
                        </td>
                        <td data-label="Situação"><span class="badge <?= e($item['badge']) ?>"><?= e($item['situacao']) ?></span></td>
                        <td data-label="Ação" class="acoes"><button class="btn btn-sm btn-outline-primary" type="button">Detalhes</button></td>
                    </tr>
                <?php endforeach; ?>
                <tr data-sem-resultados class="d-none">
                    <td colspan="6" class="text-center text-muted py-4">Nenhum item corresponde aos filtros.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<div class="d-flex flex-wrap justify-content-between gap-2 mt-3 small text-muted">
    <span><i class="bi bi-info-circle" aria-hidden="true"></i> Dados de demonstração para o protótipo.</span>
    <span>Regras de lote e validade serão conectadas ao banco após a definição do modelo.</span>
</div>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
