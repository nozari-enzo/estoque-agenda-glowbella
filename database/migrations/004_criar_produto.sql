-- Produtos da loja (brincos, piercings, acessórios).
-- fg_finalidade: 'venda' (vendido à cliente) ou 'procedimento' (usado nos atendimentos).
-- qtd_produto é o estoque atual: só deve mudar junto com um registro em movimentacao.
-- Produtos não são apagados: são desativados (fg_status = 0), para não perder o histórico.

CREATE TABLE produto (
    cd_produto         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nm_produto         VARCHAR(120) NOT NULL,
    fg_finalidade      ENUM('venda', 'procedimento') NOT NULL DEFAULT 'venda',
    ds_lote            VARCHAR(50) NULL,
    vl_compra          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    vl_produto         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    qtd_produto        INT UNSIGNED NOT NULL DEFAULT 0,
    qtd_estoque_minimo INT UNSIGNED NOT NULL DEFAULT 0,
    dt_validade        DATE NULL,
    cd_categoria       INT UNSIGNED NOT NULL,
    fg_status          TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (cd_produto),
    KEY idx_produto_nome (nm_produto),
    CONSTRAINT fk_produto_categoria
        FOREIGN KEY (cd_categoria) REFERENCES categoria (cd_categoria)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_produto_valores CHECK (vl_compra >= 0 AND vl_produto >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
