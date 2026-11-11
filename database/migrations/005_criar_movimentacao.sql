-- Histórico de entradas e saídas de estoque.
-- Toda alteração em produto.qtd_produto deve gerar uma linha aqui.

CREATE TABLE movimentacao (
    cd_movimentacao INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cd_produto      INT UNSIGNED NOT NULL,
    cd_usuario      INT UNSIGNED NULL,
    fg_tipo         ENUM('entrada', 'saida') NOT NULL,
    qtd_movimentada INT UNSIGNED NOT NULL,
    ds_motivo       VARCHAR(150) NULL,
    dt_movimentacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cd_movimentacao),
    KEY idx_movimentacao_produto_data (cd_produto, dt_movimentacao),
    CONSTRAINT fk_movimentacao_produto
        FOREIGN KEY (cd_produto) REFERENCES produto (cd_produto)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_movimentacao_usuario
        FOREIGN KEY (cd_usuario) REFERENCES admin (cd_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT ck_movimentacao_quantidade CHECK (qtd_movimentada > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
