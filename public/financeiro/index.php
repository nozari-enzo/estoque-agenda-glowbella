<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$titulo = 'Financeiro';
$menu = 'financeiro';
require __DIR__ . '/../../app/views/partials/header.php';

// Valores demonstrativos: a gravação e a integração real dependem do backend.
$movimentosDemo = [
    [
        'data' => '04/11/2026',
        'tipo' => 'atendimento',
        'tipo_rotulo' => 'Atendimento',
        'descricao' => 'Piercing Humanizado',
        'vinculo' => 'AG-108 · Ana C.',
        'natureza' => 'entrada',
        'valor_centavos' => 11900,
    ],
    [
        'data' => '04/11/2026',
        'tipo' => 'joia',
        'tipo_rotulo' => 'Venda de joia',
        'descricao' => 'Brinco argola lisa · aço cirúrgico',
        'vinculo' => 'AG-108 · Ana C.',
        'natureza' => 'entrada',
        'valor_centavos' => 8900,
    ],
    [
        'data' => '03/11/2026',
        'tipo' => 'atendimento',
        'tipo_rotulo' => 'Atendimento',
        'descricao' => 'Furo Humanizado Adulto',
        'vinculo' => 'AG-107 · Luiza M.',
        'natureza' => 'entrada',
        'valor_centavos' => 14900,
    ],
    [
        'data' => '03/11/2026',
        'tipo' => 'joia',
        'tipo_rotulo' => 'Venda de joia',
        'descricao' => 'Brinco ponto de luz',
        'vinculo' => 'AG-107 · Luiza M.',
        'natureza' => 'entrada',
        'valor_centavos' => 6900,
    ],
    [
        'data' => '02/11/2026',
        'tipo' => 'atendimento',
        'tipo_rotulo' => 'Atendimento',
        'descricao' => 'Sessão de Laserterapia',
        'vinculo' => 'AG-106 · Bia R.',
        'natureza' => 'entrada',
        'valor_centavos' => 6000,
    ],
    [
        'data' => '01/11/2026',
        'tipo' => 'despesa',
        'tipo_rotulo' => 'Compra',
        'descricao' => 'Reposição de luvas estéreis',
        'vinculo' => 'Compra de estoque',
        'natureza' => 'saida',
        'valor_centavos' => 9600,
    ],
    [
        'data' => '01/11/2026',
        'tipo' => 'despesa',
        'tipo_rotulo' => 'Compra',
        'descricao' => 'Reposição de gazes estéreis',
        'vinculo' => 'Compra de estoque',
        'natureza' => 'saida',
        'valor_centavos' => 4500,
    ],
];

$totaisCentavos = ['atendimento' => 0, 'joia' => 0, 'despesa' => 0];
foreach ($movimentosDemo as $movimento) {
    $totaisCentavos[$movimento['tipo']] += $movimento['valor_centavos'];
}
$totaisCentavos['saldo'] = $totaisCentavos['atendimento'] + $totaisCentavos['joia'] - $totaisCentavos['despesa'];

$formatarMoeda = static fn (int $centavos): string => 'R$ ' . number_format($centavos / 100, 2, ',', '.');
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Financeiro</h1>
        <p>Receitas de atendimentos e joias, junto aos gastos de compras.</p>
    </div>
    <a class="btn btn-outline-primary" href="<?= url('agendamentos/') ?>">
        <i class="bi bi-calendar-heart" aria-hidden="true"></i> Abrir Agenda
    </a>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-info-circle-fill mt-1" aria-hidden="true"></i>
    <div><strong>Protótipo visual.</strong> Os valores são demonstrativos e não são salvos. Na versão final, o serviço entra como receita ao concluir o atendimento; a joia fica em um lançamento separado no mesmo agendamento. Compras entram como saídas.</div>
</div>

<section class="row g-3 mb-4" aria-label="Resumo financeiro demonstrativo de novembro de 2026">
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Procedimentos</p><strong class="fs-5"><?= e($formatarMoeda($totaisCentavos['atendimento'])) ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-gem" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Venda de joias</p><strong class="fs-5"><?= e($formatarMoeda($totaisCentavos['joia'])) ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-cart-dash" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Gastos com compras</p><strong class="fs-5"><?= e($formatarMoeda($totaisCentavos['despesa'])) ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Saldo parcial</p><strong class="fs-5"><?= e($formatarMoeda($totaisCentavos['saldo'])) ?></strong></div>
            </div>
        </article>
    </div>
</section>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-movimentacoes" data-lista-filtro data-busca="busca-financeiro" data-tipo="tipo-financeiro">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-movimentacoes">Movimentações demonstrativas</h2>
            <p class="text-muted mb-0">Novembro de 2026 · serviços, joias e compras de estoque.</p>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-12 col-lg-7">
            <label class="visually-hidden" for="busca-financeiro">Buscar movimentação</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-financeiro" type="search" placeholder="Buscar descrição ou atendimento">
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <label class="visually-hidden" for="tipo-financeiro">Filtrar movimentações por tipo</label>
            <select class="form-select" id="tipo-financeiro">
                <option value="">Todas as movimentações</option>
                <option value="atendimento">Procedimentos</option>
                <option value="joia">Venda de joias</option>
                <option value="despesa">Gastos com compras</option>
            </select>
        </div>
    </div>

    <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

    <div class="table-responsive tabela-celular-wrapper">
        <table class="table table-hover tabela-glow tabela-celular-responsive tabela-financeiro mb-0">
            <caption class="visually-hidden">Movimentações financeiras demonstrativas relacionadas a atendimentos e compras.</caption>
            <colgroup>
                <col class="col-data">
                <col class="col-lancamento">
                <col class="col-tipo">
                <col class="col-vinculo">
                <col class="col-valor">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Data</th>
                    <th scope="col">Lançamento</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Agenda / origem</th>
                    <th scope="col" class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimentosDemo as $movimento): ?>
                    <?php $ehSaida = $movimento['natureza'] === 'saida'; ?>
                    <tr data-item-lista data-tipo-item="<?= e($movimento['tipo']) ?>">
                        <td data-label="Data"><?= e($movimento['data']) ?></td>
                        <td data-label="Lançamento"><strong><?= e($movimento['descricao']) ?></strong></td>
                        <td data-label="Tipo"><span class="badge <?= $ehSaida ? 'text-bg-secondary' : 'text-bg-primary' ?>"><?= e($movimento['tipo_rotulo']) ?></span></td>
                        <td data-label="Agenda / origem">
                            <?php if ($movimento['tipo'] === 'despesa'): ?>
                                <?= e($movimento['vinculo']) ?>
                            <?php else: ?>
                                <a href="<?= url('agendamentos/') ?>" aria-label="Abrir Agenda para o atendimento <?= e($movimento['vinculo']) ?>">
                                    <?= e($movimento['vinculo']) ?> <i class="bi bi-box-arrow-up-right small" aria-hidden="true"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td data-label="Valor" class="text-md-end fw-semibold <?= $ehSaida ? 'text-danger' : 'text-success' ?>">
                            <?= $ehSaida ? '−' : '+' ?><?= e($formatarMoeda($movimento['valor_centavos'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr data-sem-resultados class="d-none">
                    <td colspan="5" class="text-center text-muted py-4">Nenhuma movimentação corresponde aos filtros.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle" aria-hidden="true"></i> O preço do procedimento e o preço de venda da joia ficam em linhas separadas, ligados ao mesmo atendimento. As compras aparecem como saídas; este saldo parcial não inclui outras despesas ou taxas.</p>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
