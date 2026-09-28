<?php
/**
 * Guia de componentes da GlowBella.
 * Mostra cada componente pronto e o HTML para copiar e colar nas páginas.
 * Só aparece no rodapé quando APP_DEBUG = true.
 */
require __DIR__ . '/../includes/init.php';

/** Mostra o exemplo renderizado e, logo abaixo, o código dele. */
function exemplo(string $titulo, string $html, string $descricao = ''): void
{
    echo '<h2 class="h4 mt-4">' . e($titulo) . '</h2>';
    if ($descricao) {
        echo '<p class="text-muted">' . $descricao . '</p>';
    }
    echo '<div class="guia-exemplo">' . $html . '</div>';
    echo '<pre class="guia-codigo"><code>' . e(trim($html)) . '</code></pre>';
}

$titulo = 'Guia de componentes';
require __DIR__ . '/../includes/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Guia de componentes</h1>
        <p>Copie o HTML de cada bloco para manter todas as telas com o mesmo visual.</p>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Use as classes normais do Bootstrap (<code>btn-primary</code>, <code>text-primary</code>...): elas já saem com as cores da GlowBella.
    As cores ficam em <code>public/assets/css/style.css</code>. Ícones: <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">icons.getbootstrap.com</a>.
</div>

<?php
exemplo('Cabeçalho de página', <<<'HTML'
<div class="cabecalho-pagina">
    <div>
        <h1>Produtos</h1>
        <p>Catálogo de brincos, piercings e acessórios.</p>
    </div>
    <a href="criar.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo produto
    </a>
</div>
HTML, 'Todo módulo começa com ele: título à esquerda e a ação principal à direita.');

exemplo('Botões', <<<'HTML'
<button type="button" class="btn btn-primary">Salvar</button>
<button type="button" class="btn btn-outline-primary">Editar</button>
<button type="button" class="btn btn-outline-secondary">Cancelar</button>
<button type="button" class="btn btn-outline-danger">Excluir</button>
<button type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
HTML, '<code>btn-primary</code> para a ação principal (uma por tela), <code>btn-outline-*</code> para as secundárias.');

exemplo('Tabela de listagem', <<<'HTML'
<div class="card-glow">
    <div class="table-responsive">
        <table class="table table-hover tabela-glow">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th class="text-end">Preço</th>
                    <th class="text-end">Estoque</th>
                    <th class="acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Argola lisa 12mm</td>
                    <td>Brincos</td>
                    <td class="text-end">R$ 59,90</td>
                    <td class="text-end">15</td>
                    <td class="acoes">
                        <a href="editar.php?id=1" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form action="excluir.php" method="post" class="d-inline">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="id" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir"
                                    data-confirmar="Excluir o produto &quot;Argola lisa 12mm&quot;?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <tr class="estoque-baixo">
                    <td>Piercing nostril curvo <span class="badge text-bg-warning">Estoque baixo</span></td>
                    <td>Piercings</td>
                    <td class="text-end">R$ 49,90</td>
                    <td class="text-end fw-semibold">2</td>
                    <td class="acoes">
                        <a href="editar.php?id=6" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
HTML, 'Excluir é sempre por <strong>formulário POST</strong> com <code>csrf_campo()</code>, nunca por link. O atributo <code>data-confirmar</code> pede confirmação automaticamente. A classe <code>estoque-baixo</code> destaca a linha.');

exemplo('Formulário', <<<'HTML'
<div class="card-glow p-4">
    <form method="post">
        <?= csrf_campo() ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label for="nome" class="form-label">Nome *</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="col-md-4">
                <label for="categoria" class="form-label">Categoria *</label>
                <select class="form-select" id="categoria" name="categoria_id" required>
                    <option value="">Selecione...</option>
                    <option value="1">Brincos</option>
                    <option value="2">Piercings</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="preco" class="form-label">Preço *</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="number" class="form-control is-invalid" id="preco" name="preco" step="0.01" min="0" value="-5">
                    <div class="invalid-feedback">O preço não pode ser negativo.</div>
                </div>
            </div>
            <div class="col-md-4">
                <label for="quantidade" class="form-label">Estoque mínimo</label>
                <input type="number" class="form-control" id="quantidade" name="estoque_minimo" min="0" value="0">
                <div class="form-text">Abaixo disso o produto aparece como "estoque baixo".</div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
        </div>
    </form>
</div>
HTML, 'Campos obrigatórios com <code>*</code>. Para mostrar erro de validação num campo, adicione <code>is-invalid</code> e um <code>invalid-feedback</code> logo depois.');

exemplo('Mensagens (flash)', <<<'HTML'
<div class="alert alert-success" role="alert">Produto cadastrado com sucesso!</div>
<div class="alert alert-danger mb-0" role="alert">Não foi possível excluir: o cliente tem agendamentos.</div>
HTML, 'Não escreva o alerta na mão: no PHP use <code>flash(\'Mensagem\')</code> ou <code>flash(\'Erro\', \'danger\')</code> e depois <code>redirecionar(\'produtos/\')</code>. O header mostra a mensagem sozinho.');

exemplo('Status de agendamento', <<<'HTML'
<span class="badge status-agendado">Agendado</span>
<span class="badge status-concluido">Concluído</span>
<span class="badge status-cancelado">Cancelado</span>
HTML);

exemplo('Card de resumo', <<<'HTML'
<div class="row g-3">
    <div class="col-sm-6 col-lg-4">
        <div class="card-glow p-3 d-flex align-items-center gap-3">
            <span class="icone-circulo"><i class="bi bi-calendar-heart"></i></span>
            <div>
                <div class="text-muted small">Agendamentos hoje</div>
                <div class="fs-4 fw-semibold">2</div>
            </div>
        </div>
    </div>
</div>
HTML);

exemplo('Lista vazia', <<<'HTML'
<div class="card-glow">
    <div class="estado-vazio">
        <i class="bi bi-people"></i>
        <h2>Nenhum cliente cadastrado</h2>
        <p>Cadastre a primeira cliente para começar a agendar.</p>
        <a href="criar.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nova cliente</a>
    </div>
</div>
HTML, 'Use sempre que uma listagem ou busca não tiver resultados.');
?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
