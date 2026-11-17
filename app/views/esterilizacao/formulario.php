<?php
/**
 * Formulário de ciclo de esterilização, usado por public/esterilizacao/criar.php e editar.php.
 * Antes de incluir, defina:
 *   $ciclo    → [] no cadastro ou os dados de buscar_esterilizacao()
 *   $produtos → listar_produtos($pdo) (só os ativos, para escolher os itens do ciclo)
 */
$editando = isset($ciclo['cd_esterilizacao']);
$indicadores = ['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'reprovado' => 'Reprovado'];
$itensDoCiclo = $ciclo['produtos'] ?? [['cd_produto' => null, 'qtd_produto' => 1]];
?>
<form id="form-ciclo" class="card-glow p-3 p-lg-4" novalidate
      data-api="<?= e($editando ? 'api/esterilizacoes.php?id=' . $ciclo['cd_esterilizacao'] : 'api/esterilizacoes.php') ?>"
      data-metodo="<?= $editando ? 'PUT' : 'POST' ?>">

    <div class="alert alert-danger d-none" role="alert" data-erro-formulario></div>

    <div class="ciclo-formulario-grupo mb-4">
        <div class="mb-3">
            <h2 class="h6 fw-semibold mb-1">Dados do processamento</h2>
            <p class="small text-muted mb-0">Data, método e até quando os itens ficam válidos.</p>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label" for="nm_metodo">Método <span class="text-danger">*</span></label>
                <input class="form-control" id="nm_metodo" name="nm_metodo" maxlength="50" list="metodos" value="<?= e($ciclo['nm_metodo'] ?? 'Autoclave') ?>">
                <datalist id="metodos"><option value="Autoclave"><option value="Estufa"><option value="Químico"></datalist>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <label class="form-label" for="dt_esterilizacao">Data do processamento <span class="text-danger">*</span></label>
                <input class="form-control" id="dt_esterilizacao" name="dt_esterilizacao" type="date" max="<?= date('Y-m-d') ?>"
                       value="<?= e($ciclo['dt_esterilizacao'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <label class="form-label" for="dt_validade_esterilizacao">Válido até <span class="text-danger">*</span></label>
                <input class="form-control" id="dt_validade_esterilizacao" name="dt_validade_esterilizacao" type="date"
                       value="<?= e($ciclo['dt_validade_esterilizacao'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                <div class="form-text">Prazo definido pela equipe para o método usado.</div>
            </div>
        </div>
    </div>

    <div class="ciclo-formulario-grupo mb-4">
        <div class="mb-3">
            <h2 class="h6 fw-semibold mb-1">Indicadores</h2>
            <p class="small text-muted mb-0">O biológico costuma levar alguns dias: deixe "Pendente" e edite o ciclo quando sair o resultado.</p>
        </div>
        <div class="row g-3">
            <?php foreach (['fg_indicador_quimico' => 'Indicador químico', 'fg_indicador_biologico' => 'Indicador biológico'] as $campo => $rotulo): ?>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="<?= $campo ?>"><?= $rotulo ?></label>
                    <select class="form-select" id="<?= $campo ?>" name="<?= $campo ?>">
                        <?php foreach ($indicadores as $chave => $nome): ?>
                            <option value="<?= $chave ?>"<?= ($ciclo[$campo] ?? 'pendente') === $chave ? ' selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="ciclo-formulario-grupo">
        <div class="mb-3">
            <h2 class="h6 fw-semibold mb-1">Produtos esterilizados <span class="text-danger">*</span></h2>
            <p class="small text-muted mb-0">Itens que passaram por este ciclo e a quantidade de cada um.</p>
        </div>

        <div id="itens-ciclo" class="d-flex flex-column gap-2">
            <?php foreach ($itensDoCiclo as $item): ?>
                <div class="row g-2 align-items-center" data-item-ciclo>
                    <div class="col-12 col-md-7">
                        <select class="form-select" data-campo="cd_produto" aria-label="Produto">
                            <option value="">Selecione o produto...</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= $produto['cd_produto'] ?>"<?= $item['cd_produto'] === $produto['cd_produto'] ? ' selected' : '' ?>><?= e($produto['nm_produto']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-8 col-md-3">
                        <input class="form-control" type="number" min="1" step="1" data-campo="qtd_produto" aria-label="Quantidade" value="<?= (int) $item['qtd_produto'] ?>">
                    </div>
                    <div class="col-4 col-md-2 d-grid">
                        <button type="button" class="btn btn-outline-secondary" data-remover-item>Remover</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Âncora para o erro "produtos" da API aparecer aqui -->
        <input type="hidden" name="produtos">
        <button type="button" class="btn btn-outline-primary mt-3" id="adicionar-item"><i class="bi bi-plus-lg" aria-hidden="true"></i> Adicionar produto</button>
    </div>

    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4 pt-3 border-top">
        <a href="<?= url('esterilizacao/') ?>" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg" aria-hidden="true"></i> Salvar ciclo</button>
    </div>
</form>

<script src="<?= url('assets/js/esterilizacao.js') ?>" defer></script>
