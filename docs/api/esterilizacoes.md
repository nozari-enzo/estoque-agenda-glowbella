# `api/esterilizacoes.php`: esterilizações

Regras gerais (login, CSRF, códigos de resposta): [`docs/api.md`](../api.md).

| Método | Endereço | O que faz |
|---|---|---|
| `GET` | `api/esterilizacoes.php` | Lista os ciclos (do mais recente para o mais antigo), cada um com seus produtos |
| `GET` | `api/esterilizacoes.php?id=1` | Um ciclo, com seus produtos |
| `POST` | `api/esterilizacoes.php` | Registra um ciclo com os produtos esterilizados |
| `PUT` | `api/esterilizacoes.php?id=1` | Edita só os campos enviados (ex.: resultado do indicador biológico) |
| `DELETE` | `api/esterilizacoes.php?id=1` | Desativa o ciclo |

**Filtros da listagem:**

| Filtro | Exemplo | Efeito |
|---|---|---|
| `situacao` | `?situacao=vencido` | `valido`, `pendente`, `vencido` ou `reprovado` |
| `cd_produto` | `?cd_produto=5` | Ciclos que levaram esse produto |
| `de` / `ate` | `?de=2026-11-01&ate=2026-11-30` | Período da esterilização (inclusive) |
| `fg_status` | `?fg_status=todos` | `1` ativos (padrão), `0` inativos, `todos` |

**Campos:**

| Campo | Obrigatório | Regra |
|---|---|---|
| `nm_metodo` | Sim | Até 50 caracteres (ex.: "Autoclave") |
| `dt_esterilizacao` | Sim | `AAAA-MM-DD`, não pode ser no futuro |
| `dt_validade_esterilizacao` | Sim | `AAAA-MM-DD`, não pode ser antes da data da esterilização |
| `fg_indicador_quimico` | Não (padrão `pendente`) | `pendente`, `aprovado` ou `reprovado` |
| `fg_indicador_biologico` | Não (padrão `pendente`) | `pendente`, `aprovado` ou `reprovado` |
| `produtos` | Sim no `POST`; no `PUT`, só se quiser trocar a lista | `[{ "cd_produto": 5, "qtd_produto": 2 }, ...]`. `qtd_produto` é opcional (padrão 1). Produtos ativos, sem repetir |
| `fg_status` | Não | `0` ou `1` (só no `PUT`) |

**Situação do ciclo (`situacao`)**, calculada automaticamente:

| Situação | Quando |
|---|---|
| `reprovado` | Algum indicador reprovado |
| `vencido` | A validade já passou |
| `pendente` | Algum indicador ainda pendente (o biológico costuma levar alguns dias) |
| `valido` | Indicadores aprovados e dentro da validade |

**Regras:**
- O ciclo e a lista de produtos são gravados **juntos** (mesma transação).
- No `PUT`, se vier `produtos`, a lista do ciclo é **substituída** pela nova; se não vier, os produtos continuam os mesmos.
- A esterilização **não altera o estoque**: ela só registra quais produtos passaram pelo ciclo.
- O prazo de validade e os critérios de aprovação ainda precisam ser confirmados com a cliente; a API só garante que a validade não seja antes da data do ciclo.

**Exemplos:**
```javascript
// Registrar um ciclo com dois produtos
await glowApi('api/esterilizacoes.php', { method: 'POST', body: {
    nm_metodo: 'Autoclave',
    dt_esterilizacao: '2026-11-10',
    dt_validade_esterilizacao: '2026-12-10',
    fg_indicador_quimico: 'aprovado',
    produtos: [{ cd_produto: 5, qtd_produto: 2 }, { cd_produto: 7 }],
} })
// → { cd_esterilizacao: 4, ..., situacao: 'pendente', produtos: [...] }

// Dias depois: resultado do indicador biológico
await glowApi('api/esterilizacoes.php?id=4', { method: 'PUT', body: { fg_indicador_biologico: 'aprovado' } })
// → { ..., situacao: 'valido' }

// Ciclos vencidos (para o alerta da Home)
await glowApi('api/esterilizacoes.php?situacao=vencido')
```
