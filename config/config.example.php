<?php
/**
 * MODELO de configuração.
 *
 * Copie este arquivo para "config.php" (na mesma pasta) e ajuste os valores
 * para a sua máquina. O config.php NÃO vai para o GitHub (está no .gitignore).
 */

// Banco de dados (padrão do XAMPP: usuário root sem senha)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'glowbella');
define('DB_USER', 'root');
define('DB_PASS', '');

// Endereço da pasta public/ no navegador (sem barra no final)
define('BASE_URL', 'http://localhost/estoque-agenda-glowbella/public');

// true = mostra erros na tela (desenvolvimento)
// false = esconde erros (produção / deploy)
define('APP_DEBUG', true);

// Fuso horário usado nas datas
define('APP_TIMEZONE', 'America/Sao_Paulo');
