<?php
/**
 * Formulário de produto, usado por public/produtos/criar.php e editar.php.
 * Antes de incluir, defina:
 *   $produto    → [] no cadastro ou os dados de buscar_produto()
 *   $categorias → listar_categorias($pdo) (só as ativas)
 * O "name" de cada campo é o nome usado pela api/produtos.php.
 */
$editando = isset($produto['cd_produto']);
$finalidade = $produto['fg_finalidade'] ?? 'venda';
$valor = fn ($campo) => isset($produto[$campo]) ? number_format((float) $produto[$campo], 2, '.', '') : '';
?>
<form id="form-produto" novalidate
      data-api="<?= e($editando ? 'api/produtos.php?id=' . $produto['cd_produto'] : 'api/produtos.php') ?>"
      data-metodo="<?= $editando ? 'PUT' : 'POST' ?>">

    <div class="alert alert-danger d-none" role="alert" data-erro-formulario></div>

    <section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-identificacao">
        <h2 class="h4 mb-3" id="titulo-identificacao">Identificação</h2>
        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <label class="form-label" for="nm_produto">Nome do item <span class="text-danger">*</span></label>
                <input class="form-control" type="text" id="nm_produto" name="nm_produto" maxlength="120" autocomplete="off"
                       placeholder="Ex.: Brinco argola lisa · aço cirúrgico" value="<?= e($produto['nm_produto'] ?? '') ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <label class="form-label" for="cd_categoria">Categoria <span class="text-danger">*</span></label>
                <select class="form-select" id="cd_categoria" name="cd_categoria">
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['cd_categoria'] ?>"<?= ($produto['cd_categoria'] ?? null) === $categoria['cd_categoria'] ? ' selected' : '' ?>>
                            <?= e($categoria['nm_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><a href="<?= url('produtos/categorias.php') ?>">Gerenciar categorias</a></div>
            </div>
            <div class="col-12">
                <fieldset class="grupo-finalidade" aria-describedby="ajuda-finalidade">
                    <legend>Finalidade do item <span class="text-danger">*</span></legend>
                    <p id="ajuda-finalidade" class="small text-muted mb-3">Separa os insumos usados no atendimento das joias vendidas ou aplicadas.</p>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="opcao-finalidade h-100">
                                <input class="form-check-input" type="radio" name="fg_finalidade" value="procedimento"<?= $finalidade === 'procedimento' ? ' checked' : '' ?>>
                                <span><strong>Uso em procedimento</strong><small class="d-block text-muted">Insumos usados durante o atendimento.</small></span>
                            </label>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="opcao-finalidade h-100">
                                <input class="form-check-input" type="radio" name="fg_finalidade" value="venda"<?= $finalidade === 'venda' ? ' checked' : '' ?>>
                                <span><strong>Venda / aplicação</strong><small class="d-block text-muted">Joias escolhidas pela cliente e cobradas no atendimento.</small></span>
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </section>

    <section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-precos">
        <h2 class="h4 mb-3" id="titulo-precos">Preços e estoque</h2>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <label class="form-label" for="vl_compra">Preço de compra unitário</label>
                <div class="input-group has-validation">
                    <span class="input-group-text">R$</span>
                    <input class="form-control" type="number" id="vl_compra" name="vl_compra" min="0" step="0.01" placeholder="0,00" value="<?= $valor('vl_compra') ?>">
                </div>
                <div class="form-text">Custo de referência por unidade.</div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <label class="form-label" for="vl_produto">Preço de venda <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <span class="input-group-text">R$</span>
                    <input class="form-control" type="number" id="vl_produto" name="vl_produto" min="0" step="0.01" placeholder="0,00" value="<?= $valor('vl_produto') ?>">
                </div>
                <div class="form-text">Use 0 para insumos que não são vendidos.</div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <label class="form-label" for="qtd_estoque_minimo">Estoque mínimo</label>
                <input class="form-control" type="number" id="qtd_estoque_minimo" name="qtd_estoque_minimo" min="0" step="1" placeholder="0" value="<?= e((string) ($produto['qtd_estoque_minimo'] ?? '')) ?>">
                <div class="form-text">Abaixo disso, o produto aparece como estoque baixo.</div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <?php if ($editando): ?>
                    <span class="form-label d-block">Estoque atual</span>
                    <p class="fs-4 fw-semibold mb-0"><?= (int) $produto['qtd_produto'] ?></p>
                    <div class="form-text">Para mudar, registre uma entrada ou saída no <a href="<?= url('estoque/') ?>">Estoque</a>.</div>
                <?php else: ?>
                    <label class="form-label" for="qtd_inicial">Quantidade inicial</label>
                    <input class="form-control" type="number" id="qtd_inicial" name="qtd_inicial" min="0" step="1" placeholder="0">
                    <div class="form-text">Registrada como entrada "Estoque inicial".</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="card-glow p-3 p-lg-4 mb-4" aria-labelledby="titulo-rastreio">
        <h2 class="h4 mb-1" id="titulo-rastreio">Rastreamento e validade</h2>
        <p class="text-muted mb-3">Opcional. Preencha quando o item tiver lote ou validade do fabricante.</p>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="ds_lote">Número do lote</label>
                <input class="form-control" type="text" id="ds_lote" name="ds_lote" maxlength="50" placeholder="Conforme a embalagem" value="<?= e($produto['ds_lote'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="dt_validade">Validade do fabricante</label>
                <input class="form-control" type="date" id="dt_validade" name="dt_validade" value="<?= e($produto['dt_validade'] ?? '') ?>">
            </div>
        </div>
    </section>

    <p class="small text-muted">Campos com <span class="text-danger">*</span> são obrigatórios.</p>

    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
        <a href="<?= url('produtos/') ?>" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Salvar produto</button>
    </div>
</form>

<script src="<?= url('assets/js/produtos.js') ?>" defer></script>
