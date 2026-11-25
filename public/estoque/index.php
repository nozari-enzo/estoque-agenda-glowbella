<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/produtos/produtos.php';
require __DIR__ . '/../../app/repositories/movimentacoes/movimentacoes.php';
require __DIR__ . '/../../app/services/estoque.php';

// Validade "próxima": vence nos próximos 60 dias
const DIAS_VALIDADE_PROXIMA = 60;

$produtos = listar_produtos($pdo);
$hoje = date('Y-m-d');
$limiteValidade = date('Y-m-d', strtotime('+' . DIAS_VALIDADE_PROXIMA . ' days'));

/** Situação de um produto no estoque, da mais urgente para a menos urgente. */
function situacao_estoque(array $produto, string $hoje, string $limiteValidade): array
{
    if ($produto['qtd_produto'] === 0) {
        return ['sem', 'Sem estoque', 'text-bg-danger'];
    }
    if ($produto['dt_validade'] && $produto['dt_validade'] < $hoje) {
        return ['vencido', 'Vencido', 'text-bg-danger'];
    }
    if ($produto['estoque_baixo']) {
        return ['baixo', 'Estoque baixo', 'text-bg-warning'];
    }
    if ($produto['dt_validade'] && $produto['dt_validade'] <= $limiteValidade) {
        return ['validade', 'Validade próxima', 'text-bg-warning'];
    }
    return ['regular', 'Regular', 'text-bg-success'];
}

$contagem = ['sem' => 0, 'vencido' => 0, 'baixo' => 0, 'validade' => 0, 'regular' => 0];
foreach ($produtos as &$produto) {
    $produto['situacao'] = situacao_estoque($produto, $hoje, $limiteValidade);
    $contagem[$produto['situacao'][0]]++;
}
unset($produto);

// Histórico: de um produto (?produto=3) ou as últimas movimentações
$cdProdutoHistorico = filter_var($_GET['produto'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
$produtoHistorico = $cdProdutoHistorico ? buscar_produto($pdo, $cdProdutoHistorico) : null;
$movimentacoes = listar_movimentacoes($pdo, ['cd_produto' => $produtoHistorico ? $cdProdutoHistorico : null, 'limite' => 20]);

$finalidades = ['procedimento' => 'Uso em procedimento', 'venda' => 'Venda / aplicação'];

$titulo = 'Estoque';
$menu = 'estoque';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Estoque</h1>
        <p>Consulte o que tem, o que está faltando e registre entradas e saídas.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="<?= url('produtos/') ?>">
            <i class="bi bi-gem" aria-hidden="true"></i> Produtos
        </a>
        <a class="btn btn-primary" href="#movimentar">
            <i class="bi bi-arrow-left-right" aria-hidden="true"></i> Registrar movimentação
        </a>
    </div>
</div>

<?php if (isset($_GET['salvo'])): ?>
    <div class="alert alert-success" role="alert">Movimentação registrada e estoque atualizado.</div>
<?php endif; ?>

<?php if (isset($_GET['produto_novo'])): ?>
    <div class="alert alert-success" role="alert">Produto cadastrado. A quantidade inicial já entrou no estoque.</div>
<?php endif; ?>

<section class="row g-3 mb-4" aria-label="Resumo do estoque">
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Produtos ativos</p><strong class="fs-4"><?= count($produtos) ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-x-octagon" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Sem estoque</p><strong class="fs-4"><?= $contagem['sem'] ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Abaixo do mínimo</p><strong class="fs-4"><?= $contagem['baixo'] ?></strong></div>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <article class="card-glow resumo-estoque h-100 p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="icone-circulo"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span>
                <div><p class="text-muted small mb-1">Vencidos ou vencendo (<?= DIAS_VALIDADE_PROXIMA ?> dias)</p><strong class="fs-4"><?= $contagem['vencido'] + $contagem['validade'] ?></strong></div>
            </div>
        </article>
    </div>
</section>

<section class="card-glow p-3 p-lg-4 mb-4" id="movimentar" aria-labelledby="titulo-movimentar">
    <h2 class="h4 mb-1" id="titulo-movimentar">Registrar movimentação</h2>
    <p class="text-muted mb-3">Entrada soma no estoque (compra, devolução); saída desconta (venda, uso, perda).</p>

    <?php if (!$produtos): ?>
        <p class="mb-0">Cadastre um produto em <a href="<?= url('produtos/criar.php') ?>">Produtos</a> para movimentar o estoque.</p>
    <?php else: ?>
        <form id="form-movimentacao" novalidate>
            <div class="alert alert-danger d-none" role="alert" data-erro-formulario></div>
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <label class="form-label" for="busca-produto">Produto <span class="text-danger">*</span></label>
                    <div class="busca-produto">
                        <!-- name="cd_produto": os erros da API para esse campo aparecem aqui -->
                        <input class="form-control" type="text" id="busca-produto" name="cd_produto" autocomplete="off"
                               placeholder="Digite para buscar..." role="combobox" aria-autocomplete="list"
                               aria-expanded="false" aria-controls="sugestoes-produto">
                        <div class="list-group busca-produto-lista d-none" id="sugestoes-produto" role="listbox" aria-label="Produtos encontrados"></div>
                    </div>
                    <div class="form-text" id="produto-escolhido" aria-live="polite"></div>
                    <div class="alert alert-warning py-2 px-3 mt-2 mb-0 d-none" id="produto-nao-cadastrado" role="status">
                        Produto não cadastrado.
                        <a class="alert-link" id="link-novo-produto" href="<?= url('produtos/criar.php?origem=estoque') ?>">Cadastrar novo produto</a>
                    </div>
                    <script type="application/json" id="dados-produtos"><?= json_encode(array_map(fn ($produto) => [
                        'id'   => $produto['cd_produto'],
                        'nome' => $produto['nm_produto'],
                        'qtd'  => $produto['qtd_produto'],
                        'preco' => (float) $produto['vl_produto'],
                    ], $produtos), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <span class="form-label d-block">Tipo <span class="text-danger">*</span></span>
                    <div class="btn-group w-100" role="group" aria-label="Tipo de movimentação">
                        <input type="radio" class="btn-check" name="fg_tipo" id="tipo-entrada" value="entrada" checked>
                        <label class="btn btn-outline-primary" for="tipo-entrada"><i class="bi bi-plus-lg" aria-hidden="true"></i> Entrada</label>
                        <input type="radio" class="btn-check" name="fg_tipo" id="tipo-saida" value="saida">
                        <label class="btn btn-outline-primary" for="tipo-saida"><i class="bi bi-dash-lg" aria-hidden="true"></i> Saída</label>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label" for="qtd_movimentada">Quantidade <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" id="qtd_movimentada" name="qtd_movimentada" min="1" step="1" placeholder="0">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="ds_motivo">Motivo <span class="text-danger">*</span></label>
                    <!-- As opções mudam com o tipo: estoque.js remonta a lista com data-motivos ao trocar entrada/saída -->
                    <select class="form-select" id="ds_motivo" name="ds_motivo"
                            data-motivos="<?= e(json_encode(MOTIVOS_MOVIMENTACAO, JSON_UNESCAPED_UNICODE)) ?>">
                        <option value="">Selecione...</option>
                        <?php foreach (MOTIVOS_MOVIMENTACAO['entrada'] as $motivo): ?>
                            <option value="<?= e($motivo) ?>"><?= e($motivo) ?></option>
                        <?php endforeach; ?>
                        <option value="outro">Outro...</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 d-none" id="campo-motivo-outro">
                    <label class="form-label" for="ds_motivo_outro">Qual motivo? <span class="text-danger">*</span></label>
                    <input class="form-control" id="ds_motivo_outro" name="ds_motivo_outro" maxlength="150" placeholder="Ex.: Brinde para cliente">
                </div>
                <div class="col-12 col-md-6 d-none" id="campo-valor-venda">
                    <label class="form-label" for="vl_total">Valor da venda <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text">R$</span>
                        <input class="form-control" type="number" id="vl_total" name="vl_total" min="0" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-text">Quantidade × preço de venda do produto. Mude se deu desconto. Entra no Financeiro.</div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Registrar</button>
            </div>
        </form>
    <?php endif; ?>
</section>

<section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-itens-estoque" data-lista-filtro data-busca="busca-estoque" data-tipo="tipo-estoque" data-status="status-estoque">
    <div class="mb-3">
        <h2 class="h4 mb-1" id="titulo-itens-estoque">Itens em estoque</h2>
        <p class="text-muted mb-0">Busque pelo nome, categoria ou lote. Use a situação para ver o que está faltando.</p>
    </div>

    <?php if (!$produtos): ?>
        <div class="estado-vazio">
            <i class="bi bi-box-seam" aria-hidden="true"></i>
            <h2>Nenhum produto cadastrado</h2>
        </div>
    <?php else: ?>
        <div class="row g-2 mb-3">
            <div class="col-12 col-lg-6">
                <label class="visually-hidden" for="busca-estoque">Buscar item ou lote</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input class="form-control" id="busca-estoque" type="search" placeholder="Buscar item ou lote">
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <label class="visually-hidden" for="tipo-estoque">Filtrar por finalidade</label>
                <select class="form-select" id="tipo-estoque">
                    <option value="">Todas as finalidades</option>
                    <?php foreach ($finalidades as $chave => $rotulo): ?>
                        <option value="<?= $chave ?>"><?= e($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-3">
                <label class="visually-hidden" for="status-estoque">Filtrar por situação</label>
                <select class="form-select" id="status-estoque">
                    <option value="">Todas as situações</option>
                    <option value="sem">Sem estoque (<?= $contagem['sem'] ?>)</option>
                    <option value="baixo">Estoque baixo (<?= $contagem['baixo'] ?>)</option>
                    <option value="vencido">Vencido (<?= $contagem['vencido'] ?>)</option>
                    <option value="validade">Validade próxima (<?= $contagem['validade'] ?>)</option>
                    <option value="regular">Regular (<?= $contagem['regular'] ?>)</option>
                </select>
            </div>
        </div>

        <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

        <div class="table-responsive tabela-celular-wrapper">
            <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Produtos, lotes, quantidades e situações do estoque.</caption>
                <thead>
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col">Lote</th>
                        <th scope="col">Validade</th>
                        <th scope="col" class="text-end">Quantidade</th>
                        <th scope="col">Situação</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <?php [$chaveSituacao, $rotuloSituacao, $classeSituacao] = $produto['situacao']; ?>
                        <tr data-item-lista data-tipo-item="<?= e($produto['fg_finalidade']) ?>" data-status-item="<?= $chaveSituacao ?>">
                            <td data-label="Item">
                                <strong><?= e($produto['nm_produto']) ?></strong>
                                <span class="d-block small text-muted"><?= e($produto['nm_categoria']) ?> · <?= e($finalidades[$produto['fg_finalidade']]) ?></span>
                            </td>
                            <td data-label="Lote"><?= $produto['ds_lote'] ? e($produto['ds_lote']) : '<span class="text-muted">—</span>' ?></td>
                            <td data-label="Validade"><?= $produto['dt_validade'] ? e(date('d/m/Y', strtotime($produto['dt_validade']))) : '<span class="text-muted">—</span>' ?></td>
                            <td data-label="Quantidade" class="text-md-end">
                                <strong><?= $produto['qtd_produto'] ?></strong>
                                <span class="d-block small text-muted">mínimo <?= $produto['qtd_estoque_minimo'] ?></span>
                            </td>
                            <td data-label="Situação"><span class="badge <?= $classeSituacao ?>"><?= $rotuloSituacao ?></span></td>
                            <td data-label="Ações" class="acoes">
                                <div class="d-inline-flex flex-wrap flex-xl-nowrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-movimentar="<?= $produto['cd_produto'] ?>" data-tipo="entrada">Entrada</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-movimentar="<?= $produto['cd_produto'] ?>" data-tipo="saida"<?= $produto['qtd_produto'] === 0 ? ' disabled' : '' ?>>Saída</button>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('estoque/?produto=' . $produto['cd_produto'] . '#historico') ?>">Histórico</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultados class="d-none">
                        <td colspan="6" class="text-center text-muted py-4">Nenhum item corresponde aos filtros.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="card-glow p-3 p-lg-4" id="historico" aria-labelledby="titulo-historico">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-historico">
                <?= $produtoHistorico ? 'Histórico de ' . e($produtoHistorico['nm_produto']) : 'Últimas movimentações' ?>
            </h2>
            <p class="text-muted mb-0">Entradas e saídas registradas, com quem registrou.</p>
        </div>
        <?php if ($produtoHistorico): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= url('estoque/#historico') ?>">Ver todas</a>
        <?php endif; ?>
    </div>

    <?php if (!$movimentacoes): ?>
        <p class="text-muted mb-0">Nenhuma movimentação registrada.</p>
    <?php else: ?>
        <div class="table-responsive tabela-celular-wrapper">
            <table class="table tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Histórico de movimentações de estoque.</caption>
                <thead>
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Produto</th>
                        <th scope="col">Tipo</th>
                        <th scope="col" class="text-end">Quantidade</th>
                        <th scope="col">Motivo</th>
                        <th scope="col">Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimentacoes as $movimentacao): ?>
                        <?php $entrada = $movimentacao['fg_tipo'] === 'entrada'; ?>
                        <tr>
                            <td data-label="Data"><?= e(date('d/m/Y H:i', strtotime($movimentacao['dt_movimentacao']))) ?></td>
                            <td data-label="Produto"><?= e($movimentacao['nm_produto']) ?></td>
                            <td data-label="Tipo"><span class="badge <?= $entrada ? 'status-concluido' : 'status-agendado' ?>"><?= $entrada ? 'Entrada' : 'Saída' ?></span></td>
                            <td data-label="Quantidade" class="text-md-end"><strong><?= ($entrada ? '+' : '−') . $movimentacao['qtd_movimentada'] ?></strong></td>
                            <td data-label="Motivo">
                                <?= $movimentacao['ds_motivo'] ? e($movimentacao['ds_motivo']) : '<span class="text-muted">—</span>' ?>
                                <?php if ($movimentacao['vl_total'] !== null): ?>
                                    <span class="text-muted text-nowrap">· R$ <?= number_format($movimentacao['vl_total'], 2, ',', '.') ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Registrado por"><?= $movimentacao['nm_usuario'] ? e($movimentacao['nm_usuario']) : '<span class="text-muted">—</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script src="<?= url('assets/js/estoque.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
