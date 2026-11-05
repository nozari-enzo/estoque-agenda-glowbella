-- Clientes da loja.
-- nu_telefone é texto para não perder o zero à esquerda nem a formatação.
-- fg_status: 1 = ativa, 0 = inativa.

CREATE TABLE cliente (
    cd_cliente    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nm_cliente    VARCHAR(120) NOT NULL,
    dt_nascimento DATE NULL,
    nu_telefone   VARCHAR(20) NOT NULL,
    fg_status     TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (cd_cliente),
    UNIQUE KEY uk_cliente_telefone (nu_telefone),
    KEY idx_cliente_nome (nm_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
