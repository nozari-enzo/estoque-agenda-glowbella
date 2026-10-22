<?php
require __DIR__ . '/../../app/bootstrap/init.php';

// Valida se o ID foi passado na URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    flash('Agendamento não encontrado.', 'danger');
    redirecionar('agendamentos/');
}

// Busca o agendamento atual no banco
$stmt = $pdo->prepare('SELECT * FROM agendamentos WHERE id = ?');
$stmt->execute([$id]);
$agendamento = $stmt->fetch();

if (!$agendamento) {
    flash('Agendamento não encontrado.', 'danger');
    redirecionar('agendamentos/');
}

// Processa a atualização via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $servico_id = filter_input(INPUT_POST, 'servico_id', FILTER_VALIDATE_INT);
    $data = $_POST['data'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $status = $_POST['status'] ?? 'agendado';
    $observacoes = trim($_POST['observacoes'] ?? '');

    if ($cliente_id && $servico_id && $data && $horario) {
        try {
            // Recalcula o horário de fim baseado no serviço
            $stmt = $pdo->prepare('SELECT duracao_minutos FROM servicos WHERE id = ?');
            $stmt->execute([$servico_id]);
            $duracao = (int) $stmt->fetchColumn();

            $inicio = $data . ' ' . $horario . ':00';
            $fim = date('Y-m-d H:i:s', strtotime("$inicio + $duracao minutes"));

            // Executa o UPDATE no banco
            $sql = "UPDATE agendamentos 
                    SET cliente_id = ?, servico_id = ?, inicio = ?, fim = ?, status = ?, observacoes = ? 
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $cliente_id, 
                $servico_id, 
                $inicio, 
                $fim, 
                $status, 
                $observacoes ?: null, 
                $id
            ]);

            flash('Agendamento atualizado com sucesso!');
            redirecionar('agendamentos/');
        } catch (PDOException $erro) {
            flash('Erro ao atualizar agendamento: ' . $erro->getMessage(), 'danger');
        }
    } else {
        flash('Por favor, preencha todos os campos obrigatórios.', 'danger');
    }
}

// Busca as listas para preencher os <select>
$clientes = $pdo->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();
$servicos = $pdo->query('SELECT id, nome, duracao_minutos FROM servicos WHERE ativo = 1 ORDER BY nome')->fetchAll();

// Extrai a data e hora do banco para preencher os inputs corretos do HTML
$dataAtual = date('Y-m-d', strtotime($agendamento['inicio']));
$horarioAtual = date('H:i', strtotime($agendamento['inicio']));

$titulo = 'Editar Agendamento';
$menu = 'agendamentos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Editar Agendamento</h1>
        <p>Atualize os detalhes, horários ou o status do atendimento.</p>
    </div>
    <a href="<?= url('agendamentos/') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar à agenda
    </a>
</div>

<div class="card-glow p-4">
    <form method="post">
        <?= csrf_campo() ?>
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="cliente_id" class="form-label">Cliente *</label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione a cliente...</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id'] ?>" <?= $cliente['id'] == $agendamento['cliente_id'] ? 'selected' : '' ?>>
                            <?= e($cliente['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="servico_id" class="form-label">Serviço *</label>
                <select class="form-select" id="servico_id" name="servico_id" required>
                    <option value="">Selecione o serviço...</option>
                    <?php foreach ($servicos as $servico): ?>
                        <option value="<?= $servico['id'] ?>" <?= $servico['id'] == $agendamento['servico_id'] ? 'selected' : '' ?>>
                            <?= e($servico['nome']) ?> (<?= $servico['duracao_minutos'] ?> min)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="data" class="form-label">Data *</label>
                <input type="date" class="form-control" id="data" name="data" value="<?= e($dataAtual) ?>" required>
            </div>

            <div class="col-md-4">
                <label for="horario" class="form-label">Horário de Início *</label>
                <input type="time" class="form-control" id="horario" name="horario" value="<?= e($horarioAtual) ?>" required>
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status *</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="agendado" <?= $agendamento['status'] === 'agendado' ? 'selected' : '' ?>>Agendado</option>
                    <option value="concluido" <?= $agendamento['status'] === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                    <option value="cancelado" <?= $agendamento['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>

            <div class="col-12">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Detalhes adicionais (opcional)..."><?= e($agendamento['observacoes']) ?></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= url('agendamentos/') ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg" aria-hidden="true"></i> Salvar alterações
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>