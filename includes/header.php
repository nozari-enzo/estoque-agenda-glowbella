<?php
/**
 * Cabeçalho padrão de todas as páginas.
 *
 * Antes de incluir, defina o título da página:
 *
 *   $titulo = 'Produtos';
 *   require __DIR__ . '/../../includes/header.php';
 */
$titulo = isset($titulo) ? $titulo . ' | GlowBella' : 'GlowBella';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>

    <!-- Bootstrap 5 + ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Estilos próprios do projeto -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-glow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url() ?>">
            <i class="bi bi-gem"></i> GlowBella
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url() ?>">Início</a></li>
                <!-- Os links de Produtos, Clientes, Estoque e Agenda entram aqui conforme as features ficarem prontas -->
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4 flex-grow-1">
    <?php foreach (pegar_flash() as $aviso): ?>
        <div class="alert alert-<?= e($aviso['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= e($aviso['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endforeach; ?>
