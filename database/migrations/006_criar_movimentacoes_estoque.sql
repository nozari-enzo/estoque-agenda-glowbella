-- Histórico de entradas e saídas de estoque.
-- Toda alteração em produtos.quantidade deve gerar uma linha aqui.

CREATE TABLE movimentacoes_estoque (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    produto_id  INT UNSIGNED NOT NULL,
    usuario_id  INT UNSIGNED NULL,
    tipo        ENUM('entrada', 'saida') NOT NULL,
    motivo      ENUM('compra', 'venda', 'ajuste', 'perda', 'devolucao') NOT NULL,
    quantidade  INT UNSIGNED NOT NULL,
    observacao  VARCHAR(255) NULL,
    criado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_movimentacoes_produto_data (produto_id, criado_em),
    CONSTRAINT fk_movimentacoes_produto
        FOREIGN KEY (produto_id) REFERENCES produtos (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_movimentacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT ck_movimentacoes_quantidade CHECK (quantidade > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
