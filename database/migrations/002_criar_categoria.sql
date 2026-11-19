-- Categorias de produtos (ex.: Brincos, Piercings, Acessórios).
-- fg_status: 1 = ativa, 0 = inativa.

CREATE TABLE categoria (
    cd_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nm_categoria VARCHAR(60) NOT NULL,
    fg_status    TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (cd_categoria),
    UNIQUE KEY uk_categoria_nome (nm_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
