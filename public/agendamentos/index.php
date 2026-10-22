<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$titulo = 'Agenda';
$menu = 'agendamentos';
require __DIR__ . '/../../app/views/partials/header.php';

// Busca os agendamentos reais do banco de dados unindo clientes e serviços
$sql = "SELECT a.id, a.inicio, a.fim, a.status, a.observacoes,
               c.nome as cliente_nome, c.telefone as cliente_telefone,
               s.nome as servico_nome
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN servicos s ON a.servico_id = s.id
        ORDER BY a.inicio ASC";

$agendamentosDB = $pdo->query($sql)->fetchAll();

$agendamentosHoje = 0;
$hoje = date('Y-m-d');
$listaAgendamentos = [];

// Formata os dados para exibir na tela
foreach ($agendamentosDB as $row) {
    $inicio = new DateTime($row['inicio']);
    $fim = new DateTime($row['fim']);
    
    if ($inicio->format('Y-m-d') === $hoje && $row['status'] !== 'cancelado') {
        $agendamentosHoje++;
    }

    // Define as classes visuais da GlowBella
    $classeStatus = match($row['status']) {
        'agendado' => 'status-agendado',
        'concluido' => 'status-concluido',
        'cancelado' => 'status-cancelado',
        default => 'bg-secondary'
    };

    $listaAgendamentos[] = [
        'id' => $row['id'],
        'data' => $inicio->format('d/m/Y'),
        'horario' => $inicio->format('H:i') . ' - ' . $fim->format('H:i'),
        'cliente' => $row['cliente_nome'],
        'telefone' => $row['cliente_telefone'],
        'servico' => $row['servico_nome'],
        'status' => ucfirst($row['status']),
        'status_chave' => $row['status'],
        'classe_status' => $classeStatus,
        'observacao' => $row['observacoes']
    ];
}
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Agenda</h1>
        <p>Gerencie os horários marcados e acompanhe os atendimentos do dia.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('agendamentos/criar.php') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Novo agendamento
        </a>
    </div>
</div>

<section class="row g-3 mb-4" aria-label="Resumo da agenda">
    <div class="col-12 col-sm-6 col-lg-4">
        <article class="card-glow p-3 h-100 d-flex align-items-center gap-3">
            <span class="icone-circulo"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
            <div>
                <div class="text-muted small mb-1">Agendamentos hoje</div>
                <strong class="fs-4"><?= $agendamentosHoje ?></strong>
            </div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <article class="card-glow p-3 h-100 d-flex align-items-center gap-3">
            <span class="icone-circulo"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
            <div>
                <div class="text-muted small mb-1">Total de serviços</div>
                <strong class="fs-4"><?= count($listaAgendamentos) ?></strong>
            </div>
        </article>
    </div>
</section>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-lista-agenda" data-lista-filtro data-busca="busca-agenda" data-status="status-agenda">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-lista-agenda">Lista de horários</h2>
            <p class="text-muted mb-0">Pesquise por cliente, serviço ou filtre pelo status.</p>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-12 col-lg-8">
            <label class="visually-hidden" for="busca-agenda">Buscar cliente ou serviço</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-agenda" type="search" placeholder="Buscar por nome do cliente ou serviço">
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <label class="visually-hidden" for="status-agenda">Filtrar status</label>
            <select class="form-select" id="status-agenda">
                <option value="">Todos os status</option>
                <option value="agendado">Agendado</option>
                <option value="concluido">Concluído</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>
    </div>

    <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

    <div class="table-responsive tabela-celular-wrapper">
        <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
            <caption class="visually-hidden">Lista de agendamentos.</caption>
            <thead>
                <tr>
                    <th scope="col">Data e Hora</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Serviço</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="acoes">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaAgendamentos as $agenda): ?>
                    <tr data-item-lista data-status-item="<?= e($agenda['status_chave']) ?>">
                        <td data-label="Data e Hora">
                            <strong><?= e($agenda['data']) ?></strong>
                            <span class="d-block small text-muted"><?= e($agenda['horario']) ?></span>
                        </td>
                        <td data-label="Cliente">
                            <strong><?= e($agenda['cliente']) ?></strong>
                            <span class="d-block small text-muted"><?= e($agenda['telefone']) ?></span>
                        </td>
                        <td data-label="Serviço">
                            <?= e($agenda['servico']) ?>
                            <?php if ($agenda['observacao']): ?>
                                <span class="d-block small text-muted"><i class="bi bi-chat-left-text"></i> <?= e($agenda['observacao']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Status">
                            <span class="badge <?= e($agenda['classe_status']) ?>"><?= e($agenda['status']) ?></span>
                        </td>
                        <td data-label="Ação" class="acoes">
                            <a href="<?= url('agendamentos/editar.php?id=' . $agenda['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($listaAgendamentos)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Nenhum agendamento cadastrado.</td>
                    </tr>
                <?php endif; ?>
                <tr data-sem-resultados class="d-none">
                    <td colspan="5" class="text-center text-muted py-4">Nenhum agendamento encontrado com estes filtros.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>