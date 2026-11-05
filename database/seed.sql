-- Dados de exemplo para desenvolvimento e testes.
-- Rode DEPOIS das migrations, com o banco vazio.
-- Todos os nomes, telefones e e-mails são fictícios.

-- Usuário inicial
--   e-mail: admin@glowbella.com
--   senha:  glowbella123   (troque em produção!)
INSERT INTO admin (cd_usuario, nm_usuario, ds_email, ds_senha) VALUES
(1, 'Administradora', 'admin@glowbella.com', '$2y$12$qasf95HMVQA51M85lzI6FeoC751RATJRvv9yGYxUR4.b.J2kADp5C');

INSERT INTO categoria (cd_categoria, nm_categoria) VALUES
(1, 'Brincos'),
(2, 'Piercings'),
(3, 'Acessórios');

INSERT INTO produto (cd_produto, nm_produto, fg_finalidade, ds_lote, vl_compra, vl_produto, qtd_produto, qtd_estoque_minimo, dt_validade, cd_categoria) VALUES
(1, 'Argola lisa 12mm',           'venda',        'L2026-01', 22.00,  59.90, 15,  5, NULL,                          1),
(2, 'Brinco ponto de luz',        'venda',        'L2026-01', 14.50,  39.90, 22,  8, NULL,                          1),
(3, 'Ear cuff trançado',          'venda',        'L2026-02', 10.00,  29.90,  3,  5, NULL,                          1),
(4, 'Argola cravejada 8mm',       'venda',        'L2026-02', 180.00, 349.00, 4,  2, NULL,                          1),
(5, 'Piercing labret cristal',    'procedimento', 'T-0931',   25.00,  69.90, 12,  5, CURDATE() + INTERVAL 2 YEAR,  2),
(6, 'Piercing nostril curvo',     'procedimento', 'T-0932',   18.00,  49.90,  2,  5, CURDATE() + INTERVAL 2 YEAR,  2),
(7, 'Piercing argola segmentada', 'procedimento', 'A-1120',   16.00,  45.00, 10,  4, CURDATE() + INTERVAL 2 YEAR,  2),
(8, 'Solução de limpeza 30ml',    'venda',        'S-0450',    9.00,  24.90, 30, 10, CURDATE() + INTERVAL 20 DAY,  3);

-- Entradas iniciais que explicam as quantidades acima
INSERT INTO movimentacao (cd_produto, cd_usuario, fg_tipo, qtd_movimentada, ds_motivo) VALUES
(1, 1, 'entrada', 15, 'Estoque inicial'),
(2, 1, 'entrada', 22, 'Estoque inicial'),
(3, 1, 'entrada',  3, 'Estoque inicial'),
(4, 1, 'entrada',  4, 'Estoque inicial'),
(5, 1, 'entrada', 12, 'Estoque inicial'),
(6, 1, 'entrada',  2, 'Estoque inicial'),
(7, 1, 'entrada', 10, 'Estoque inicial'),
(8, 1, 'entrada', 30, 'Estoque inicial');

INSERT INTO cliente (cd_cliente, nm_cliente, dt_nascimento, nu_telefone) VALUES
(1, 'Ana Souza',      '1998-03-14', '(11) 90000-0001'),
(2, 'Beatriz Lima',   '2001-07-22', '(11) 90000-0002'),
(3, 'Carla Mendes',   NULL,         '(11) 90000-0003'),
(4, 'Daniela Rocha',  '1995-11-02', '(11) 90000-0004'),
(5, 'Eduarda Castro', '2003-01-30', '(11) 90000-0005');

-- Agendamentos relativos à data de hoje, para sempre haver dados atuais
INSERT INTO agenda (cd_cliente, dt_agendamento, dt_fim, ds_procedimento, fg_status) VALUES
(1, CONCAT(CURDATE(), ' 10:00:00'), CONCAT(CURDATE(), ' 10:30:00'), 'Aplicação de piercing - hélix', 'agendado'),
(2, CONCAT(CURDATE(), ' 14:00:00'), CONCAT(CURDATE(), ' 14:15:00'), 'Troca de joia',                 'agendado'),
(3, CONCAT(CURDATE() + INTERVAL 1 DAY, ' 11:00:00'), CONCAT(CURDATE() + INTERVAL 1 DAY, ' 11:30:00'), 'Aplicação de piercing - nostril', 'agendado'),
(4, CONCAT(CURDATE() + INTERVAL 2 DAY, ' 16:00:00'), CONCAT(CURDATE() + INTERVAL 2 DAY, ' 16:20:00'), 'Avaliação e limpeza',           'agendado'),
(5, CONCAT(CURDATE() - INTERVAL 3 DAY, ' 15:00:00'), CONCAT(CURDATE() - INTERVAL 3 DAY, ' 15:30:00'), 'Aplicação de piercing - lóbulo', 'concluido');

-- Ciclos de esterilização e os produtos de cada ciclo
INSERT INTO esterilizacao (cd_esterilizacao, nm_metodo, dt_esterilizacao, dt_validade_esterilizacao, fg_indicador_quimico, fg_indicador_biologico) VALUES
(1, 'Autoclave', CURDATE() - INTERVAL 35 DAY, CURDATE() - INTERVAL 5 DAY,  'aprovado', 'aprovado'),
(2, 'Autoclave', CURDATE() - INTERVAL 7 DAY,  CURDATE() + INTERVAL 23 DAY, 'aprovado', 'aprovado'),
(3, 'Autoclave', CURDATE(),                   CURDATE() + INTERVAL 30 DAY, 'aprovado', 'pendente');

INSERT INTO esterilizacao_produto (cd_esterilizacao, cd_produto, qtd_produto) VALUES
(1, 7, 2),
(2, 5, 4),
(2, 6, 1),
(3, 5, 3),
(3, 7, 2);
