<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/agenda/agenda.php';

// Busca os agendamentos chamando o repositório
$agendamentos = listar_agenda($pdo);

$titulo = 'Agenda';
$menu = 'agendamentos';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Agenda</h1>
        <p>Gerencie os horários marcados e acompanhe os atendimentos.</p>
    </div>
    <a href="<?= url('agendamentos/criar.php') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Novo agendamento
    </a>
</div>

<?php if (isset($_GET['salvo'])): ?>
    <div class="alert alert-success" role="alert">Agendamento salvo com sucesso!</div>
<?php endif; ?>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-lista-agenda" data-lista-filtro data-busca="busca-agenda">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-lista-agenda">Lista de horários</h2>
            <p class="text-muted mb-0">Pesquise por cliente ou procedimento.</p>
        </div>
    </div>

    <?php if (!$agendamentos): ?>
        <div class="estado-vazio">
            <i class="bi bi-calendar-x" aria-hidden="true"></i>
            <h2>Nenhum agendamento encontrado</h2>
            <p class="mb-0">Clique em <strong>Novo agendamento</strong> para marcar um horário.</p>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <label class="visually-hidden" for="busca-agenda">Buscar agendamento</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-agenda" type="search" placeholder="Buscar por cliente ou procedimento">
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
                        <th scope="col">Procedimento</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $agenda): ?>
                        <tr data-item-lista>
                            <td data-label="Data e Hora">
                                <strong><?= e(date('d/m/Y', strtotime($agenda['dt_agendamento']))) ?></strong>
                                <span class="d-block small text-muted">
                                    <?= e(date('H:i', strtotime($agenda['dt_agendamento']))) ?> às <?= e(date('H:i', strtotime($agenda['dt_fim']))) ?>
                                </span>
                            </td>
                            <td data-label="Cliente"><strong><?= e($agenda['nm_cliente']) ?></strong></td>
                            <td data-label="Procedimento"><?= e($agenda['ds_procedimento']) ?></td>
                            <td data-label="Status">
                                <span class="badge text-bg-<?= $agenda['fg_status'] === 'agendado' ? 'primary' : ($agenda['fg_status'] === 'concluido' ? 'success' : 'secondary') ?>">
                                    <?= ucfirst(e($agenda['fg_status'])) ?>
                                </span>
                            </td>
                            <td data-label="Ações" class="acoes">
                                <div class="d-inline-flex flex-wrap gap-2">
                                    <a href="<?= url('agendamentos/editar.php?id=' . $agenda['cd_agenda']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <?php if ($agenda['fg_status'] !== 'cancelado'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-cancelar="<?= $agenda['cd_agenda'] ?>">Cancelar</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultados class="d-none">
                        <td colspan="5" class="text-center text-muted py-4">Nenhum agendamento encontrado com essa busca.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script src="<?= url('assets/js/agendamentos.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>