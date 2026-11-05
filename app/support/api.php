<?php
/**
 * Funções comuns para os arquivos de API (public/api/*.php).
 *
 * Todo arquivo de API começa assim:
 *
 *   require __DIR__ . '/../../app/bootstrap/init.php';
 *   require __DIR__ . '/../../app/support/api.php';
 *
 * As respostas são sempre JSON. Erros seguem o formato:
 *   { "erro": "Mensagem para a pessoa", "campos": { "nm_produto": "Informe o nome." } }
 */

require_once __DIR__ . '/../services/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Qualquer erro não tratado vira uma resposta JSON 500, em vez de HTML de erro do PHP.
set_exception_handler(function (Throwable $erro): void {
    $resposta = ['erro' => 'Erro interno no servidor.'];

    if (APP_DEBUG) {
        $resposta['detalhe'] = $erro->getMessage();
    }

    responder($resposta, 500);
});

/**
 * Envia a resposta em JSON e encerra o script.
 *
 *   responder($produtos);                     // 200 OK
 *   responder(['cd_produto' => 10], 201);     // 201 Created
 */
function responder(mixed $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Responde com um erro.
 *
 *   responder_erro('Produto não encontrado.', 404);
 *   responder_erro('Confira os campos.', 422, $erros);
 */
function responder_erro(string $mensagem, int $status, array $campos = []): void
{
    $resposta = ['erro' => $mensagem];

    if ($campos) {
        $resposta['campos'] = $campos;
    }

    responder($resposta, $status);
}

/** Método HTTP da requisição: GET, POST, PUT ou DELETE. */
function metodo(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/**
 * Lê o corpo da requisição em JSON e devolve como array.
 * Responde 400 se o corpo não for um JSON de objeto válido.
 */
function ler_json(): array
{
    $corpo = file_get_contents('php://input');

    if ($corpo === '' || $corpo === false) {
        return [];
    }

    $dados = json_decode($corpo, true);

    // Precisa ser um objeto ({...}), não uma lista ([...]).
    if (!is_array($dados) || ($dados !== [] && array_keys($dados) === range(0, count($dados) - 1))) {
        responder_erro('O corpo da requisição precisa ser um objeto JSON.', 400);
    }

    return $dados;
}

/**
 * Lê o "id" da URL (ex.: api/produtos.php?id=3).
 * Devolve null se não veio; responde 400 se veio inválido.
 */
function id_da_url(): ?int
{
    if (!isset($_GET['id']) || $_GET['id'] === '') {
        return null;
    }

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($id === false) {
        responder_erro('O id informado é inválido.', 400);
    }

    return $id;
}

/** Exige um id na URL (para PUT e DELETE). */
function exigir_id(): int
{
    return id_da_url() ?? responder_erro('Informe o id na URL (ex.: ?id=3).', 400);
}

/**
 * Exige que a pessoa esteja logada. Responde 401 se não estiver.
 * Devolve os dados do usuário da sessão (cd_usuario, nm_usuario).
 */
function exigir_login(): array
{
    return usuario_logado() ?? responder_erro('Faça login para continuar.', 401);
}

/**
 * Protege POST, PUT e DELETE contra CSRF.
 * O JavaScript envia o token no cabeçalho X-CSRF-Token
 * (a função glowApi() de public/assets/js/app.js já faz isso).
 */
function exigir_csrf(): void
{
    if (in_array(metodo(), ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }

    $enviado = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        responder_erro('Sessão expirada. Recarregue a página e tente novamente.', 419);
    }
}

/** Responde 405 informando quais métodos o recurso aceita. */
function metodo_nao_permitido(array $permitidos): void
{
    header('Allow: ' . implode(', ', $permitidos));
    responder_erro('Método não permitido.', 405);
}

/**
 * Converte erros de restrição do banco em mensagens amigáveis.
 * Use em volta de INSERT/UPDATE que podem violar UNIQUE ou chave estrangeira.
 */
function responder_erro_banco(PDOException $erro): void
{
    $codigo = (int) ($erro->errorInfo[1] ?? 0);

    match ($codigo) {
        1062    => responder_erro('Já existe um registro com esses dados.', 409),
        1451    => responder_erro('Não é possível remover: existem registros ligados a este.', 409),
        1452    => responder_erro('Um dos registros relacionados não existe.', 422),
        4025    => responder_erro('Os dados não respeitam as regras do banco.', 422),
        default => throw $erro,
    };
}

/* ---------- Validação ---------- */

/**
 * Texto obrigatório ou opcional, com tamanho máximo.
 * Devolve o texto sem espaços nas pontas, ou null se vazio e opcional.
 */
function validar_texto(array $dados, string $campo, int $maximo, bool $obrigatorio, array &$erros, string $rotulo): ?string
{
    $valor = $dados[$campo] ?? null;

    if (is_string($valor)) {
        $valor = trim($valor);
    }

    if ($valor === null || $valor === '') {
        if ($obrigatorio) {
            $erros[$campo] = "Informe $rotulo.";
        }
        return null;
    }

    if (!is_string($valor)) {
        $erros[$campo] = ucfirst($rotulo) . ' precisa ser um texto.';
        return null;
    }

    if (mb_strlen($valor) > $maximo) {
        $erros[$campo] = ucfirst($rotulo) . " pode ter no máximo $maximo caracteres.";
        return null;
    }

    return $valor;
}

/** Número inteiro maior ou igual a $minimo. */
function validar_inteiro(array $dados, string $campo, int $minimo, bool $obrigatorio, array &$erros, string $rotulo): ?int
{
    $valor = $dados[$campo] ?? null;

    if ($valor === null || $valor === '') {
        if ($obrigatorio) {
            $erros[$campo] = "Informe $rotulo.";
        }
        return null;
    }

    $inteiro = filter_var($valor, FILTER_VALIDATE_INT);

    if ($inteiro === false || is_bool($valor) || $inteiro < $minimo) {
        $erros[$campo] = ucfirst($rotulo) . " precisa ser um número inteiro a partir de $minimo.";
        return null;
    }

    return $inteiro;
}

/** Valor em dinheiro (aceita 59.9 ou "59,90"), maior ou igual a zero. */
function validar_valor(array $dados, string $campo, bool $obrigatorio, array &$erros, string $rotulo): ?string
{
    $valor = $dados[$campo] ?? null;

    if ($valor === null || $valor === '') {
        if ($obrigatorio) {
            $erros[$campo] = "Informe $rotulo.";
        }
        return null;
    }

    if (is_string($valor)) {
        $valor = str_replace(',', '.', trim($valor));
    }

    if (is_bool($valor) || !is_numeric($valor) || (float) $valor < 0) {
        $erros[$campo] = ucfirst($rotulo) . ' precisa ser um valor maior ou igual a zero.';
        return null;
    }

    return number_format((float) $valor, 2, '.', '');
}

/** Data no formato AAAA-MM-DD. */
function validar_data(array $dados, string $campo, bool $obrigatorio, array &$erros, string $rotulo): ?string
{
    $valor = $dados[$campo] ?? null;

    if ($valor === null || $valor === '') {
        if ($obrigatorio) {
            $erros[$campo] = "Informe $rotulo.";
        }
        return null;
    }

    $data = is_string($valor) ? DateTime::createFromFormat('!Y-m-d', $valor) : false;

    if (!$data || $data->format('Y-m-d') !== $valor) {
        $erros[$campo] = ucfirst($rotulo) . ' precisa ser uma data válida (AAAA-MM-DD).';
        return null;
    }

    return $valor;
}

/** Um valor de uma lista fixa (ex.: fg_finalidade). */
function validar_opcao(array $dados, string $campo, array $opcoes, ?string $padrao, array &$erros, string $rotulo): ?string
{
    $valor = $dados[$campo] ?? null;

    if ($valor === null || $valor === '') {
        if ($padrao === null) {
            $erros[$campo] = "Informe $rotulo.";
        }
        return $padrao;
    }

    // Aceita 1 e "1" do mesmo jeito
    if (is_int($valor)) {
        $valor = (string) $valor;
    }

    if (!in_array($valor, $opcoes, true)) {
        $erros[$campo] = ucfirst($rotulo) . ' precisa ser: ' . implode(', ', $opcoes) . '.';
        return null;
    }

    return $valor;
}
