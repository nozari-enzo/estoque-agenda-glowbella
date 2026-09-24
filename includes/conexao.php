<?php
/**
 * Conexão com o MySQL usando PDO. Cria a variável $pdo.
 *
 * Use SEMPRE prepared statements (nunca coloque variáveis direto no SQL):
 *
 *   $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
 *   $stmt->execute([$id]);
 *   $produto = $stmt->fetch();
 */

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    DB_HOST,
    DB_PORT,
    DB_NAME
);

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erros viram exceções
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // resultados como array associativo
        PDO::ATTR_EMULATE_PREPARES   => false,                  // prepared statements reais
    ]);
} catch (PDOException $erro) {
    http_response_code(500);

    if (APP_DEBUG) {
        exit('Erro ao conectar ao banco de dados: ' . e($erro->getMessage()));
    }

    exit('Erro ao conectar ao banco de dados. Tente novamente mais tarde.');
}
