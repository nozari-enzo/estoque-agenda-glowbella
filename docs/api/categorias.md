# `api/categorias.php`: categorias

Regras gerais (login, CSRF, códigos de resposta): [`docs/api.md`](../api.md).

| Método | Endereço | O que faz |
|---|---|---|
| `GET` | `api/categorias.php` | Lista as categorias, com quantos produtos ativos cada uma tem |
| `GET` | `api/categorias.php?id=2` | Uma categoria |
| `POST` | `api/categorias.php` | Cria |
| `PUT` | `api/categorias.php?id=2` | Edita só os campos enviados (`fg_status: 1` reativa) |
| `DELETE` | `api/categorias.php?id=2` | Desativa |

**Filtros da listagem:**

| Filtro | Exemplo | Efeito |
|---|---|---|
| `busca` | `?busca=brin` | Parte do nome |
| `fg_status` | `?fg_status=todos` | `1` ativas (padrão), `0` inativas, `todos` |

**Campos:**

| Campo | Obrigatório | Regra |
|---|---|---|
| `nm_categoria` | Sim | Até 60 caracteres, sem repetir (maiúsculas e minúsculas contam como iguais) |
| `fg_status` | Não | `0` ou `1` (só no `PUT`) |

**Regras:**
- Não é possível **desativar** uma categoria que tem produtos ativos: a resposta é `409`. Desative ou mude a categoria dos produtos antes.
- Produtos só podem ser cadastrados em categorias **ativas**.

**Exemplo:**
```javascript
await glowApi('api/categorias.php', { method: 'POST', body: { nm_categoria: 'Colares' } })
// → { cd_categoria: 4, nm_categoria: 'Colares', fg_status: 1, qtd_produtos_ativos: 0 }
```
