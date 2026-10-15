<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$titulo = 'Novo produto';
$menu = 'produtos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Novo produto</h1>
        <p>Defina a finalidade do item e as informações de rastreamento.</p>
    </div>
    <a href="<?= url('produtos/') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao catálogo</a>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-info-circle-fill mt-1" aria-hidden="true"></i>
    <div><strong>Protótipo visual.</strong> Os campos ajudam a definir o fluxo. Salvar ainda não cria um produto.</div>
</div>

<section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-identificacao">
    <h2 class="h4 mb-3" id="titulo-identificacao">Identificação</h2>
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <label class="form-label" for="produto-nome">Nome do item <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="produto-nome" placeholder="Ex.: Luva estéril tamanho M">
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <label class="form-label" for="produto-codigo">Código interno</label>
            <input class="form-control" type="text" id="produto-codigo" placeholder="Ex.: LUVA-M-001">
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <label class="form-label" for="produto-categoria">Categoria <span class="text-danger">*</span></label>
            <select class="form-select" id="produto-categoria">
                <option value="">Selecione...</option>
                <option>Insumos</option>
                <option>Brincos</option>
                <option>Piercings</option>
                <option>Acessórios</option>
                <option>Kits de procedimento</option>
            </select>
        </div>
        <div class="col-12 col-lg-8">
            <fieldset>
                <legend class="form-label">Finalidade do item <span class="text-danger">*</span></legend>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <label class="opcao-finalidade flex-fill">
                        <input class="form-check-input" type="radio" name="finalidade-demo" value="consumo">
                        <span><strong>Uso e consumo / procedimento</strong><small class="d-block text-muted">Insumos usados durante o atendimento.</small></span>
                    </label>
                    <label class="opcao-finalidade flex-fill">
                        <input class="form-check-input" type="radio" name="finalidade-demo" value="venda">
                        <span><strong>Venda / aplicação</strong><small class="d-block text-muted">Joias e itens escolhidos pela cliente.</small></span>
                    </label>
                </div>
            </fieldset>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <label class="form-label" for="produto-preco">Preço de venda (quando aplicável)</label>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input class="form-control" type="number" id="produto-preco" min="0" step="0.01" placeholder="0,00">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <label class="form-label" for="produto-minimo">Estoque mínimo</label>
            <input class="form-control" type="number" id="produto-minimo" min="0" placeholder="0">
            <div class="form-text">O sistema poderá alertar quando a quantidade atingir este ponto.</div>
        </div>
    </div>
</section>

<section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-rastreio">
    <h2 class="h4 mb-1" id="titulo-rastreio">Rastreamento e validade</h2>
    <p class="text-muted mb-3">As opções abaixo representam campos de interface; as regras obrigatórias serão definidas junto ao modelo do banco.</p>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="form-check form-switch opcao-rastreio">
                <input class="form-check-input" type="checkbox" role="switch" id="controla-lote" data-mostra-bloco="#dados-lote">
                <label class="form-check-label" for="controla-lote"><strong>Controlar número de lote</strong><span class="d-block small text-muted">Ative para registrar o lote recebido.</span></label>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="form-check form-switch opcao-rastreio">
                <input class="form-check-input" type="checkbox" role="switch" id="controla-validade" data-mostra-bloco="#campo-validade" disabled>
                <label class="form-check-label" for="controla-validade"><strong>Controlar validade do fabricante</strong><span class="d-block small text-muted">Disponível quando o controle por lote estiver ativo.</span></label>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="form-check form-switch opcao-rastreio">
                <input class="form-check-input" type="checkbox" role="switch" id="reesterilizavel">
                <label class="form-check-label" for="reesterilizavel"><strong>Pode ser reesterilizado internamente</strong><span class="d-block small text-muted">A regra de identificação individual ou por pacote será validada com o banco.</span></label>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1 d-none" id="dados-lote">
        <div class="col-12"><hr class="my-2"><h3 class="h5 mb-0">Dados do primeiro lote (demonstração)</h3></div>
        <div class="col-12 col-md-6">
            <label class="form-label" for="numero-lote">Número do lote</label>
            <input class="form-control" type="text" id="numero-lote" placeholder="Informe conforme a embalagem">
        </div>
        <div class="col-12 col-md-6 d-none" id="campo-validade">
            <label class="form-label" for="validade-fabricante">Validade do fabricante</label>
            <input class="form-control" type="date" id="validade-fabricante">
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label" for="quantidade-inicial">Quantidade inicial</label>
            <input class="form-control" type="number" id="quantidade-inicial" min="0" placeholder="0">
        </div>
    </div>
</section>

<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
    <a href="<?= url('produtos/') ?>" class="btn btn-outline-secondary">Cancelar</a>
    <button type="button" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Salvar produto</button>
</div>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
