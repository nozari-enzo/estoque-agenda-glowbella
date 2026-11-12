<?php
$editando = isset($agendamento['cd_agenda']);
// Busca clientes para o select
require_once __DIR__ . '/../../repositories/clientes/clientes.php';
$clientes = listar_clientes($pdo, ['fg_status' => '1']);
?>
<form id="form-agenda" class="card-glow p-3 p-lg-4" novalidate
      data-api="<?= e($editando ? 'api/agenda.php?id=' . $agendamento['cd_agenda'] : 'api/agenda.php') ?>"
      data-metodo="<?= $editando ? 'PUT' : 'POST' ?>">

    <div class="alert alert-danger d-none" role="alert" data-erro-formulario></div>
    
    <!-- Alerta de conflitos de horário -->
    <div class="alert alert-warning d-none" role="alert" id="alerta-conflitos">
        <strong>Conflito de horário!</strong> Já existem agendamentos neste período:
        <ul id="lista-conflitos" class="mb-0 mt-1"></ul>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label" for="cd_cliente">Cliente *</label>
            <div class="input-group">
                <select class="form-select" id="cd_cliente" name="cd_cliente">
                    <option value="">Selecione...</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['cd_cliente'] ?>" <?= ($agendamento['cd_cliente'] ?? '') == $cliente['cd_cliente'] ? 'selected' : '' ?>>
                            <?= e($cliente['nm_cliente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- Botão de atalho para Nova Cliente -->
                <a href="<?= url('clientes/criar.php') ?>" class="btn btn-outline-secondary" title="Cadastrar nova cliente" target="_blank">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="ds_procedimento">Procedimento *</label>
            <input class="form-control" id="ds_procedimento" name="ds_procedimento" value="<?= e($agendamento['ds_procedimento'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="dt_agendamento">Data e Hora de Início *</label>
            <input class="form-control" id="dt_agendamento" name="dt_agendamento" type="datetime-local" value="<?= $editando ? date('Y-m-d\TH:i', strtotime($agendamento['dt_agendamento'])) : '' ?>">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label" for="qtd_duracao_min">Duração (minutos) *</label>
            <input class="form-control" id="qtd_duracao_min" name="qtd_duracao_min" type="number" min="5" step="5" value="<?= e($agendamento['qtd_duracao_min'] ?? 30) ?>">
        </div>

        <!-- Novo campo: Valor do Procedimento -->
        <div class="col-12 col-md-4">
            <label class="form-label" for="vl_procedimento">Valor do Procedimento (R$)</label>
            <input class="form-control" id="vl_procedimento" name="vl_procedimento" type="number" step="0.01" min="0" placeholder="0.00" value="<?= e($agendamento['vl_procedimento'] ?? '') ?>">
        </div>

        <?php if ($editando): ?>
        <div class="col-12 col-md-4">
            <label class="form-label" for="fg_status">Status *</label>
            <select class="form-select" id="fg_status" name="fg_status">
                <option value="agendado" <?= $agendamento['fg_status'] === 'agendado' ? 'selected' : '' ?>>Agendado</option>
                <option value="concluido" <?= $agendamento['fg_status'] === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                <option value="cancelado" <?= $agendamento['fg_status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </div>
        <?php endif; ?>
    </div>

    <p class="small text-muted mt-3 mb-0">Campos com * são obrigatórios.</p>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= url('agendamentos/') ?>" class="btn btn-outline-secondary">Voltar</a>
    </div>
</form>

<script src="<?= url('assets/js/agendamentos.js') ?>" defer></script>