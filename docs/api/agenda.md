# `api/agenda.php`: agenda

Regras gerais (login, CSRF, códigos de resposta): [`docs/api.md`](../api.md).

| Método | Endereço | O que faz |
|---|---|---|
| `GET` | `api/agenda.php` | Lista os agendamentos (do mais cedo para o mais tarde) |
| `GET` | `api/agenda.php?id=1` | Um agendamento |
| `POST` | `api/agenda.php` | Agenda (confere conflito de horário) |
| `PUT` | `api/agenda.php?id=1` | Edita, remarca ou conclui (só os campos enviados) |
| `DELETE` | `api/agenda.php?id=1` | Cancela (`fg_status = 'cancelado'`) |

**Filtros da listagem:**

| Filtro | Exemplo | Efeito |
|---|---|---|
| `data` | `?data=2026-11-10` | Um dia |
| `de` / `ate` | `?de=2026-11-10&ate=2026-11-16` | Um período (inclusive) — use para a visão da semana/mês |
| `cd_cliente` | `?cd_cliente=2` | Histórico de uma cliente |
| `fg_status` | `?fg_status=agendado` | `agendado`, `concluido` ou `cancelado` (padrão: todos) |

**Campos:**

| Campo | Obrigatório | Regra |
|---|---|---|
| `cd_cliente` | Sim | Cliente existente e ativa |
| `dt_agendamento` | Sim | Início: `AAAA-MM-DD HH:MM` (aceita o valor do `<input type="datetime-local">`) |
| `qtd_duracao_min` **ou** `dt_fim` | Sim (um dos dois) | Duração em minutos, ou o fim em `AAAA-MM-DD HH:MM`. O fim precisa ser depois do início |
| `ds_procedimento` | Sim | Até 150 caracteres (ex.: "Aplicação de piercing - hélix") |
| `fg_status` | Não (padrão `agendado`) | `agendado`, `concluido` ou `cancelado` |

**Regras:**
- **Conflito de horário:** não é possível ter dois agendamentos (não cancelados) se sobrepondo. Horários encostados são permitidos (ex.: 10:00–10:30 e 10:30–11:00). No conflito, a resposta é `409` e traz em `conflitos` os agendamentos que ocupam o horário:
  ```json
  {
    "erro": "Já existe agendamento nesse horário.",
    "campos": { "dt_agendamento": "Horário ocupado. Escolha outro horário." },
    "conflitos": [{ "cd_agenda": 1, "nm_cliente": "Ana Souza", "dt_agendamento": "2026-11-10 10:00:00", "dt_fim": "2026-11-10 10:30:00", "...": "..." }]
  }
  ```
- **Remarcar:** um `PUT` só com `dt_agendamento` mantém a mesma duração.
- **Concluir:** `PUT` com `{ "fg_status": "concluido" }`.
- **Cancelar** libera o horário para outro agendamento.
- Cada agendamento já vem com `nm_cliente` e `nu_telefone` da cliente.

**Exemplos:**
```javascript
// Agenda de hoje
await glowApi('api/agenda.php?data=2026-11-10')

// Novo agendamento de 30 minutos
await glowApi('api/agenda.php', { method: 'POST', body: {
    cd_cliente: 2, dt_agendamento: '2026-11-10T15:00', qtd_duracao_min: 30, ds_procedimento: 'Troca de joia'
} })

// Tratando conflito
try {
    await glowApi('api/agenda.php', { method: 'POST', body: { /* ... */ } })
} catch (erro) {
    if (erro.status === 409) { /* mostrar "horário ocupado" */ }
}
```
