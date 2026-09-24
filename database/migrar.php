<?php
/**
 * Roda as migrations pendentes (arquivos .sql de database/migrations/) em ordem.
 * Guarda na tabela "migracoes" quais arquivos já foram executados,
 * então pode ser rodado quantas vezes quiser: só executa o que é novo.
 *
 * Uso (no terminal, dentro da pasta do projeto):
 *
 *   Windows/XAMPP:  C:\xampp\php\php.exe database\migrar.php
 *   Linux/Mac:      php database/migrar.php
 *
 * Opções:
 *   --seed    depois das migrations, importa database/seed.sql (só se o banco estiver vazio)
 *   --reset   APAGA o banco inteiro e recria do zero (cuidado!)
 */

if (PHP_SAPI !== 'cli') {
    exit('Este script só pode ser executado pelo terminal.');
}

$arquivoConfig = __DIR__ . '/../config/config.php';
if (!file_exists($arquivoConfig)) {
    fwrite(STDERR, "Arquivo config/config.php não encontrado. Copie config/config.example.php para config/config.php.\n");
    exit(1);
}
require $arquivoConfig;

$opcoes = array_slice($argv, 1);
$rodarSeed = in_array('--seed', $opcoes, true);
$resetar = in_array('--reset', $opcoes, true);

try {
    // Conecta sem escolher o banco, para poder criá-lo se não existir
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT),
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $erro) {
    fwrite(STDERR, 'Erro ao conectar ao MySQL: ' . $erro->getMessage() . "\nO MySQL está ligado no XAMPP?\n");
    exit(1);
}

$banco = '`' . str_replace('`', '``', DB_NAME) . '`';

if ($resetar) {
    echo 'Tem certeza que quer APAGAR o banco ' . DB_NAME . '? Digite "sim": ';
    if (trim((string) fgets(STDIN)) !== 'sim') {
        exit("Cancelado.\n");
    }
    $pdo->exec("DROP DATABASE IF EXISTS $banco");
    echo "Banco apagado.\n";
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS $banco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE $banco");
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migracoes (
        arquivo      VARCHAR(255) NOT NULL PRIMARY KEY,
        executado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$executadas = $pdo->query('SELECT arquivo FROM migracoes')->fetchAll(PDO::FETCH_COLUMN);

$arquivos = glob(__DIR__ . '/migrations/*.sql');
sort($arquivos);

$novas = 0;
foreach ($arquivos as $caminho) {
    $arquivo = basename($caminho);

    // 000 só cria o banco, o que este script já faz (e respeitando o DB_NAME do config)
    if (str_starts_with($arquivo, '000_') || in_array($arquivo, $executadas, true)) {
        continue;
    }

    echo "Executando $arquivo... ";
    try {
        $pdo->exec(file_get_contents($caminho));
        $stmt = $pdo->prepare('INSERT INTO migracoes (arquivo) VALUES (?)');
        $stmt->execute([$arquivo]);
        echo "ok\n";
        $novas++;
    } catch (PDOException $erro) {
        echo "ERRO\n";
        fwrite(STDERR, $erro->getMessage() . "\n");
        exit(1);
    }
}

echo $novas ? "$novas migration(s) executada(s).\n" : "Nenhuma migration pendente. Banco atualizado.\n";

if ($rodarSeed) {
    $temDados = (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    if ($temDados) {
        echo "Seed ignorado: o banco já tem dados. Use --reset --seed para recriar do zero.\n";
    } else {
        $pdo->exec(file_get_contents(__DIR__ . '/seed.sql'));
        echo "Dados de exemplo importados. Login: admin@glowbella.com / glowbella123\n";
    }
}
