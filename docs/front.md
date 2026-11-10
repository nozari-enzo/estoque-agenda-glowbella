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
├── editar.php       Página "Editar cliente" (carrega os dados pelo repository)
└── formulario.php   O <form>, usado pelo criar.php e pelo editar.php
public/assets/js/
└── clientes.js      Liga o formulário e o botão de desativar à API
```

---

## Exemplo completo: clientes

Este exemplo foi testado no navegador (cadastro, erros, edição, telefone repetido, desativação). Para uma tela nova, copie e troque os campos.

### 1. Lista: `public/clientes/index.php`

```php
<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';

// 1. Os dados vêm do repository, a mesma função que a api/clientes.php usa
$clientes = listar_clientes($pdo);

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

<section class="card-glow p-3 p-lg-4" data-lista-filtro data-busca="busca-clientes">
    <label class="visually-hidden" for="busca-clientes">Buscar cliente</label>
    <input class="form-control mb-3" id="busca-clientes" type="search" placeholder="Buscar por nome ou telefone">
    <p class="small text-muted" aria-live="polite" data-contador-resultados></p>

    <div class="table-responsive">
        <table class="table table-hover tabela-glow mb-0">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Telefone</th>
                    <th scope="col" class="acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <!-- 2. Sempre e() ao mostrar dados do banco -->
                    <tr data-item-lista>
                        <td><?= e($cliente['nm_cliente']) ?></td>
                        <td><?= e($cliente['nu_telefone']) ?></td>
                        <td class="acoes">
                            <a href="<?= url('clientes/editar.php?id=' . $cliente['cd_cliente']) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-desativar="<?= $cliente['cd_cliente'] ?>">Desativar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr data-sem-resultados class="d-none">
                    <td colspan="3" class="text-center text-muted py-4">Nenhuma cliente encontrada.</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<!-- 3. O JavaScript da tela fica em public/assets/js/<modulo>.js, com defer -->
<script src="<?= url('assets/js/clientes.js') ?>" defer></script>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
```

O que observar:
- Os `require` do `init.php` e do repository vêm **antes** do header. O `$pdo` vem do `init.php`.
- Cada linha tem `data-item-lista`, e a `<section>` tem `data-lista-filtro data-busca="id-do-campo-de-busca"`. A busca funciona sozinha.
- O `?salvo=1` é o jeito de mostrar "salvo com sucesso" depois que o JavaScript volta para a lista.

### 2. Formulário: `public/clientes/formulario.php`

```php
<?php
/**
 * Formulário de cliente, usado pelo criar.php e pelo editar.php.
 * Antes de incluir, defina $cliente (array com os dados, ou [] no cadastro).
 * O "name" de cada campo é o mesmo nome da API (nm_cliente, nu_telefone...).
 */
?>
<form id="form-cliente" class="card-glow p-3 p-lg-4" novalidate
      data-api="<?= e(isset($cliente['cd_cliente']) ? 'api/clientes.php?id=' . $cliente['cd_cliente'] : 'api/clientes.php') ?>"
      data-metodo="<?= isset($cliente['cd_cliente']) ? 'PUT' : 'POST' ?>">

    <!-- Mensagem geral de erro (fica escondida até a API responder com erro) -->
    <div class="alert alert-danger d-none" role="alert" data-erro-formulario></div>

    <div class="mb-3">
        <label class="form-label" for="nm_cliente">Nome *</label>
        <input class="form-control" id="nm_cliente" name="nm_cliente" maxlength="120"
               value="<?= e($cliente['nm_cliente'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label" for="nu_telefone">Telefone *</label>
        <input class="form-control" id="nu_telefone" name="nu_telefone" placeholder="(11) 90000-0000"
               value="<?= e($cliente['nu_telefone'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label" for="dt_nascimento">Data de nascimento</label>
        <input class="form-control" id="dt_nascimento" name="dt_nascimento" type="date"
               value="<?= e($cliente['dt_nascimento'] ?? '') ?>">
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= url('clientes/') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<script src="<?= url('assets/js/clientes.js') ?>" defer></script>
```

- `data-api` e `data-metodo` dizem para onde o formulário envia: `POST api/clientes.php` no cadastro e `PUT api/clientes.php?id=3` na edição.
- `novalidate` desliga a validação do navegador: quem valida é a API, e o erro aparece embaixo do campo do mesmo jeito em todas as telas.
- `data-erro-formulario` é onde aparece a mensagem geral ("Confira os campos da cliente.").

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
    <div><h1>Nova cliente</h1></div>
</div>

<?php require __DIR__ . '/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
```

```php
<?php
require __DIR__ . '/../../app/bootstrap/init.php';
require __DIR__ . '/../../app/repositories/clientes/clientes.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$cliente = $id ? buscar_cliente($pdo, $id) : null;

if (!$cliente) {
    flash('Cliente não encontrada.', 'danger');
    redirecionar('clientes/');
}

$titulo = 'Editar cliente';
$menu = 'clientes';
require __DIR__ . '/../../app/views/partials/header.php';
?>

<div class="cabecalho-pagina">
    <div><h1>Editar cliente</h1></div>
</div>

<?php require __DIR__ . '/formulario.php'; ?>
<?php require __DIR__ . '/../../app/views/partials/footer.php'; ?>
```

No `editar.php`, os dados vêm do repository (`buscar_cliente`) e preenchem o formulário pelo `value`. Se o id não existir, volta para a lista com uma mensagem (`flash` + `redirecionar`).

### 4. JavaScript: `public/assets/js/clientes.js`

```javascript
// Tela de clientes (public/clientes/). Guia: docs/front.md

// Formulário de cadastro e edição
const formCliente = document.getElementById('form-cliente');

if (formCliente) {
    glowFormulario(formCliente, {
        api: formCliente.dataset.api,
        method: formCliente.dataset.metodo,
        aoSalvar: () => { window.location.href = glowUrl('clientes/?salvo=1'); },
    });
}

// Botão de desativar da lista
document.querySelectorAll('[data-desativar]').forEach((botao) => {
    botao.addEventListener('click', async () => {
        if (!confirm('Desativar esta cliente?')) return;

        try {
            await glowApi('api/clientes.php?id=' + botao.dataset.desativar, { method: 'DELETE' });
            botao.closest('tr').remove();
        } catch (erro) {
            alert(erro.message);
        }
    });
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
