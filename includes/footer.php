        </main>

        <footer class="app-rodape">
            GlowBella &copy; <?= date('Y') ?>
            <?php if (APP_DEBUG): ?>
                &middot; <a href="<?= url('componentes.php') ?>">Guia de componentes</a>
                &middot; <a href="<?= url('teste-conexao.php') ?>">Teste de conexão</a>
            <?php endif; ?>
        </footer>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
