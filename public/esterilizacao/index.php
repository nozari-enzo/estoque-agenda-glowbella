<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$titulo = 'Esterilização';
$menu = 'esterilizacao';
require __DIR__ . '/../../app/views/partials/header.php';

$ciclosDemo = [
    ['codigo' => 'C-026', 'data' => '04/10/2026', 'metodo' => 'Autoclave', 'pacotes' => 8, 'responsavel' => 'Equipe (demonstração)', 'indicadores' => 'Químico: aprovado · Biológico: aprovado', 'validade' => '04/11/2026', 'situacao' => 'Válido', 'badge' => 'text-bg-success'],
    ['codigo' => 'C-025', 'data' => '28/09/2026', 'metodo' => 'Autoclave', 'pacotes' => 5, 'responsavel' => 'Equipe (demonstração)', 'indicadores' => 'Químico: aprovado · Biológico: aprovado', 'validade' => '29/10/2026', 'situacao' => 'Válido', 'badge' => 'text-bg-success'],
    ['codigo' => 'C-024', 'data' => '03/09/2026', 'metodo' => 'Autoclave', 'pacotes' => 3, 'responsavel' => 'Equipe (demonstração)', 'indicadores' => 'Químico: aprovado · Biológico: aprovado', 'validade' => '03/10/2026', 'situacao' => 'Validade encerrada', 'badge' => 'text-bg-danger'],
];
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Ciclos de esterilização</h1>
        <p>Histórico de processamento, indicadores e validade dos pacotes.</p>
    </div>
    <button class="btn btn-primary" type="button"><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar ciclo</button>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start" role="status">
    <i class="bi bi-info-circle-fill mt-1" aria-hidden="true"></i>
    <div><strong>Protótipo visual.</strong> Os ciclos são demonstrativos. O registro não altera o estoque nem comprova conformidade sanitária; os critérios e prazos precisam ser definidos pela equipe.</div>
</div>

<section class="row g-3 mb-4" aria-label="Resumo demonstrativo dos ciclos">
    <div class="col-12 col-sm-6 col-xl-4">
        <article class="card-glow p-3 h-100">
            <div class="d-flex align-items-center gap-3"><span class="icone-circulo"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></span><div><p class="small text-muted mb-1">Ciclos registrados</p><strong class="fs-4">3</strong></div></div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <article class="card-glow p-3 h-100">
            <div class="d-flex align-items-center gap-3"><span class="icone-circulo"><i class="bi bi-box2-heart" aria-hidden="true"></i></span><div><p class="small text-muted mb-1">Pacotes demonstrativos</p><strong class="fs-4">16</strong></div></div>
        </article>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <article class="card-glow p-3 h-100">
            <div class="d-flex align-items-center gap-3"><span class="icone-circulo"><i class="bi bi-calendar-x" aria-hidden="true"></i></span><div><p class="small text-muted mb-1">Com validade encerrada</p><strong class="fs-4">1</strong></div></div>
        </article>
    </div>
</section>

<div class="row g-4 align-items-start">
    <div class="col-12 col-xl-4">
        <section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-novo-ciclo">
            <h2 class="h4 mb-1" id="titulo-novo-ciclo">Novo registro</h2>
            <p class="small text-muted mb-3">Campos ilustrativos para documentar um processamento.</p>

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-xl-12">
                    <label class="form-label" for="ciclo-codigo">Identificação do ciclo</label>
                    <input class="form-control" id="ciclo-codigo" type="text" placeholder="Ex.: C-027">
                </div>
                <div class="col-12 col-sm-6 col-xl-12">
                    <label class="form-label" for="ciclo-data">Data do processamento</label>
                    <input class="form-control" id="ciclo-data" type="date">
                </div>
                <div class="col-12 col-sm-6 col-xl-12">
                    <label class="form-label" for="ciclo-metodo">Método</label>
                    <select class="form-select" id="ciclo-metodo">
                        <option value="">Selecione...</option>
                        <option>Autoclave</option>
                        <option>Outro método</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-xl-12">
                    <label class="form-label" for="ciclo-responsavel">Responsável</label>
                    <input class="form-control" id="ciclo-responsavel" type="text" placeholder="Nome da pessoa responsável">
                </div>
                <div class="col-12">
                    <label class="form-label" for="ciclo-pacotes">Pacotes ou identificadores processados</label>
                    <textarea class="form-control" id="ciclo-pacotes" rows="3" placeholder="Informe os pacotes deste ciclo"></textarea>
                    <div class="form-text">A forma de identificar cada pacote será definida com o modelo do banco.</div>
                </div>
                <div class="col-12 col-sm-6 col-xl-12">
                    <label class="form-label" for="indicador-quimico">Indicador químico</label>
                    <select class="form-select" id="indicador-quimico">
                        <option value="">Selecione o resultado...</option>
                        <option>Aprovado</option>
                        <option>Reprovado</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-xl-12">
                    <label class="form-label" for="indicador-biologico">Indicador biológico</label>
                    <select class="form-select" id="indicador-biologico">
                        <option value="">Selecione o resultado...</option>
                        <option>Aprovado</option>
                        <option>Reprovado</option>
                        <option>Não aplicável / pendente de definição</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="ciclo-validade">Validade atribuída ao pacote</label>
                    <input class="form-control" id="ciclo-validade" type="date">
                    <div class="form-text">O sistema não calcula esse prazo neste protótipo.</div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                <button class="btn btn-outline-primary" type="button">Adicionar pacote</button>
                <button class="btn btn-primary" type="button"><i class="bi bi-check-lg" aria-hidden="true"></i> Salvar ciclo</button>
            </div>
        </section>
    </div>

    <div class="col-12 col-xl-8">
        <section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-historico-ciclos">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div><h2 class="h4 mb-1" id="titulo-historico-ciclos">Histórico</h2><p class="text-muted mb-0">Cada linha mantém os dados demonstrativos de um processamento.</p></div>
                <div class="input-group busca-ciclo">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <label class="visually-hidden" for="busca-ciclos">Buscar ciclo</label>
                    <input class="form-control" id="busca-ciclos" type="search" placeholder="Buscar ciclo ou responsável">
                </div>
            </div>

            <div class="table-responsive tabela-celular-wrapper">
                <table class="table table-hover tabela-glow tabela-celular-responsive tabela-ciclos mb-0">
                    <caption class="visually-hidden">Histórico demonstrativo dos ciclos de esterilização.</caption>
                    <colgroup>
                        <col class="col-ciclo">
                        <col class="col-pacotes">
                        <col class="col-indicadores">
                        <col class="col-validade">
                        <col class="col-situacao">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Ciclo / data</th>
                            <th scope="col">Pacotes</th>
                            <th scope="col">Indicadores</th>
                            <th scope="col">Validade do pacote</th>
                            <th scope="col">Situação</th>
                        </tr>
                    </thead>
                    <tbody data-lista-filtro data-busca="busca-ciclos">
                        <?php foreach ($ciclosDemo as $ciclo): ?>
                            <tr data-item-lista>
                                <td data-label="Ciclo / data"><strong><?= e($ciclo['codigo']) ?></strong><span class="d-block small text-muted"><?= e($ciclo['data']) ?> · <?= e($ciclo['metodo']) ?></span><span class="d-block small text-muted">Resp.: <?= e($ciclo['responsavel']) ?></span></td>
                                <td data-label="Pacotes"><?= e((string) $ciclo['pacotes']) ?></td>
                                <td data-label="Indicadores" class="small"><span class="d-block"><?= e($ciclo['indicadores']) ?></span></td>
                                <td data-label="Validade do pacote"><?= e($ciclo['validade']) ?></td>
                                <td data-label="Situação"><span class="badge <?= e($ciclo['badge']) ?>"><?= e($ciclo['situacao']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="d-none" data-sem-resultados>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum ciclo corresponde à busca.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
