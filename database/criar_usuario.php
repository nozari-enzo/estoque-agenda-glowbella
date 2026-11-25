<?php
/**
 * Cria um usuário do sistema, ou troca a senha de um que já existe (mesmo e-mail).
 * A senha é guardada com password_hash(), nunca em texto puro.
 *
 * Uso (no terminal, dentro da pasta do projeto):
 *
 *   Windows/XAMPP:  C:\xampp\php\php.exe database\criar_usuario.php "Nome" email@exemplo.com
 *   Linux/Mac:      php database/criar_usuario.php "Nome" email@exemplo.com
 *
 * A senha é pedida no terminal (não fica salva no histórico de comandos).
 *
 * Para a HOSPEDAGEM (sem terminal), use --sql: em vez de gravar no seu banco, ele cria o
 * arquivo usuario-hospedagem.sql para importar no phpMyAdmin de lá (docs/hospedagem.md):
 *
 *   C:\xampp\php\php.exe database\criar_usuario.php "Nome" email@exemplo.com --sql
 */

if (PHP_SAPI !== 'cli') {
    exit('Este script só pode ser executado pelo terminal.');
}

$gerarSql = in_array('--sql', $argv, true);
$argumentos = array_values(array_diff(array_slice($argv, 1), ['--sql']));
[$nome, $email] = $argumentos + ['', ''];
$nome = trim((string) $nome);
$email = trim((string) $email);

if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php database/criar_usuario.php \"Nome\" email@exemplo.com [--sql]\n");
    exit(1);
}

echo 'Senha (mínimo 8 caracteres): ';
$senha = trim((string) fgets(STDIN));

if (mb_strlen($senha) < 8) {
    fwrite(STDERR, "A senha precisa ter pelo menos 8 caracteres.\n");
    exit(1);
}

$hash = password_hash($senha, PASSWORD_DEFAULT);

if ($gerarSql) {
    // Texto entre aspas simples no SQL: dobra as aspas e as barras
    $sql = fn (string $texto): string => "'" . str_replace(['\\', "'"], ['\\\\', "''"], $texto) . "'";
    $arquivoSaida = __DIR__ . '/../usuario-hospedagem.sql';

    file_put_contents($arquivoSaida, "-- Cria o usuário $email, ou troca a senha se ele já existir (e reativa).\n"
        . "-- Importe no phpMyAdmin da hospedagem e depois APAGUE este arquivo.\n"
        . "SET NAMES utf8mb4;\n"
        . "INSERT INTO admin (nm_usuario, ds_email, ds_senha, fg_status)\n"
        . 'VALUES (' . $sql($nome) . ', ' . $sql($email) . ', ' . $sql($hash) . ", 1)\n"
        . "ON DUPLICATE KEY UPDATE nm_usuario = VALUES(nm_usuario), ds_senha = VALUES(ds_senha), fg_status = 1;\n");

    echo "Arquivo criado: usuario-hospedagem.sql (na pasta do projeto).\n"
        . "Importe no phpMyAdmin da hospedagem e depois apague o arquivo.\n";
    exit(0);
}

$arquivoConfig = __DIR__ . '/../config/config.php';
if (!file_exists($arquivoConfig)) {
    fwrite(STDERR, "Arquivo config/config.php não encontrado. Copie config/config.example.php para config/config.php.\n");
    exit(1);
}
require $arquivoConfig;

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $erro) {
    fwrite(STDERR, 'Erro ao conectar ao MySQL: ' . $erro->getMessage() . "\nO MySQL está ligado? Já rodou database/migrar.php?\n");
    exit(1);
}

$stmt = $pdo->prepare('SELECT cd_usuario FROM admin WHERE ds_email = ?');
$stmt->execute([$email]);
$id = $stmt->fetchColumn();

if ($id) {
    $pdo->prepare('UPDATE admin SET nm_usuario = ?, ds_senha = ?, fg_status = 1 WHERE cd_usuario = ?')
        ->execute([$nome, $hash, $id]);
    echo "Senha de $email atualizada (usuário reativado, se estava desativado).\n";
} else {
    $pdo->prepare('INSERT INTO admin (nm_usuario, ds_email, ds_senha) VALUES (?, ?, ?)')
        ->execute([$nome, $email, $hash]);
    echo "Usuário $email criado. Já pode entrar em public/login.php.\n";
}
