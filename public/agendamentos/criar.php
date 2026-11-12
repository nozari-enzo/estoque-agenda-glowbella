<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require_once __DIR__ . '/../../app/repositories/agenda/agenda.php';

// Processa o formulário de cadastro via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar(); // Verifica o token de segurança

    // Captura os dados
    $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $servico_id = filter_input(INPUT_POST, 'servico_id', FILTER_VALIDATE_INT);
    $data = $_POST['data'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $observacoes = trim($_POST['observacoes'] ?? '');

    if ($cliente_id && $servico_id && $data && $horario) {
        try {
            // Busca a duração do serviço selecionado na tabela 'servico' (singular)
            $stmt = $pdo->prepare('SELECT duracao_minutos FROM servico WHERE id = ?');
            $stmt->execute([$servico_id]);
            $duracao = (int) $stmt->fetchColumn();

            // Monta o timestamp de início e adiciona a duração do serviço para o fim
            $inicio = $data . ' ' . $horario . ':00';
            $fim = date('Y-m-d H:i:s', strtotime("$inicio + $duracao minutes"));

            // Prepara os dados com os nomes de colunas que a função do repositório espera
            $dadosAgendamento = [
                'cd_cliente' => $cliente_id,
                'cd_servico' => $servico_id,
                'dt_agendamento' => $inicio,
                'dt_fim' => $fim,
                'fg_status' => 'agendado',
                'observacoes' => $observacoes ?: null
            ];

            // Usa a função do repositório para inserir no banco de dados
            inserir_agendamento($pdo, $dadosAgendamento);

            flash('Agendamento criado com sucesso!');
            redirecionar('agendamentos/');
        } catch (PDOException $erro) {
            flash('Erro ao salvar agendamento: ' . $erro->getMessage(), 'danger');
        }
    } else {
        flash('Por favor, preencha todos os campos obrigatórios.', 'danger');
    }
}

// Busca as listas para preencher os <select> usando os nomes de tabela corretos
$clientes = $pdo->query('SELECT cd_cliente AS id, nm_cliente AS nome FROM cliente ORDER BY nm_cliente')->fetchAll();
$servicos = $pdo->query('SELECT id, nome, duracao_minutos FROM servico WHERE ativo = 1 ORDER BY nome')->fetchAll();

$titulo = 'Novo Agendamento';
$menu = 'agendamentos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Novo Agendamento</h1>
        <p>Preencha os detalhes para marcar um novo horário.</p>
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
                        <option value="<?= $cliente['id'] ?>"><?= e($cliente['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="servico_id" class="form-label">Serviço *</label>
                <select class="form-select" id="servico_id" name="servico_id" required>
                    <option value="">Selecione o serviço...</option>
                    <?php foreach ($servicos as $servico): ?>
                        <option value="<?= $servico['id'] ?>">
                            <?= e($servico['nome']) ?> (<?= $servico['duracao_minutos'] ?> min)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="data" class="form-label">Data *</label>
                <input type="date" class="form-control" id="data" name="data" required>
            </div>

            <div class="col-md-6">
                <label for="horario" class="form-label">Horário de Início *</label>
                <input type="time" class="form-control" id="horario" name="horario" required>
                <div class="form-text">O horário de término será calculado automaticamente.</div>
            </div>

            <div class="col-12">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3" placeholder="Detalhes adicionais (opcional)..."></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= url('agendamentos/') ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg" aria-hidden="true"></i> Salvar agendamento
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>