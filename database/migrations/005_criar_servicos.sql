-- Serviços que podem ser agendados (ex.: aplicação de piercing).

CREATE TABLE servicos (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome             VARCHAR(100) NOT NULL,
    descricao        VARCHAR(255) NULL,
    duracao_minutos  SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    preco            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ativo            TINYINT(1) NOT NULL DEFAULT 1,
    criado_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_servicos_nome (nome),
    CONSTRAINT ck_servicos_preco CHECK (preco >= 0),
    CONSTRAINT ck_servicos_duracao CHECK (duracao_minutos > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
