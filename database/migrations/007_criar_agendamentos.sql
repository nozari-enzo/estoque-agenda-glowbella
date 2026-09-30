-- Agendamentos de serviços.
-- "inicio" e "fim" guardam data + hora, o que facilita verificar
-- conflito de horário e montar o calendário:
--   conflito = existe agendamento não cancelado com inicio < novo_fim AND fim > novo_inicio

CREATE TABLE agendamentos (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id   INT UNSIGNED NOT NULL,
    servico_id   INT UNSIGNED NOT NULL,
    usuario_id   INT UNSIGNED NULL,
    inicio       DATETIME NOT NULL,
    fim          DATETIME NOT NULL,
    status       ENUM('agendado', 'concluido', 'cancelado') NOT NULL DEFAULT 'agendado',
    observacoes  TEXT NULL,
    criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_agendamentos_inicio (inicio),
    KEY idx_agendamentos_cliente (cliente_id),
    CONSTRAINT fk_agendamentos_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamentos_servico
        FOREIGN KEY (servico_id) REFERENCES servicos (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamentos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT ck_agendamentos_periodo CHECK (fim > inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
