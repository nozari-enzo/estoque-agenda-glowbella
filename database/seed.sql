-- Dados de exemplo para desenvolvimento e testes.
-- Rode DEPOIS das migrations, com o banco vazio.
-- Todos os nomes, telefones e e-mails são fictícios.

-- Usuário inicial
--   e-mail: admin@glowbella.com
--   senha:  glowbella123   (troque em produção!)
INSERT INTO usuarios (id, nome, email, senha_hash, perfil) VALUES
(1, 'Administradora', 'admin@glowbella.com', '$2y$12$qasf95HMVQA51M85lzI6FeoC751RATJRvv9yGYxUR4.b.J2kADp5C', 'admin');

INSERT INTO categorias (id, nome, descricao) VALUES
(1, 'Brincos',    'Brincos de pressão, argolas e ear cuffs'),
(2, 'Piercings',  'Joias para aplicação de piercing'),
(3, 'Acessórios', 'Tarraxas, produtos de limpeza e cuidados');

INSERT INTO produtos (id, categoria_id, codigo, nome, material, preco, quantidade, estoque_minimo) VALUES
(1, 1, 'BR-001', 'Argola lisa 12mm',            'Prata 925',       59.90, 15, 5),
(2, 1, 'BR-002', 'Brinco ponto de luz',         'Prata 925',       39.90, 22, 8),
(3, 1, 'BR-003', 'Ear cuff trançado',           'Aço cirúrgico',   29.90,  3, 5),
(4, 1, 'BR-004', 'Argola cravejada 8mm',        'Ouro 18k',       349.00,  4, 2),
(5, 2, 'PC-001', 'Piercing labret cristal',     'Titânio',         69.90, 12, 5),
(6, 2, 'PC-002', 'Piercing nostril curvo',      'Titânio',         49.90,  2, 5),
(7, 2, 'PC-003', 'Piercing argola segmentada',  'Aço cirúrgico',   45.00, 10, 4),
(8, 3, 'AC-001', 'Solução de limpeza 30ml',     NULL,              24.90, 30, 10);

-- Entradas iniciais que explicam as quantidades acima
INSERT INTO movimentacoes_estoque (produto_id, usuario_id, tipo, motivo, quantidade, observacao) VALUES
(1, 1, 'entrada', 'compra', 15, 'Estoque inicial'),
(2, 1, 'entrada', 'compra', 22, 'Estoque inicial'),
(3, 1, 'entrada', 'compra',  3, 'Estoque inicial'),
(4, 1, 'entrada', 'compra',  4, 'Estoque inicial'),
(5, 1, 'entrada', 'compra', 12, 'Estoque inicial'),
(6, 1, 'entrada', 'compra',  2, 'Estoque inicial'),
(7, 1, 'entrada', 'compra', 10, 'Estoque inicial'),
(8, 1, 'entrada', 'compra', 30, 'Estoque inicial');

INSERT INTO clientes (id, nome, telefone, email, data_nascimento, observacoes) VALUES
(1, 'Ana Souza',      '(11) 90000-0001', 'ana.souza@exemplo.com',   '1998-03-14', NULL),
(2, 'Beatriz Lima',   '(11) 90000-0002', NULL,                      '2001-07-22', 'Alergia a níquel'),
(3, 'Carla Mendes',   '(11) 90000-0003', 'carla.m@exemplo.com',     NULL,         NULL),
(4, 'Daniela Rocha',  '(11) 90000-0004', NULL,                      '1995-11-02', 'Prefere atendimento à tarde'),
(5, 'Eduarda Castro', '(11) 90000-0005', 'duda.castro@exemplo.com', '2003-01-30', NULL);

INSERT INTO servicos (id, nome, descricao, duracao_minutos, preco) VALUES
(1, 'Aplicação de piercing - orelha', 'Lóbulo, hélix, tragus etc. Joia vendida à parte', 30, 60.00),
(2, 'Aplicação de piercing - nariz',  'Nostril ou septo. Joia vendida à parte',          30, 70.00),
(3, 'Troca de joia',                  'Troca de joia em piercing já cicatrizado',        15, 20.00),
(4, 'Avaliação e limpeza',            'Avaliação de cicatrização e orientação',          20,  0.00);

-- Agendamentos relativos à data de hoje, para sempre haver dados atuais
INSERT INTO agendamentos (cliente_id, servico_id, usuario_id, inicio, fim, status, observacoes) VALUES
(1, 1, 1, CONCAT(CURDATE(), ' 10:00:00'), CONCAT(CURDATE(), ' 10:30:00'), 'agendado', 'Hélix lado esquerdo'),
(2, 3, 1, CONCAT(CURDATE(), ' 14:00:00'), CONCAT(CURDATE(), ' 14:15:00'), 'agendado', NULL),
(3, 2, 1, CONCAT(CURDATE() + INTERVAL 1 DAY, ' 11:00:00'), CONCAT(CURDATE() + INTERVAL 1 DAY, ' 11:30:00'), 'agendado', NULL),
(4, 4, 1, CONCAT(CURDATE() + INTERVAL 2 DAY, ' 16:00:00'), CONCAT(CURDATE() + INTERVAL 2 DAY, ' 16:20:00'), 'agendado', NULL),
(5, 1, 1, CONCAT(CURDATE() - INTERVAL 3 DAY, ' 15:00:00'), CONCAT(CURDATE() - INTERVAL 3 DAY, ' 15:30:00'), 'concluido', 'Lóbulo duplo');
