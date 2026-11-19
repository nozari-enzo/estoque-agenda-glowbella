-- Usuários que acessam o sistema (dona da loja e funcionárias).
-- ds_senha guarda o hash gerado por password_hash() do PHP, nunca a senha em texto.
-- fg_status: 1 = ativo, 0 = inativo.

CREATE TABLE admin (
    cd_usuario  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nm_usuario  VARCHAR(100) NOT NULL,
    ds_email    VARCHAR(150) NOT NULL,
    ds_senha    VARCHAR(255) NOT NULL,
    fg_status   TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (cd_usuario),
    UNIQUE KEY uk_admin_email (ds_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
