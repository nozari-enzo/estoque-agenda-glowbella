# API do GlowBella

As telas conversam com o banco por APIs em PHP que respondem **JSON**.

```
Tela (HTML + JavaScript)  ──glowApi()──▶  public/api/<modulo>.php  ──▶  app/repositories/<modulo>/  ──PDO──▶  MySQL
```

## Onde fica cada coisa

| Pasta | O que tem | Regra |
|---|---|---|
| `public/api/` | Um arquivo por recurso (`produtos.php`, `clientes.php`...). Recebe a requisição, valida e responde | Sem SQL e sem HTML |
| `app/repositories/<modulo>/` | As funções de SQL e a validação dos campos do módulo (ex.: `app/repositories/produtos/produtos.php`) | Único lugar com SQL |
| `app/services/` | Regras de negócio que vão além de um SQL simples (login, estoque, conflito de agenda...) | Só quando precisar |
| `app/support/api.php` | Funções comuns: responder JSON, ler o corpo, exigir login, CSRF, validações | Use em toda API |

## Regras gerais

- **Login:** toda API (menos o próprio login) começa com `exigir_login()`. Sem login, a resposta é `401`.
- **CSRF:** `POST`, `PUT` e `DELETE` exigem o cabeçalho `X-CSRF-Token`. A função `glowApi()` já envia.
- **Nada é apagado de verdade:** `DELETE` desativa (`fg_status = 0`).
- **Datas** em `AAAA-MM-DD`; **valores** com ponto (`59.9`) ou vírgula (`"59,90"`).
- **Estoque:** `qtd_produto` só muda por movimentação de entrada ou saída, nunca pelo `PUT` do produto.

## Respostas e erros

| Código | Quando |
|---|---|
| `200` | Deu certo |
| `201` | Registro criado (o corpo traz o registro) |
| `400` | Requisição malformada (JSON inválido, id inválido) |
| `401` | Não está logado, ou e-mail/senha incorretos |
| `404` | Registro não encontrado |
| `405` | Método não aceito nesse endereço |
| `409` | Conflito (ex.: dado repetido, registro em uso) |
| `419` | Token CSRF ausente ou vencido (recarregue a página) |
| `422` | Dados inválidos (veja `campos`) |
| `500` | Erro no servidor (com `APP_DEBUG = true`, vem `detalhe`) |

Formato de erro:
```json
{
  "erro": "Confira os campos do produto.",
  "campos": {
    "nm_produto": "Informe o nome do produto.",
    "vl_produto": "O valor de venda precisa ser um valor maior ou igual a zero."
  }
}
```

## Usando nas telas: `glowApi()`

Disponível em todas as páginas (`public/assets/js/app.js`):

```javascript
// Listar com filtro
const produtos = await glowApi('api/produtos.php?busca=argola');

// Criar
try {
    const novo = await glowApi('api/produtos.php', {
        method: 'POST',
        body: { nm_produto: 'Brinco gota', vl_produto: 44.9, cd_categoria: 1, qtd_inicial: 6 },
    });
} catch (erro) {
    // erro.message → mensagem geral, erro.status → 422, erro.campos → { nm_produto: '...' }
}

// Editar (só os campos que mudaram)
await glowApi('api/produtos.php?id=3', { method: 'PUT', body: { vl_produto: 64.9 } });

// Desativar
await glowApi('api/produtos.php?id=3', { method: 'DELETE' });
```

> ⚠️ Ao mostrar dados da API na tela, use `elemento.textContent = produto.nm_produto` (ou crie os elementos com `document.createElement`). **Nunca** monte HTML com `innerHTML` usando dados vindos da API: um nome como `<script>...` seria executado (XSS).

Para testar rápido sem tela: abra qualquer página do sistema, aperte **F12 → Console** e rode os comandos acima.

---

## `api/auth.php`: login

| Método | O que faz | Corpo |
|---|---|---|
| `GET` | Quem está logado (`401` se ninguém) | — |
| `POST` | Entrar | `{ "ds_email": "...", "ds_senha": "..." }` |
| `DELETE` | Sair | — |

Resposta do login: `{ "cd_usuario": 1, "nm_usuario": "Administradora" }`.

A regra de login fica em `app/services/auth.php` (`fazer_login()`, `usuario_logado()`, `fazer_logout()`) e pode ser usada também por um formulário de login tradicional.

## `api/produtos.php`: produtos

| Método | Endereço | O que faz |
|---|---|---|
| `GET` | `api/produtos.php` | Lista os produtos |
| `GET` | `api/produtos.php?id=3` | Um produto |
| `POST` | `api/produtos.php` | Cria |
| `PUT` | `api/produtos.php?id=3` | Edita só os campos enviados (não altera o estoque) |
| `DELETE` | `api/produtos.php?id=3` | Desativa |

**Filtros da listagem** (na URL, todos opcionais):

| Filtro | Exemplo | Efeito |
|---|---|---|
| `busca` | `?busca=argola` | Parte do nome ou do lote |
| `cd_categoria` | `?cd_categoria=2` | Só de uma categoria |
| `fg_finalidade` | `?fg_finalidade=procedimento` | `venda` ou `procedimento` |
| `fg_status` | `?fg_status=todos` | `1` ativos (padrão), `0` inativos, `todos` |
| `estoque_baixo` | `?estoque_baixo=1` | Só os abaixo do estoque mínimo |

**Campos aceitos no `POST` e no `PUT`:**

| Campo | Obrigatório | Regra |
|---|---|---|
| `nm_produto` | Sim | Até 120 caracteres |
| `vl_produto` | Sim | Valor ≥ 0 |
| `cd_categoria` | Sim | Categoria existente e ativa |
| `fg_finalidade` | Não (padrão `venda`) | `venda` ou `procedimento` |
| `ds_lote` | Não | Até 50 caracteres |
| `vl_compra` | Não (padrão `0`) | Valor ≥ 0 |
| `qtd_estoque_minimo` | Não (padrão `0`) | Inteiro ≥ 0 |
| `dt_validade` | Não | `AAAA-MM-DD` |
| `qtd_inicial` | Só no `POST` | Inteiro ≥ 0. Se > 0, registra a movimentação "Estoque inicial" |
| `fg_status` | Só no `PUT` | `0` ou `1` (para reativar um produto) |

**Exemplo de produto retornado:**
```json
{
  "cd_produto": 3,
  "nm_produto": "Ear cuff trançado",
  "fg_finalidade": "venda",
  "ds_lote": "L2026-02",
  "vl_compra": 10,
  "vl_produto": 29.9,
  "qtd_produto": 3,
  "qtd_estoque_minimo": 5,
  "dt_validade": null,
  "cd_categoria": 1,
  "nm_categoria": "Brincos",
  "fg_status": 1,
  "estoque_baixo": true
}
```

---

## Como criar uma API nova

Use produtos como modelo:

1. **Repositório** `app/repositories/<modulo>/<modulo>.php` com `listar_...`, `buscar_...`, `criar_...`, `atualizar_...`, `desativar_...` e `validar_...` (copie `app/repositories/produtos/produtos.php` e troque colunas e regras).
2. **API** `public/api/<recurso>.php`, copiando `produtos.php`:
   - `require` do `init.php`, do `api.php` e do seu repositório;
   - `exigir_login()` e `exigir_csrf()` no começo;
   - um `case` por método HTTP; o que não for aceito cai em `metodo_nao_permitido()`.
3. **Regra de negócio** que envolve mais de uma tabela ou uma conferência especial (ex.: movimentação de estoque, conflito de horário da agenda) vai em `app/services/<assunto>.php`, chamado pela API.
4. **Teste** no console do navegador com `glowApi()`: listar, criar, editar, desativar, e pelo menos um caso inválido (deve voltar `422` com `campos`).
5. **Documente** o recurso em `docs/api/<modulo>.md`, no mesmo formato de produtos, e marque na tabela abaixo.

### APIs do sistema

Cada API tem sua própria página de documentação:

| API | Tabela(s) | Documentação |
|---|---|---|
| `auth.php` | `admin` | Nesta página |
| `produtos.php` | `produto` | Nesta página |
| `categorias.php` | `categoria` | [`docs/api/categorias.md`](api/categorias.md) |
| `clientes.php` | `cliente` | [`docs/api/clientes.md`](api/clientes.md) |
| `agenda.php` | `agenda` (+ conflito de horário) | [`docs/api/agenda.md`](api/agenda.md) |
| `movimentacoes.php` | `movimentacao` + `produto.qtd_produto` | [`docs/api/movimentacoes.md`](api/movimentacoes.md) |
| `esterilizacoes.php` | `esterilizacao` + `esterilizacao_produto` | [`docs/api/esterilizacoes.md`](api/esterilizacoes.md) |

### Funções de apoio (`app/support/api.php`)

| Função | Para que serve |
|---|---|
| `responder($dados, $status)` / `responder_erro($msg, $status, $campos)` | Enviar a resposta JSON |
| `ler_json()` | Ler o corpo da requisição |
| `id_da_url()` / `exigir_id()` | Ler o `?id=` da URL |
| `exigir_login()` / `exigir_csrf()` | Proteger a API |
| `metodo()` / `metodo_nao_permitido([...])` | Método HTTP |
| `responder_erro_banco($erro)` | Traduzir erros do banco (repetido, em uso...) |
| `validar_texto`, `validar_inteiro`, `validar_valor`, `validar_data`, `validar_data_hora`, `validar_opcao` | Validar campos do JSON |
| `filtro_texto`, `filtro_inteiro`, `filtro_data`, `filtro_opcao` | Ler filtros da URL (`?busca=...`) |
| `mesclar_edicao($atual, $entrada, $campos)` | `PUT` parcial: o que não vier mantém o valor atual |
