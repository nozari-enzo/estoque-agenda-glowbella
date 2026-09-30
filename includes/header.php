<?php
/**
 * Cabeçalho padrão de todas as páginas (menu lateral + início do conteúdo).
 *
 * Antes de incluir, defina o título e qual item do menu fica destacado:
 *
 *   $titulo = 'Produtos';
 *   $menu = 'produtos';   // inicio, produtos, estoque, clientes ou agendamentos
 *   require __DIR__ . '/../../includes/header.php';
 */
$tituloPagina = isset($titulo) ? $titulo . ' | GlowBella' : 'GlowBella';
$menuAtivo = $menu ?? '';

// Itens do menu: chave => [texto, ícone do Bootstrap Icons, caminho]
$itensMenu = [
    'inicio'       => ['Início', 'bi-house-heart', ''],
    'agendamentos' => ['Agenda', 'bi-calendar-heart', 'agendamentos/'],
    'clientes'     => ['Clientes', 'bi-people', 'clientes/'],
    'produtos'     => ['Produtos', 'bi-gem', 'produtos/'],
    'estoque'      => ['Estoque', 'bi-box-seam', 'estoque/'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina) ?></title>

    <!-- Fontes: Playfair Display (títulos) e Inter (textos) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600&display=swap">

    <!-- Bootstrap 5 + ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Identidade visual da GlowBella -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<div class="app">

    <!-- Menu lateral: fixo no computador, gaveta no celular -->
    <aside class="menu-lateral offcanvas-lg offcanvas-start" tabindex="-1" id="menuLateral" aria-label="Menu principal">
        <div class="offcanvas-body">
            <div class="d-flex align-items-start justify-content-between">
                <a class="menu-marca" href="<?= url() ?>">
                    <i class="bi bi-gem"></i> GlowBella
                </a>
                <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"
                        data-bs-target="#menuLateral" aria-label="Fechar menu"></button>
            </div>

            <nav class="nav flex-column">
                <?php foreach ($itensMenu as $chave => [$texto, $icone, $caminho]): ?>
                    <a class="nav-link <?= $chave === $menuAtivo ? 'active' : '' ?>"
                       href="<?= url($caminho) ?>"
                       <?= $chave === $menuAtivo ? 'aria-current="page"' : '' ?>>
                        <i class="bi <?= $icone ?>"></i> <?= $texto ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="menu-rodape">
                <!-- Nome do usuário logado e botão "Sair" entram aqui na US13 (Login) -->
                Estoque &amp; Agenda
            </div>
        </div>
    </aside>

    <div class="app-conteudo">

        <!-- Barra superior: só aparece no celular -->
        <header class="barra-topo d-lg-none">
            <button class="btn-menu" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
                    aria-controls="menuLateral" aria-label="Abrir menu">
                <i class="bi bi-list"></i>
            </button>
            <a class="menu-marca" href="<?= url() ?>"><i class="bi bi-gem"></i> GlowBella</a>
        </header>

        <main class="app-principal">
            <?php foreach (pegar_flash() as $aviso): ?>
                <div class="alert alert-<?= e($aviso['tipo']) ?> alert-dismissible fade show" role="alert">
                    <?= e($aviso['mensagem']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endforeach; ?>
