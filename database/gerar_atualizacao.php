<?php
/**
 * Junta as migrations num arquivo só (atualizacao-hospedagem.sql), para importar no
 * phpMyAdmin da hospedagem, onde não dá para rodar o migrar.php. Guia: docs/hospedagem.md
 *
 * Uso (no terminal, dentro da pasta do projeto):
 *
 *   Primeira instalação (banco vazio, todas as migrations):
 *     C:\xampp\php\php.exe database\gerar_atualizacao.php
 *
 *   Atualização (só as migrations depois da última que a hospedagem já tem):
 *     C:\xampp\php\php.exe database\gerar_atualizacao.php --depois-de 009
 *
 *   --seed  inclui os dados de exemplo (SÓ para hospedagem de teste, nunca com dados reais)
 *
 * O arquivo também registra cada migration na tabela "migracoes" da hospedagem.
 * Para ver qual foi a última lá: SELECT * FROM migracoes ORDER BY arquivo;
 */

if (PHP_SAPI !== 'cli') {
    exit('Este script só pode ser executado pelo terminal.');
}

$opcoes = array_slice($argv, 1);
$comSeed = in_array('--seed', $opcoes, true);
$posicao = array_search('--depois-de', $opcoes, true);
$depoisDe = $posicao !== false ? ($opcoes[$posicao + 1] ?? '') : '000';

if (!ctype_digit($depoisDe) || strlen($depoisDe) !== 3) {
    fwrite(STDERR, "Informe o número da última migration da hospedagem com 3 dígitos. Ex.: --depois-de 009\n");
    exit(1);
}

$arquivos = glob(__DIR__ . '/migrations/*.sql');
sort($arquivos);

// 000 cria o banco (na hospedagem o banco é criado pelo painel) e as já aplicadas ficam de fora
$pendentes = array_values(array_filter($arquivos, fn ($caminho) => substr(basename($caminho), 0, 3) > $depoisDe));

if (!$pendentes) {
    echo "Nenhuma migration depois da $depoisDe: a hospedagem já está com o banco atualizado.\n";
    exit(0);
}

$sql = "-- Atualização do banco da GlowBella para a hospedagem\n"
    . '-- Gerado em ' . date('d/m/Y H:i') . ' com: migrations ' . substr(basename($pendentes[0]), 0, 3)
    . ' a ' . substr(basename(end($pendentes)), 0, 3) . ($comSeed ? ' + dados de exemplo' : '') . "\n"
    . "-- Importe UMA vez no phpMyAdmin da hospedagem (banco selecionado > Importar).\n\n"
    . "SET NAMES utf8mb4;\n\n"
    . "CREATE TABLE IF NOT EXISTS migracoes (\n"
    . "    arquivo      VARCHAR(255) NOT NULL PRIMARY KEY,\n"
    . "    executado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP\n"
    . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n";

foreach ($pendentes as $caminho) {
    $arquivo = basename($caminho);
    $sql .= "\n-- ==================== $arquivo ====================\n"
        . rtrim(file_get_contents($caminho)) . "\n"
        . "INSERT INTO migracoes (arquivo) VALUES ('$arquivo');\n";
}

if ($comSeed) {
    $sql .= "\n-- ==================== seed.sql (dados de exemplo) ====================\n"
        . rtrim(file_get_contents(__DIR__ . '/seed.sql')) . "\n";
}

file_put_contents(__DIR__ . '/../atualizacao-hospedagem.sql', $sql);

echo 'Arquivo criado: atualizacao-hospedagem.sql (na pasta do projeto), com ' . count($pendentes) . " migration(s):\n";
foreach ($pendentes as $caminho) {
    echo '  ' . basename($caminho) . "\n";
}
echo "Faça backup do banco da hospedagem antes de importar.\n";
