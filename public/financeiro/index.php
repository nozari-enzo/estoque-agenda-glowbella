<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/agenda/agenda.php';
require __DIR__ . '/../../app/repositories/movimentacoes/movimentacoes.php';
require __DIR__ . '/../../app/repositories/produtos/produtos.php';
require __DIR__ . '/../../app/services/estoque.php';

// Mês do relatório (?mes=11&ano=2026). Valores inválidos voltam para o mês atual.
$numeroDaUrl = fn (string $nome): int => is_string($_GET[$nome] ?? null) && ctype_digit($_GET[$nome]) ? (int) $_GET[$nome] : 0;
$mes = $numeroDaUrl('mes');
$ano = $numeroDaUrl('ano');
if ($mes < 1 || $mes > 12) {
    $mes = (int) date('n');
}
if ($ano < 2000 || $ano > 2100) {
    $ano = (int) date('Y');
}

$inicioMes = sprintf('%04d-%02d-01', $ano, $mes);
$fimMes = date('Y-m-t', strtotime($inicioMes));
$mesAnterior = strtotime("$inicioMes -1 month");
$proximoMes = strtotime("$inicioMes +1 month");
$nomesMeses = [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$nomeMes = $nomesMeses[$mes] . ' de ' . $ano;

// Receitas: agendamentos do mês com valor (concluído = recebido, agendado = previsto)
$agendamentos = array_filter(
    listar_agenda($pdo, ['de' => $inicioMes, 'ate' => $fimMes]),
    fn ($a) => $a['fg_status'] !== 'cancelado'
);

// Vendas de balcão: saídas de estoque com motivo "Venda" (o valor é informado ao registrar a saída)
$vendas = array_filter(
    listar_movimentacoes($pdo, ['fg_tipo' => 'saida', 'de' => $inicioMes, 'ate' => $fimMes, 'limite' => 500]),
    fn ($m) => $m['ds_motivo'] === MOTIVO_VENDA && $m['vl_total'] !== null
);

// Gastos: entradas com motivo "Compra fornecedor" × preço de compra do produto.
// Estoque inicial, devolução e ajuste não são dinheiro saindo, então não contam.
$precoCompra = array_column(listar_produtos($pdo, ['fg_status' => 'todos']), 'vl_compra', 'cd_produto');
$compras = array_filter(
    listar_movimentacoes($pdo, ['fg_tipo' => 'entrada', 'de' => $inicioMes, 'ate' => $fimMes, 'limite' => 500]),
    fn ($m) => $m['ds_motivo'] === MOTIVO_COMPRA
);

$lancamentos = [];
$totais = ['recebido' => 0.0, 'vendas' => 0.0, 'previsto' => 0.0, 'compras' => 0.0];
$porCliente = [];
$semValor = 0;

foreach ($agendamentos as $agendamento) {
    if ($agendamento['vl_procedimento'] === null) {
        $semValor++;
        continue;
    }

    $recebido = $agendamento['fg_status'] === 'concluido';
    $totais[$recebido ? 'recebido' : 'previsto'] += $agendamento['vl_procedimento'];

    $cliente = &$porCliente[$agendamento['cd_cliente']];
    $cliente['nome'] = $agendamento['nm_cliente'];
    $cliente['atendimentos'] = ($cliente['atendimentos'] ?? 0) + 1;
    $cliente['recebido'] = ($cliente['recebido'] ?? 0) + ($recebido ? $agendamento['vl_procedimento'] : 0);
    $cliente['previsto'] = ($cliente['previsto'] ?? 0) + ($recebido ? 0 : $agendamento['vl_procedimento']);
    unset($cliente);

    $lancamentos[] = [
        'data'      => $agendamento['dt_agendamento'],
        'descricao' => $agendamento['ds_procedimento'],
        'tipo'      => $recebido ? 'recebido' : 'previsto',
        'vinculo'   => $agendamento['nm_cliente'],
        'link'      => url('agendamentos/editar.php?id=' . $agendamento['cd_agenda']),
        'valor'     => $agendamento['vl_procedimento'],
    ];
}

foreach ($vendas as $venda) {
    $totais['vendas'] += $venda['vl_total'];

    $lancamentos[] = [
        'data'      => $venda['dt_movimentacao'],
        'descricao' => $venda['qtd_movimentada'] . '× ' . $venda['nm_produto'],
        'tipo'      => 'venda',
        'vinculo'   => 'Saída de estoque',
        'link'      => url('estoque/?produto=' . $venda['cd_produto'] . '#historico'),
        'valor'     => $venda['vl_total'],
    ];
}

foreach ($compras as $compra) {
    $valor = $compra['qtd_movimentada'] * ($precoCompra[$compra['cd_produto']] ?? 0);
    $totais['compras'] += $valor;

    $lancamentos[] = [
        'data'      => $compra['dt_movimentacao'],
        'descricao' => $compra['qtd_movimentada'] . '× ' . $compra['nm_produto'],
        'tipo'      => 'compra',
        'vinculo'   => 'Entrada de estoque',
        'link'      => url('estoque/?produto=' . $compra['cd_produto'] . '#historico'),
        'valor'     => $valor,
    ];
}

usort($lancamentos, fn ($a, $b) => strcmp($b['data'], $a['data']));
uasort($porCliente, fn ($a, $b) => ($b['recebido'] + $b['previsto']) <=> ($a['recebido'] + $a['previsto']));

$entrou = $totais['recebido'] + $totais['vendas'];
$saldo = $entrou - $totais['compras'];
$tipos = [
    'recebido' => ['Atendimento concluído', 'text-bg-primary'],
    'venda'    => ['Venda', 'text-bg-success'],
    'previsto' => ['Atendimento agendado', 'status-agendado'],
    'compra'   => ['Compra', 'text-bg-secondary'],
];
$moeda = fn (float $valor): string => 'R$ ' . number_format($valor, 2, ',', '.');

$titulo = 'Financeiro';
$menu = 'financeiro';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Financeiro</h1>
        <p>Receitas dos atendimentos e das vendas de balcão, e gastos com compras de estoque.</p>
    </div>
    <a class="btn btn-outline-primary" href="<?= url('agendamentos/') ?>">
        <i class="bi bi-calendar-heart" aria-hidden="true"></i> Abrir Agenda
    </a>
</div>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <a href="<?= url('financeiro/?mes=' . date('n', $mesAnterior) . '&ano=' . date('Y', $mesAnterior)) ?>" class="btn btn-sm btn-outline-secondary" aria-label="Mês anterior" title="Mês anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
    <strong class="calendario-titulo"><?= e($nomeMes) ?></strong>
    <a href="<?= url('financeiro/?mes=' . date('n', $proximoMes) . '&ano=' . date('Y', $proximoMes)) ?>" class="btn btn-sm btn-outline-secondary" aria-label="Próximo mês" title="Próximo mês"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
</div>

<section class="row g-3 mb-4" aria-label="Resumo financeiro de <?= e($nomeMes) ?>">
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
                <div>
                    <p class="text-muted small mb-1">Recebido</p>
                    <strong class="fs-5" data-total="recebido"><?= $moeda($entrou) ?></strong>
                    <p class="small text-muted mb-0">Atendimentos: <span data-total="atendimentos"><?= $moeda($totais['recebido']) ?></span></p>
                    <p class="small text-muted mb-0">Vendas: <span data-total="vendas"><?= $moeda($totais['vendas']) ?></span></p>
                </div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Previsto (agendados)</p><strong class="fs-5" data-total="previsto"><?= $moeda($totais['previsto']) ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-cart-dash" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Gastos com compras*</p><strong class="fs-5" data-total="compras"><?= $moeda($totais['compras']) ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-financeiro h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Saldo (recebido − gastos)</p><strong class="fs-5 <?= $saldo < 0 ? 'text-danger' : '' ?>" data-total="saldo"><?= $moeda($saldo) ?></strong></div>
            </div>
        </article>
    </div>
</section>

<?php if ($semValor): ?>
    <div class="alert alert-warning" role="status">
        <?= $semValor ?> <?= $semValor === 1 ? 'atendimento do mês está' : 'atendimentos do mês estão' ?> sem valor e não <?= $semValor === 1 ? 'entra' : 'entram' ?> nos totais.
        Informe o valor do procedimento ao editar o agendamento.
    </div>
<?php endif; ?>

<section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-lancamentos" data-lista-filtro data-busca="busca-financeiro" data-tipo="tipo-financeiro">
    <div class="mb-3">
        <h2 class="h4 mb-1" id="titulo-lancamentos">Lançamentos de <?= e($nomeMes) ?></h2>
        <p class="text-muted mb-0">Atendimentos da agenda (por cliente e dia), vendas de balcão e compras registradas no estoque.</p>
    </div>

    <?php if (!$lancamentos): ?>
        <div class="estado-vazio">
            <i class="bi bi-cash-coin" aria-hidden="true"></i>
            <h2>Nenhum lançamento neste mês</h2>
            <p class="mb-0">Os atendimentos com valor, as vendas e as compras de estoque aparecem aqui.</p>
        </div>
    <?php else: ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-lg-7">
                <label class="visually-hidden" for="busca-financeiro">Buscar lançamento</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input class="form-control" id="busca-financeiro" type="search" placeholder="Buscar cliente, procedimento ou produto">
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <label class="visually-hidden" for="tipo-financeiro">Filtrar por tipo</label>
                <select class="form-select" id="tipo-financeiro">
                    <option value="">Todos os lançamentos</option>
                    <?php foreach ($tipos as $chave => [$rotulo]): ?>
                        <option value="<?= $chave ?>"><?= $rotulo ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

        <div class="table-responsive tabela-celular-wrapper">
            <table class="table table-hover tabela-glow tabela-celular-responsive tabela-financeiro mb-0">
                <caption class="visually-hidden">Lançamentos financeiros do mês.</caption>
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
                        <th scope="col">Cliente / origem</th>
                        <th scope="col" class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lancamentos as $lancamento): ?>
                        <?php [$rotuloTipo, $classeTipo] = $tipos[$lancamento['tipo']]; $saida = $lancamento['tipo'] === 'compra'; ?>
                        <tr data-item-lista data-tipo-item="<?= $lancamento['tipo'] ?>">
                            <td data-label="Data"><?= e(date('d/m/Y', strtotime($lancamento['data']))) ?></td>
                            <td data-label="Lançamento"><?= e($lancamento['descricao']) ?></td>
                            <td data-label="Tipo"><span class="badge <?= $classeTipo ?>"><?= $rotuloTipo ?></span></td>
                            <td data-label="Cliente / origem"><a href="<?= e($lancamento['link']) ?>"><?= e($lancamento['vinculo']) ?></a></td>
                            <td data-label="Valor" class="text-md-end text-nowrap fw-semibold <?= $saida ? 'text-danger' : ($lancamento['tipo'] === 'previsto' ? 'text-muted' : 'text-success') ?>">
                                <?= ($saida ? '− ' : '+ ') . $moeda($lancamento['valor']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultados class="d-none">
                        <td colspan="5" class="text-center text-muted py-4">Nenhum lançamento corresponde aos filtros.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($porCliente): ?>
    <section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-por-cliente">
        <h2 class="h4 mb-1" id="titulo-por-cliente">Por cliente</h2>
        <p class="text-muted mb-3">Quanto cada cliente gerou em <?= e($nomeMes) ?>.</p>
        <div class="table-responsive tabela-celular-wrapper">
            <table class="table tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Totais por cliente no mês.</caption>
                <thead>
                    <tr>
                        <th scope="col">Cliente</th>
                        <th scope="col" class="text-end">Atendimentos</th>
                        <th scope="col" class="text-end">Recebido</th>
                        <th scope="col" class="text-end">Previsto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porCliente as $cdCliente => $cliente): ?>
                        <tr>
                            <td data-label="Cliente"><a href="<?= url('clientes/editar.php?id=' . $cdCliente) ?>"><?= e($cliente['nome']) ?></a></td>
                            <td data-label="Atendimentos" class="text-md-end"><?= $cliente['atendimentos'] ?></td>
                            <td data-label="Recebido" class="text-md-end text-nowrap"><?= $moeda($cliente['recebido']) ?></td>
                            <td data-label="Previsto" class="text-md-end text-nowrap"><?= $moeda($cliente['previsto']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle" aria-hidden="true"></i>
    * Gasto estimado: quantidade de cada entrada de estoque com motivo "Compra fornecedor" × o preço de compra cadastrado no produto.
    O valor de cada atendimento vem do campo "Valor do procedimento" da agenda, e o de cada venda de balcão, do "Valor da venda" informado na saída de estoque.
    Outras despesas e taxas não estão incluídas.</p>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
