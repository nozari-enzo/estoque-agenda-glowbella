<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/agenda/agenda.php';

// Mês e ano do calendário (?mes=11&ano=2026). Valores inválidos voltam para o mês atual.
$numeroDaUrl = fn (string $nome): int => is_string($_GET[$nome] ?? null) && ctype_digit($_GET[$nome]) ? (int) $_GET[$nome] : 0;
$mes = $numeroDaUrl('mes');
$ano = $numeroDaUrl('ano');
if ($mes < 1 || $mes > 12) {
    $mes = (int) date('n');
}
if ($ano < 2000 || $ano > 2100) {
    $ano = (int) date('Y');
}
$mes = sprintf('%02d', $mes);

// Dia clicado no calendário (?dia=2026-11-12). Só aceita uma data válida.
$diaFiltro = is_string($_GET['dia'] ?? null) ? $_GET['dia'] : '';
$dataDia = DateTime::createFromFormat('!Y-m-d', $diaFiltro);
if (!$dataDia || $dataDia->format('Y-m-d') !== $diaFiltro) {
    $diaFiltro = '';
}

$rotulosStatus = ['agendado' => 'Agendado', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];

// Cálculos para os botões de próximo mês / mês anterior
$mesAnt = date('m', strtotime("$ano-$mes-01 -1 month"));
$anoAnt = date('Y', strtotime("$ano-$mes-01 -1 month"));
$mesProx = date('m', strtotime("$ano-$mes-01 +1 month"));
$anoProx = date('Y', strtotime("$ano-$mes-01 +1 month"));

// Propriedades do mês atual
$primeiroDia = "$ano-$mes-01";
$totalDias = date('t', strtotime($primeiroDia));
$diaSemanaInicio = date('w', strtotime($primeiroDia)); // 0 = Domingo, 6 = Sábado

// Nomes dos meses para o título
$mesesPt = ['01'=>'Janeiro', '02'=>'Fevereiro', '03'=>'Março', '04'=>'Abril', '05'=>'Maio', '06'=>'Junho', '07'=>'Julho', '08'=>'Agosto', '09'=>'Setembro', '10'=>'Outubro', '11'=>'Novembro', '12'=>'Dezembro'];

// Busca todos os agendamentos no banco
$todosAgendamentos = listar_agenda($pdo);

// Agrupa os agendamentos pela data (para desenhar no calendário)
$agendaPorDia = [];
foreach ($todosAgendamentos as $ag) {
    $dataStr = substr($ag['dt_agendamento'], 0, 10);
    $agendaPorDia[$dataStr][] = $ag;
}

// Filtra a lista da tabela se um dia for clicado
$agendamentosLista = $todosAgendamentos;
if ($diaFiltro) {
    $agendamentosLista = array_filter($todosAgendamentos, function($a) use ($diaFiltro) {
        return substr($a['dt_agendamento'], 0, 10) === $diaFiltro;
    });
}

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

<!-- 1. CALENDÁRIO VISUAL (TOPO) -->
<section class="card-glow p-3 p-lg-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Visão Mensal</h2>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url("agendamentos/?mes=$mesAnt&ano=$anoAnt") ?>" class="btn btn-sm btn-outline-secondary" aria-label="Mês anterior" title="Mês anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
            <strong class="calendario-titulo"><?= $mesesPt[$mes] ?> de <?= $ano ?></strong>
            <a href="<?= url("agendamentos/?mes=$mesProx&ano=$anoProx") ?>" class="btn btn-sm btn-outline-secondary" aria-label="Próximo mês" title="Próximo mês"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        </div>
    </div>

    <div class="table-responsive">
        <div class="calendario-grid">
            <div class="calendario-header">Dom</div>
            <div class="calendario-header">Seg</div>
            <div class="calendario-header">Ter</div>
            <div class="calendario-header">Qua</div>
            <div class="calendario-header">Qui</div>
            <div class="calendario-header">Sex</div>
            <div class="calendario-header">Sáb</div>

            <?php
            // Preenche espaços vazios antes do dia 1
            for ($i = 0; $i < $diaSemanaInicio; $i++) {
                echo '<div class="calendario-dia vazio"></div>';
            }

            $hoje = date('Y-m-d');

            // Renderiza os dias do mês
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                $dataAtual = sprintf('%s-%s-%02d', $ano, $mes, $dia);
                $classeHoje = ($dataAtual === $hoje) ? 'hoje' : '';
                $classeAtivo = ($dataAtual === $diaFiltro) ? 'ativo' : '';
                
                echo "<div class='calendario-dia $classeHoje'>";
                echo "<div class='calendario-numero'><a href='" . url("agendamentos/?mes=$mes&ano=$ano&dia=$dataAtual") . "' class='$classeAtivo' title='Filtrar por este dia'>$dia</a></div>";

                // Se houver agendamentos neste dia, cria as etiquetas
                if (isset($agendaPorDia[$dataAtual])) {
                    foreach ($agendaPorDia[$dataAtual] as $evento) {
                        $hora = date('H:i', strtotime($evento['dt_agendamento']));
                        $status = $evento['fg_status'];
                        $classeStatus = '';
                        
                        if ($status === 'concluido') $classeStatus = 'evento-concluido';
                        if ($status === 'cancelado') $classeStatus = 'evento-cancelado';

                        // Pega apenas o primeiro nome da cliente para caber no bloco
                        $primeiroNome = explode(' ', trim($evento['nm_cliente']))[0];

                        echo "<a href='" . url("agendamentos/editar.php?id=" . $evento['cd_agenda']) . "' class='evento-badge $classeStatus' title='" . e($evento['nm_cliente']) . " - " . e($evento['ds_procedimento']) . "'>";
                        echo "<strong>$hora</strong> " . e($primeiroNome);
                        echo "</a>";
                    }
                }
                echo "</div>";
            }

            // Preenche espaços vazios no final da última semana
            $diasFaltantes = (7 - (($diaSemanaInicio + $totalDias) % 7)) % 7;
            for ($i = 0; $i < $diasFaltantes; $i++) {
                echo '<div class="calendario-dia vazio"></div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- 2. LISTA DETALHADA (TABELA) -->
<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-lista-agenda" data-lista-filtro data-busca="busca-agenda">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="h4 mb-1" id="titulo-lista-agenda">Lista detalhada</h2>
            <p class="text-muted mb-0">
                <?= $diaFiltro ? "Exibindo só os horários de " . date('d/m/Y', strtotime($diaFiltro)) . "." : "Exibindo todos os horários." ?>
            </p>
        </div>
        <?php if ($diaFiltro): ?>
            <a href="<?= url("agendamentos/?mes=$mes&ano=$ano") ?>" class="btn btn-outline-secondary btn-sm">Mostrar todos os dias</a>
        <?php endif; ?>
    </div>

    <?php if (!$agendamentosLista): ?>
        <div class="estado-vazio mt-4">
            <i class="bi bi-calendar-x" aria-hidden="true"></i>
            <h2>Nenhum agendamento encontrado</h2>
            <p class="mb-0">Não há horários marcados para este filtro.</p>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <label class="visually-hidden" for="busca-agenda">Buscar agendamento</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-agenda" type="search" placeholder="Buscar por cliente ou procedimento na lista abaixo">
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
                        <th scope="col">Valor</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentosLista as $agenda): ?>
                        <tr data-item-lista>
                            <td data-label="Data e Hora">
                                <strong><?= e(date('d/m/Y', strtotime($agenda['dt_agendamento']))) ?></strong>
                                <span class="d-block small text-muted">
                                    <?= e(date('H:i', strtotime($agenda['dt_agendamento']))) ?> às <?= e(date('H:i', strtotime($agenda['dt_fim']))) ?>
                                </span>
                            </td>
                            <td data-label="Cliente"><strong><?= e($agenda['nm_cliente']) ?></strong></td>
                            <td data-label="Procedimento"><?= e($agenda['ds_procedimento']) ?></td>
                            <td data-label="Valor">
                                <?= $agenda['vl_procedimento'] ? 'R$ ' . number_format($agenda['vl_procedimento'], 2, ',', '.') : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td data-label="Status">
                                <span class="badge status-<?= e($agenda['fg_status']) ?>"><?= e($rotulosStatus[$agenda['fg_status']] ?? $agenda['fg_status']) ?></span>
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
                        <td colspan="6" class="text-center text-muted py-4">Nenhum agendamento encontrado com essa busca.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script src="<?= url('assets/js/agendamentos.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>