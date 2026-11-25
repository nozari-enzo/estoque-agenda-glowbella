# `api/movimentacoes.php`: movimentações de estoque

Regras gerais (login, CSRF, códigos de resposta): [`docs/api.md`](../api.md).

| Método | Endereço | O que faz |
|---|---|---|
| `GET` | `api/movimentacoes.php` | Histórico (das mais recentes para as mais antigas) |
| `GET` | `api/movimentacoes.php?id=1` | Uma movimentação |
| `POST` | `api/movimentacoes.php` | Registra uma entrada ou saída **e atualiza o estoque do produto** |

Não existe `PUT` nem `DELETE` (resposta `405`): o histórico não é alterado. Para corrigir um lançamento errado, registre uma movimentação contrária com o motivo "Ajuste de inventário".

**Filtros da listagem:**

| Filtro | Exemplo | Efeito |
|---|---|---|
| `cd_produto` | `?cd_produto=3` | Histórico de um produto |
| `fg_tipo` | `?fg_tipo=saida` | `entrada` ou `saida` |
| `de` / `ate` | `?de=2026-11-01&ate=2026-11-30` | Período (inclusive) |
| `limite` | `?limite=20` | Quantidade máxima (padrão 100, máximo 500) |

**Campos do `POST`:**

| Campo | Obrigatório | Regra |
|---|---|---|
| `cd_produto` | Sim | Produto existente e ativo |
| `fg_tipo` | Sim | `entrada` ou `saida` |
| `qtd_movimentada` | Sim | Inteiro a partir de 1 |
| `ds_motivo` | Sim | Até 150 caracteres. Use um motivo da lista (abaixo) ou escreva outro |
| `vl_total` | Só na venda | Valor total da venda (≥ 0). Obrigatório quando `fg_tipo` = `saida` e `ds_motivo` = `Venda`; ignorado nas outras |

**Motivos da lista** (constante `MOTIVOS_MOVIMENTACAO` em `app/services/estoque.php`):

| Tipo | Motivos |
|---|---|
| `entrada` | Compra fornecedor, Devolução, Ajuste de inventário |
| `saida` | Venda, Uso em procedimento, Perda ou avaria, Ajuste de inventário |

Dois motivos contam no **Financeiro**, por isso são gravados sempre com o texto exato (`"venda"` ou `"VENDA "` viram `"Venda"`):
- **`Venda`** (só saída): o `vl_total` entra como receita de vendas de balcão.
- **`Compra fornecedor`** (só entrada): quantidade × preço de compra do produto entra como gasto.

Venda como entrada, ou compra como saída, é recusada com `422`.

**Regras:**
- A movimentação e a mudança em `produto.qtd_produto` acontecem **juntas** (mesma transação): ou as duas são gravadas, ou nenhuma.
- **Saída maior que o estoque é recusada** com `422`, e a mensagem informa quanto há disponível. O estoque nunca fica negativo, mesmo com duas saídas ao mesmo tempo.
- Quem registrou fica salvo (`cd_usuario` / `nm_usuario`), a partir do login.
- A resposta do `POST` traz `qtd_produto_atual`: o estoque do produto depois da movimentação.
- O estoque inicial de um produto é registrado automaticamente (motivo "Estoque inicial") quando ele é criado com `qtd_inicial` (API de produtos). Não conta como gasto no Financeiro.

**Exemplos:**
```javascript
// Entrada de compra
await glowApi('api/movimentacoes.php', { method: 'POST', body: { cd_produto: 3, fg_tipo: 'entrada', qtd_movimentada: 10, ds_motivo: 'Compra fornecedor' } })
// → { cd_movimentacao: 9, ..., qtd_produto_atual: 13 }

// Venda de balcão (vai para o Financeiro)
await glowApi('api/movimentacoes.php', { method: 'POST', body: { cd_produto: 3, fg_tipo: 'saida', qtd_movimentada: 2, ds_motivo: 'Venda', vl_total: 110 } })

// Saída sem estoque suficiente
try {
    await glowApi('api/movimentacoes.php', { method: 'POST', body: { cd_produto: 3, fg_tipo: 'saida', qtd_movimentada: 50, ds_motivo: 'Perda ou avaria' } })
} catch (erro) {
    erro.message  // "Estoque insuficiente: há 13 unidade(s) disponível(is)."
}

// Histórico de um produto
await glowApi('api/movimentacoes.php?cd_produto=3')
```
