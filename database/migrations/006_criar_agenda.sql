-- Agendamentos de procedimentos.
-- dt_agendamento e dt_fim guardam data + hora, o que permite verificar conflito:
--   conflito = existe agendamento não cancelado com dt_agendamento < novo_fim AND dt_fim > novo_inicio
-- fg_status: 'agendado', 'concluido' ou 'cancelado'.

CREATE TABLE agenda (
    cd_agenda       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cd_cliente      INT UNSIGNED NOT NULL,
    dt_agendamento  DATETIME NOT NULL,
    dt_fim          DATETIME NOT NULL,
    ds_procedimento VARCHAR(150) NOT NULL,
    fg_status       ENUM('agendado', 'concluido', 'cancelado') NOT NULL DEFAULT 'agendado',
    PRIMARY KEY (cd_agenda),
    KEY idx_agenda_data (dt_agendamento),
    KEY idx_agenda_cliente (cd_cliente),
    CONSTRAINT fk_agenda_cliente
        FOREIGN KEY (cd_cliente) REFERENCES cliente (cd_cliente)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_agenda_periodo CHECK (dt_fim > dt_agendamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
