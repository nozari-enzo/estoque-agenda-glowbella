# Como fazer as telas (front-end)

Guia para ligar as telas às APIs. Todas as telas seguem o **mesmo padrão**, assim qualquer pessoa da equipe entende a tela de outra.

> Antes de começar, leia [`docs/api.md`](api.md) (o que cada API recebe e devolve) e abra o **Guia de componentes** (`componentes.php`) para copiar o visual.

## O padrão

| O que a tela faz | Como fazer | Por quê |
|---|---|---|
| **Mostrar a lista** quando a página abre | PHP chamando o **repository** (`listar_clientes($pdo)`) e `e()` no HTML | Mais simples, a página já abre com os dados e não tem risco de XSS |
| **Criar, editar, desativar** | JavaScript chamando a **API** com `glowFormulario()` / `glowApi()` | A API já valida, devolve o erro de cada campo e aplica as regras (conflito de horário, estoque negativo...) |
| **Busca e filtros** da lista | `data-lista-filtro` (já pronto no `app.js`) | Filtra no navegador as linhas que já estão na página |

```
Abrir a página:     clientes/index.php ──▶ listar_clientes($pdo) ──▶ MySQL        (PHP, sem API)
Salvar o formulário: clientes.js ──glowFormulario()──▶ api/clientes.php ──▶ MySQL (JavaScript + API)
```

### Regras

1. **Nada de SQL na página.** A página chama as funções de `app/repositories/<modulo>/`. Se faltar uma consulta, crie a função no repository.
2. **Nada de gravar pelo PHP da página.** Criar, editar e desativar sempre passam pela API, para que as validações e as regras fiquem num lugar só.
3. **Sempre `e()`** ao mostrar dados do banco no HTML. No JavaScript, use `textContent`, **nunca `innerHTML`** com dados vindos da API.
4. **O `name` de cada campo é o nome da API** (`nm_cliente`, `nu_telefone`, `cd_categoria`...). Assim o `glowFormulario()` envia o formulário sem nenhuma conversão.
5. **O JavaScript da tela fica em `public/assets/js/<modulo>.js`**, incluído com `defer` (nada de `<script>` com código dentro do HTML).
6. Links e redirecionamentos com `url()` no PHP e `glowUrl()` no JavaScript.

### Arquivos de uma tela

```
public/clientes/
├── index.php        Lista (PHP + repository)
├── criar.php        Página "Nova cliente"
└── editar.php       Página "Editar cliente" (carrega os dados pelo repository)
app/views/clientes/
└── formulario.php   O <form>, usado pelo criar.php e pelo editar.php (em app/, não abre direto pelo navegador)
public/assets/js/
└── clientes.js      Liga o formulário e os botões de desativar/reativar à API
```

---

## Exemplo completo: clientes

A tela de **Clientes** (`public/clientes/`) é a tela de referência: já está ligada às APIs e foi testada no navegador (cadastro, erros embaixo dos campos, telefone repetido, edição, desativar, reativar, celular). Para uma tela nova, copie os arquivos dela e troque os campos.

### 1. Lista: `public/clientes/index.php`

```php
<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';

// ?status=inativas mostra as clientes desativadas (para reativar)
$verInativas = ($_GET['status'] ?? '') === 'inativas';
$clientes = listar_clientes($pdo, ['fg_status' => $verInativas ? '0' : '1']);

$titulo = 'Clientes';
$menu = 'clientes';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Clientes</h1>
        <p>Cadastro e contato das clientes.</p>
    </div>
    <a href="<?= url('clientes/criar.php') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Nova cliente
    </a>
</div>

<?php if (isset($_GET['salvo'])): ?>
    <div class="alert alert-success" role="alert">Cliente salva com sucesso!</div>
<?php endif; ?>

<section class="card-glow p-3 p-lg-4" aria-labelledby="titulo-lista-clientes" data-lista-filtro data-busca="busca-clientes">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1" id="titulo-lista-clientes"><?= $verInativas ? 'Clientes desativadas' : 'Clientes ativas' ?></h2>
            <p class="text-muted mb-0">Pesquise pelo nome ou pelo telefone.</p>
        </div>
        <div class="btn-group" role="group" aria-label="Mostrar clientes">
            <a href="<?= url('clientes/') ?>" class="btn btn-sm <?= $verInativas ? 'btn-outline-primary' : 'btn-primary' ?>"<?= $verInativas ? '' : ' aria-current="page"' ?>>Ativas</a>
            <a href="<?= url('clientes/?status=inativas') ?>" class="btn btn-sm <?= $verInativas ? 'btn-primary' : 'btn-outline-primary' ?>"<?= $verInativas ? ' aria-current="page"' : '' ?>>Desativadas</a>
        </div>
    </div>

    <?php if (!$clientes): ?>
        <div class="estado-vazio">
            <i class="bi bi-people" aria-hidden="true"></i>
            <h2><?= $verInativas ? 'Nenhuma cliente desativada' : 'Nenhuma cliente cadastrada' ?></h2>
            <?php if (!$verInativas): ?>
                <p class="mb-0">Clique em <strong>Nova cliente</strong> para cadastrar a primeira.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <label class="visually-hidden" for="busca-clientes">Buscar cliente</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control" id="busca-clientes" type="search" placeholder="Buscar por nome ou telefone">
            </div>
        </div>

        <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

        <div class="table-responsive tabela-celular-wrapper">
            <table class="table table-hover tabela-glow tabela-celular-responsive mb-0">
                <caption class="visually-hidden">Lista de clientes.</caption>
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Telefone</th>
                        <th scope="col">Nascimento</th>
                        <th scope="col" class="acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr data-item-lista>
                            <td data-label="Nome"><strong><?= e($cliente['nm_cliente']) ?></strong></td>
                            <td data-label="Telefone"><?= e($cliente['nu_telefone']) ?></td>
                            <td data-label="Nascimento">
                                <?= $cliente['dt_nascimento'] ? e(date('d/m/Y', strtotime($cliente['dt_nascimento']))) : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td data-label="Ações" class="acoes">
                                <div class="d-inline-flex flex-wrap gap-2">
                                <?php if ($verInativas): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-reativar="<?= $cliente['cd_cliente'] ?>">Reativar</button>
                                <?php else: ?>
                                    <a href="<?= url('clientes/editar.php?id=' . $cliente['cd_cliente']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-desativar="<?= $cliente['cd_cliente'] ?>">Desativar</button>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultados class="d-none">
                        <td colspan="4" class="text-center text-muted py-4">Nenhuma cliente encontrada com essa busca.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script src="<?= url('assets/js/clientes.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
```

O que observar:
- Os `require` do `init.php` e do repository vêm **antes** do header. O `$pdo` vem do `init.php`.
- Cada linha tem `data-item-lista`, e a `<section>` tem `data-lista-filtro data-busca="id-do-campo-de-busca"`. A busca funciona sozinha.
- O `?salvo=1` é o jeito de mostrar "salvo com sucesso" depois que o JavaScript volta para a lista.
- Os botões de ação ficam dentro de um `<div class="d-inline-flex flex-wrap gap-2">`, para ficarem lado a lado também no celular.
- `?status=inativas` mostra as desativadas, com o botão **Reativar** (`PUT` com `fg_status: 1`).

### 2. Formulário: `app/views/clientes/formulario.php`

```php
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
```

- `data-api` e `data-metodo` dizem para onde o formulário envia: `POST api/clientes.php` no cadastro e `PUT api/clientes.php?id=3` na edição.
- `novalidate` desliga a validação do navegador: quem valida é a API, e o erro aparece embaixo do campo do mesmo jeito em todas as telas.
- `data-erro-formulario` é onde aparece a mensagem geral ("Confira os campos da cliente.").
- Fica em `app/views/<modulo>/` porque não é uma página: sozinho, ele não funciona (o `app/.htaccess` bloqueia o acesso direto).

### 3. Cadastro e edição: `criar.php` e `editar.php`

```php
<?php
require __DIR__ . '/../../app/bootstrap/init.php';

$cliente = [];   // cadastro: formulário vazio

$titulo = 'Nova cliente';
$menu = 'clientes';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div>
        <h1>Nova cliente</h1>
        <p>Preencha os dados de contato da cliente.</p>
    </div>
</div>

<?php require __DIR__ . '/../../app/views/clientes/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
```

```php
<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$cliente = $id ? buscar_cliente($pdo, $id) : null;

if (!$cliente || $cliente['fg_status'] !== 1) {
    flash('Cliente não encontrada ou desativada.', 'danger');
    redirecionar('clientes/');
}

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
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
```

No `editar.php`, os dados vêm do repository (`buscar_cliente`) e preenchem o formulário pelo `value`. Se o id não existir (ou a cliente estiver desativada), volta para a lista com uma mensagem (`flash` + `redirecionar`).

### 4. JavaScript: `public/assets/js/clientes.js`

```javascript
// Tela de clientes (public/clientes/). Padrão das telas: docs/front.md

// Formulário de cadastro e edição
const formCliente = document.getElementById('form-cliente');

if (formCliente) {
    glowFormulario(formCliente, {
        api: formCliente.dataset.api,
        method: formCliente.dataset.metodo,
        aoSalvar: () => { window.location.href = glowUrl('clientes/?salvo=1'); },
    });
}

// Desativar (lista de ativas) e reativar (lista de desativadas)
async function alterarSituacaoCliente(botao, opcoes, pergunta) {
    if (!confirm(pergunta)) return;

    botao.disabled = true;

    try {
        await glowApi('api/clientes.php?id=' + (botao.dataset.desativar || botao.dataset.reativar), opcoes);
        // Recarrega a lista (sem o aviso de "salvo" de antes)
        const endereco = new URL(window.location.href);
        endereco.searchParams.delete('salvo');
        window.location.href = endereco.toString();
    } catch (erro) {
        alert(erro.message);
        botao.disabled = false;
    }
}

document.querySelectorAll('[data-desativar]').forEach((botao) => {
    botao.addEventListener('click', () => alterarSituacaoCliente(botao, { method: 'DELETE' },
        'Desativar esta cliente? Ela sai da lista e não pode receber agendamentos novos.'));
});

document.querySelectorAll('[data-reativar]').forEach((botao) => {
    botao.addEventListener('click', () => alterarSituacaoCliente(botao, { method: 'PUT', body: { fg_status: 1 } },
        'Reativar esta cliente?'));
});
```

---

## Funções do `app.js`

Disponíveis em todas as páginas (o `footer.php` já carrega o `app.js`).

| Função | Para que serve |
|---|---|
| `glowFormulario(form, { api, method, aoSalvar })` | Liga um `<form>` a uma API: envia os campos, mostra os erros embaixo de cada campo e trava o botão enquanto salva |
| `glowApi(caminho, { method, body })` | Chama uma API diretamente (ex.: botão de desativar, dar baixa no estoque). Já envia o token CSRF |
| `glowUrl('clientes/')` | Endereço de uma página do sistema (igual ao `url()` do PHP) |
| `glowMostrarErros(form, erro)` / `glowLimparErros(form)` | Mostrar e limpar erros num formulário montado à mão |
| `data-confirmar="Mensagem?"` | Em um botão ou link: pede confirmação antes |
| `data-lista-filtro` | Busca e filtros da lista (veja abaixo) |

### `glowFormulario()` em detalhe

```javascript
glowFormulario(form, {
    api: 'api/agenda.php',                 // para onde envia
    method: 'POST',                        // POST no cadastro, PUT na edição
    aoSalvar: (resposta) => { ... },       // o que fazer quando der certo (a resposta é o registro salvo)

    // Opcionais:
    dados: (form) => ({ ... }),            // montar o corpo à mão (quando o form não bate 1 para 1 com a API)
    aoErrar: (erro) => { ... },            // depois de mostrar os erros (ex.: listar os conflitos da agenda)
});
```

- Os campos vão pelo `name`. Campo vazio vai como `""`, e a API entende como "não informado". Na edição, isso **limpa** um campo opcional, como a data de nascimento.
- Checkbox desmarcado não é enviado. Se precisar de `0`/`1`, use `dados`.
- Para campo dentro de `input-group`, coloque a classe `has-validation` no `input-group`, senão o Bootstrap não mostra o aviso.

**Exemplo com `dados` e `aoErrar` (agenda):**
```javascript
glowFormulario(formAgenda, {
    api: 'api/agenda.php',
    method: 'POST',
    dados: (form) => ({
        cd_cliente: form.cd_cliente.value,
        dt_agendamento: form.dt_agendamento.value,        // valor do <input type="datetime-local">
        qtd_duracao_min: Number(form.qtd_duracao_min.value),
        ds_procedimento: form.ds_procedimento.value.trim(),
    }),
    aoSalvar: () => { window.location.href = glowUrl('agendamentos/?salvo=1'); },
    aoErrar: (erro) => {
        if (erro.status === 409) {
            // erro.dados.conflitos = agendamentos que já ocupam o horário
            const lista = document.getElementById('lista-conflitos');
            lista.replaceChildren(...erro.dados.conflitos.map((c) => {
                const item = document.createElement('li');
                item.textContent = `${c.nm_cliente}: ${c.dt_agendamento.slice(11, 16)} às ${c.dt_fim.slice(11, 16)}`;
                return item;
            }));
        }
    },
});
```

### Busca e filtros

```html
<section data-lista-filtro data-busca="busca-agenda" data-status="status-agenda">
    <input id="busca-agenda" type="search">
    <select id="status-agenda"><option value="">Todos</option><option value="agendado">Agendado</option></select>
    <p data-contador-resultados></p>

    <tr data-item-lista data-status-item="agendado">...</tr>       <!-- cada linha -->
    <tr data-sem-resultados class="d-none">...</tr>                <!-- aparece quando nada bate -->
</section>
```

`data-busca` procura em todo o texto da linha. `data-status` e `data-tipo` comparam com `data-status-item` e `data-tipo-item` de cada linha.

---

## Erros: o que aparece para a pessoa

O `glowFormulario()` já trata tudo isso. Com `glowApi()` direto, use `try/catch` e mostre `erro.message`.

| Código | Quando | O que aparece |
|---|---|---|
| `422` | Campo inválido | A mensagem geral e o erro embaixo de cada campo (`erro.campos`) |
| `409` | Conflito (telefone repetido, horário ocupado, categoria em uso) | A mensagem. Na agenda, `erro.dados.conflitos` traz os horários |
| `419` | A sessão expirou | "Sessão expirada. Recarregue a página e tente novamente." |
| `401` | Não está logado | "Faça login para continuar." |
| `0` | Servidor fora do ar ou sem rede | "Não foi possível falar com o servidor." |

---

## Qual repository e qual API cada tela usa

| Tela | Lista (PHP) | Salvar (JavaScript) | Documentação |
|---|---|---|---|
| Clientes | `listar_clientes`, `buscar_cliente` | `api/clientes.php` | [clientes](api/clientes.md) |
| Produtos | `listar_produtos`, `buscar_produto` + `listar_categorias` (para o `<select>`) | `api/produtos.php` | [api.md](api.md#apiprodutosphp-produtos) |
| Categorias | `listar_categorias` | `api/categorias.php` | [categorias](api/categorias.md) |
| Agenda | `listar_agenda`, `buscar_agendamento` + `listar_clientes` (para o `<select>`) | `api/agenda.php` (cancelar = `DELETE`) | [agenda](api/agenda.md) |
| Estoque | `listar_produtos` + `listar_movimentacoes` (histórico) | `api/movimentacoes.php` (entrada e saída) | [movimentações](api/movimentacoes.md) |
| Esterilização | `listar_esterilizacoes`, `buscar_esterilizacao` + `listar_produtos` | `api/esterilizacoes.php` | [esterilizações](api/esterilizacoes.md) |
| Início | `listar_agenda($pdo, ['data' => date('Y-m-d')])`, `listar_produtos($pdo, ['estoque_baixo' => true])`, `listar_esterilizacoes($pdo, ['situacao' => 'vencido'])` | — | — |

Os filtros de cada `listar_...` são os mesmos da API (veja a documentação de cada uma).

---

## Checklist antes de abrir o PR

- [ ] A lista vem do repository, sem SQL na página
- [ ] Todo dado do banco no HTML passa por `e()`
- [ ] Os `name` dos campos são os nomes da API
- [ ] Criar, editar e desativar usam `glowFormulario()` / `glowApi()`
- [ ] O JavaScript está em `public/assets/js/<modulo>.js` com `defer`
- [ ] Testei: salvar vazio (aparecem os erros), salvar certo, editar e desativar
- [ ] Conferi no celular (sem rolagem para o lado)
- [ ] O bloco "Em construção" da página provisória foi apagado

> **Login:** por enquanto as páginas abrem sem login (só as APIs exigem). Quando a tela de login ficar pronta, cada página vai ganhar uma linha de proteção logo depois do `init.php`. Este guia será atualizado.
