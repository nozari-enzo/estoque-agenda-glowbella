<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';
require __DIR__ . '/../../app/repositories/agenda/agenda.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$cliente = $id ? buscar_cliente($pdo, $id) : null;

if (!$cliente || $cliente['fg_status'] !== 1) {
    flash('Cliente não encontrada ou desativada.', 'danger');
    redirecionar('clientes/');
}

// Agendamentos da cliente: próximos (do mais cedo) e anteriores (do mais recente)
$agendamentos = listar_agenda($pdo, ['cd_cliente' => $cliente['cd_cliente']]);
$agora = date('Y-m-d H:i:s');
$proximos = array_values(array_filter($agendamentos, fn ($a) => $a['dt_agendamento'] >= $agora && $a['fg_status'] === 'agendado'));
$anteriores = array_reverse(array_values(array_filter($agendamentos, fn ($a) => !($a['dt_agendamento'] >= $agora && $a['fg_status'] === 'agendado'))));
$totalGasto = array_sum(array_map(fn ($a) => $a['fg_status'] === 'concluido' ? (float) $a['vl_procedimento'] : 0, $agendamentos));
$totalConcluidos = count(array_filter($agendamentos, fn ($a) => $a['fg_status'] === 'concluido'));
$rotulosStatus = ['agendado' => 'Agendado', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];

$titulo = 'Editar cliente';
$menu = 'clientes';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Editar cliente</h1>
        <p><?= e($cliente['nm_cliente']) ?></p>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/clientes/formulario.php'; ?>

<section class="card-glow p-3 p-lg-4 mt-4" aria-labelledby="titulo-agenda-cliente">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-agenda-cliente">Agenda da cliente</h2>
            <p class="text-muted mb-0">
                <?= $totalConcluidos ?> <?= $totalConcluidos === 1 ? 'atendimento concluído' : 'atendimentos concluídos' ?>
                · total gasto <strong>R$ <?= number_format($totalGasto, 2, ',', '.') ?></strong>
            </p>
        </div>
        <a class="btn btn-sm btn-primary" href="<?= url('agendamentos/criar.php?cd_cliente=' . $cliente['cd_cliente']) ?>">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Agendar
        </a>
    </div>

    <?php if (!$agendamentos): ?>
        <p class="text-muted mb-0">Nenhum agendamento ainda.</p>
    <?php else: ?>
        <?php foreach (['Próximos' => $proximos, 'Anteriores' => $anteriores] as $grupo => $lista): ?>
            <?php if (!$lista) continue; ?>
            <h3 class="h6 fw-semibold mt-3"><?= $grupo ?></h3>
            <div class="table-responsive tabela-celular-wrapper">
                <table class="table tabela-glow tabela-celular-responsive mb-0">
                    <caption class="visually-hidden">Agendamentos <?= mb_strtolower($grupo) ?> da cliente.</caption>
                    <thead>
                        <tr>
                            <th scope="col">Data e hora</th>
                            <th scope="col">Procedimento</th>
                            <th scope="col" class="text-end">Valor</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="acoes">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista as $agendamento): ?>
                            <tr>
                                <td data-label="Data e hora">
                                    <strong><?= e(date('d/m/Y', strtotime($agendamento['dt_agendamento']))) ?></strong>
                                    <span class="d-block small text-muted"><?= e(date('H:i', strtotime($agendamento['dt_agendamento']))) ?> às <?= e(date('H:i', strtotime($agendamento['dt_fim']))) ?></span>
                                </td>
                                <td data-label="Procedimento"><?= e($agendamento['ds_procedimento']) ?></td>
                                <td data-label="Valor" class="text-md-end text-nowrap">
                                    <?= $agendamento['vl_procedimento'] !== null ? 'R$ ' . number_format($agendamento['vl_procedimento'], 2, ',', '.') : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td data-label="Status"><span class="badge status-<?= e($agendamento['fg_status']) ?>"><?= $rotulosStatus[$agendamento['fg_status']] ?></span></td>
                                <td data-label="Ação" class="acoes">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= url('agendamentos/editar.php?id=' . $agendamento['cd_agenda']) ?>">Abrir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
