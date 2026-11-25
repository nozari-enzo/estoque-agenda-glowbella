-- Valor total de uma venda de balcão (saída com motivo "Venda"), para o Financeiro.
-- Nas outras movimentações fica NULL.
ALTER TABLE movimentacao
    ADD COLUMN vl_total DECIMAL(10,2) NULL AFTER qtd_movimentada;

-- Vendas registradas antes desta migration: usa o preço de venda atual do produto
UPDATE movimentacao m
JOIN produto p ON p.cd_produto = m.cd_produto
SET m.vl_total = m.qtd_movimentada * p.vl_produto
WHERE m.fg_tipo = 'saida' AND m.ds_motivo = 'Venda' AND m.vl_total IS NULL;
