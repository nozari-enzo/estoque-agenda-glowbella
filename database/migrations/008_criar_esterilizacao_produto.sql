-- Produtos esterilizados em cada ciclo (um ciclo pode levar vários produtos).

CREATE TABLE esterilizacao_produto (
    cd_esterilizacao INT UNSIGNED NOT NULL,
    cd_produto       INT UNSIGNED NOT NULL,
    qtd_produto      INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (cd_esterilizacao, cd_produto),
    KEY idx_esterilizacao_produto_produto (cd_produto),
    CONSTRAINT fk_esterilizacao_produto_ciclo
        FOREIGN KEY (cd_esterilizacao) REFERENCES esterilizacao (cd_esterilizacao)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_esterilizacao_produto_produto
        FOREIGN KEY (cd_produto) REFERENCES produto (cd_produto)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_esterilizacao_produto_qtd CHECK (qtd_produto > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
