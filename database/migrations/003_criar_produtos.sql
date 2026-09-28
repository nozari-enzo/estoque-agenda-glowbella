-- Produtos da loja (brincos, piercings...).
-- "quantidade" é o estoque atual. Ela só deve mudar através de uma
-- movimentação (tabela movimentacoes_estoque), para manter o histórico.
-- Produtos não são apagados: são desativados (ativo = 0), para não perder o histórico.

CREATE TABLE produtos (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id   INT UNSIGNED NOT NULL,
    codigo         VARCHAR(30) NULL,
    nome           VARCHAR(120) NOT NULL,
    descricao      TEXT NULL,
    material       VARCHAR(60) NULL,
    preco          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantidade     INT UNSIGNED NOT NULL DEFAULT 0,
    estoque_minimo INT UNSIGNED NOT NULL DEFAULT 0,
    foto           VARCHAR(255) NULL,
    ativo          TINYINT(1) NOT NULL DEFAULT 1,
    criado_em      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_produtos_codigo (codigo),
    KEY idx_produtos_nome (nome),
    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_produtos_preco CHECK (preco >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
