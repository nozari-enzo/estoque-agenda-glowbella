-- Ciclos de esterilização (ex.: autoclave).
-- Os indicadores começam como 'pendente' (o biológico costuma levar dias) e
-- depois passam para 'aprovado' ou 'reprovado'.
-- fg_status: 1 = ciclo ativo, 0 = descartado/cancelado.

CREATE TABLE esterilizacao (
    cd_esterilizacao          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nm_metodo                 VARCHAR(50) NOT NULL,
    dt_esterilizacao          DATE NOT NULL,
    dt_validade_esterilizacao DATE NOT NULL,
    fg_indicador_quimico      ENUM('pendente', 'aprovado', 'reprovado') NOT NULL DEFAULT 'pendente',
    fg_indicador_biologico    ENUM('pendente', 'aprovado', 'reprovado') NOT NULL DEFAULT 'pendente',
    fg_status                 TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (cd_esterilizacao),
    KEY idx_esterilizacao_data (dt_esterilizacao),
    CONSTRAINT ck_esterilizacao_validade CHECK (dt_validade_esterilizacao >= dt_esterilizacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
