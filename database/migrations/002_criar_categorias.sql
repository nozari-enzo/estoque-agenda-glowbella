-- Categorias de produtos (ex.: Brincos, Piercings, Acessórios).

CREATE TABLE categorias (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(60) NOT NULL,
    descricao     VARCHAR(255) NULL,
    criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
