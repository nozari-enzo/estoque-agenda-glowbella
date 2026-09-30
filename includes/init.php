<?php
/**
 * Arquivo de inicialização. TODA página em public/ deve começar incluindo ele:
 *
 *   require __DIR__ . '/../includes/init.php';      // páginas em public/
 *   require __DIR__ . '/../../includes/init.php';   // páginas em public/modulo/
 *
 * Ele carrega a configuração, inicia a sessão, conecta ao banco ($pdo)
 * e disponibiliza as funções de includes/funcoes.php.
 */

$arquivoConfig = __DIR__ . '/../config/config.php';

if (!file_exists($arquivoConfig)) {
    http_response_code(500);
    exit('Arquivo config/config.php não encontrado. Copie config/config.example.php para config/config.php e ajuste os dados do banco.');
}

require $arquivoConfig;

// Exibição de erros conforme o ambiente
ini_set('display_errors', APP_DEBUG ? '1' : '0');
error_reporting(E_ALL);

date_default_timezone_set(APP_TIMEZONE);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/funcoes.php';
require __DIR__ . '/conexao.php';
