<?php
/**
 * Formulário de cliente, usado por public/clientes/criar.php e editar.php.
 * Antes de incluir, defina $cliente: [] no cadastro ou os dados vindos de buscar_cliente().
 * O "name" de cada campo é o nome usado pela API (nm_cliente, nu_telefone, dt_nascimento).
 */
$editando = isset($cliente['cd_cliente']);
?>
<form id="form-cliente" class="card-glow p-3 p-lg-4" novalidate
      data-api="<?= e($editando ? 'api/clientes.php?id=' . $cliente['cd_cliente'] : 'api/clientes.php') ?>"
      data-metodo="<?= $editando ? 'PUT' : 'POST' ?>">

    <!-- Mensagem geral de erro: fica escondida até a API responder com erro -->
    <div class="alert alert-danger d-none" role="alert" data-erro-formulario></div>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="nm_cliente">Nome *</label>
            <input class="form-control" id="nm_cliente" name="nm_cliente" maxlength="120" autocomplete="off"
                   value="<?= e($cliente['nm_cliente'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="nu_telefone">Telefone (WhatsApp) *</label>
            <input class="form-control" id="nu_telefone" name="nu_telefone" type="tel" inputmode="tel" placeholder="(11) 90000-0000"
                   value="<?= e($cliente['nu_telefone'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label" for="dt_nascimento">Data de nascimento</label>
            <input class="form-control" id="dt_nascimento" name="dt_nascimento" type="date" max="<?= date('Y-m-d') ?>"
                   value="<?= e($cliente['dt_nascimento'] ?? '') ?>">
        </div>
    </div>

    <p class="small text-muted mt-3 mb-0">Campos com * são obrigatórios.</p>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= url('clientes/') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<script src="<?= url('assets/js/clientes.js') ?>" defer></script>
