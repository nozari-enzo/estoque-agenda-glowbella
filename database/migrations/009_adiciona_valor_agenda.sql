-- Adiciona a coluna para o valor do procedimento no agendamento
ALTER TABLE agenda ADD COLUMN vl_procedimento DECIMAL(10,2) DEFAULT NULL AFTER ds_procedimento;