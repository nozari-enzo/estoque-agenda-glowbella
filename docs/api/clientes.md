# `api/clientes.php`: clientes

Regras gerais (login, CSRF, códigos de resposta): [`docs/api.md`](../api.md).

| Método | Endereço | O que faz |
|---|---|---|
| `GET` | `api/clientes.php` | Lista as clientes |
| `GET` | `api/clientes.php?id=1` | Uma cliente |
| `POST` | `api/clientes.php` | Cria |
| `PUT` | `api/clientes.php?id=1` | Edita só os campos enviados (`fg_status: 1` reativa) |
| `DELETE` | `api/clientes.php?id=1` | Desativa |

**Filtros da listagem:**

| Filtro | Exemplo | Efeito |
|---|---|---|
| `busca` | `?busca=ana` ou `?busca=90000-0003` | Parte do nome **ou** do telefone (só os números contam) |
| `fg_status` | `?fg_status=todos` | `1` ativas (padrão), `0` inativas, `todos` |

**Campos:**

| Campo | Obrigatório | Regra |
|---|---|---|
| `nm_cliente` | Sim | Até 120 caracteres |
| `nu_telefone` | Sim | DDD + número (10 ou 11 dígitos), sem repetir |
| `dt_nascimento` | Não | `AAAA-MM-DD`, não pode ser no futuro |
| `fg_status` | Não | `0` ou `1` (só no `PUT`) |

**Regras:**
- O telefone é salvo sempre no formato `(11) 90000-0000` (celular) ou `(11) 3000-0000` (fixo), **não importa como foi digitado**: `11900000001`, `11 90000-0001` e `(11) 90000-0001` viram o mesmo número. Por isso a checagem de telefone repetido funciona.
- Cliente desativada não aparece na listagem padrão e não pode receber agendamentos novos.

**Exemplo:**
```javascript
await glowApi('api/clientes.php', { method: 'POST', body: { nm_cliente: 'Fernanda Alves', nu_telefone: '11 98765-4321', dt_nascimento: '2000-05-10' } })
// → { cd_cliente: 6, nm_cliente: 'Fernanda Alves', dt_nascimento: '2000-05-10', nu_telefone: '(11) 98765-4321', fg_status: 1 }
```
