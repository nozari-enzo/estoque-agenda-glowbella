<?php
/**
 * Funções úteis compartilhadas por todo o sistema.
 */

/**
 * Escapa um texto para exibir no HTML com segurança (evita XSS).
 * Use SEMPRE que for mostrar dados vindos do usuário ou do banco:
 *
 *   <?= e($produto['nome']) ?>
 */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Monta um link a partir da pasta public/.
 *
 *   url('produtos/')            → http://localhost/estoque-agenda-glowbella/public/produtos/
 *   url('assets/css/style.css') → .../public/assets/css/style.css
 */
function url(string $caminho = ''): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}

/**
 * Redireciona para outra página do sistema e encerra o script.
 *
 *   redirecionar('produtos/');
 */
function redirecionar(string $caminho): void
{
    header('Location: ' . url($caminho));
    exit;
}

/**
 * Guarda uma mensagem para mostrar na próxima página (ex.: depois de salvar).
 * Tipos do Bootstrap: success, danger, warning, info.
 *
 *   flash('Produto cadastrado com sucesso!');
 *   flash('Preencha todos os campos.', 'danger');
 */
function flash(string $mensagem, string $tipo = 'success'): void
{
    $_SESSION['flash'][] = ['mensagem' => $mensagem, 'tipo' => $tipo];
}

/**
 * Retorna as mensagens guardadas e limpa a lista (usado no header.php).
 */
function pegar_flash(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensagens;
}

/**
 * Proteção CSRF para formulários.
 *
 * Dentro de todo <form method="post">:
 *   <?= csrf_campo() ?>
 *
 * No início do processamento do POST:
 *   csrf_validar();
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    $enviado = $_POST['csrf_token'] ?? '';

    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(419);
        exit('Sessão expirada ou formulário inválido. Volte e tente novamente.');
    }
}
