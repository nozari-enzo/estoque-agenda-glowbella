<?php
require __DIR__ . '/../app/bootstrap/init.php';

/**
 * Conta os registros de uma tabela para mostrar nos atalhos.
 * Se a tabela ainda não existir (migrations não rodadas), retorna null
 * e a Home continua funcionando, só sem o número.
 */
function contar(PDO $pdo, string $sql): ?int
{
    try {
        $consulta = $pdo->prepare($sql);
        $consulta->execute();

        return (int) $consulta->fetchColumn();
    } catch (PDOException $erro) {
        return null;
    }
}

/** Busca o total e uma lista curta; null indica que o banco/tabela não está disponível. */
function listarProdutosAbaixoMinimo(PDO $pdo): ?array
{
    try {
        $consultaTotal = $pdo->prepare(
            'SELECT COUNT(*)
             FROM produto
             WHERE fg_status = 1 AND qtd_produto < qtd_estoque_minimo'
        );
        $consultaTotal->execute();

        $consultaItens = $pdo->prepare(
            'SELECT nm_produto AS nome, qtd_produto AS quantidade, qtd_estoque_minimo AS estoque_minimo
             FROM produto
             WHERE fg_status = 1 AND qtd_produto < qtd_estoque_minimo
             ORDER BY (qtd_estoque_minimo - qtd_produto) DESC, nm_produto
             LIMIT 5'
        );
        $consultaItens->execute();

        return [
            'total' => (int) $consultaTotal->fetchColumn(),
            'itens' => $consultaItens->fetchAll(PDO::FETCH_ASSOC),
        ];
    } catch (PDOException $erro) {
        return null;
    }
}

/////////////////////////////////////////

$produtosAbaixoMinimo = listarProdutosAbaixoMinimo($pdo);

$titulo = 'Início';
$menu = 'inicio';
require __DIR__ . '/../app/views/partials/estilologin.php';
?>


<div class="card" style="min-height: 700px; display: flex; justify-content: center; align-items: center; border:none">
<div class="card p-4 d-flex justify-content-center" style="min-height: 400px;">
  <div class="card-body d-flex flex-column justify-content-center gap-4">
    <h2 class="h5 text-center">Acesse sua conta do Sistema</h2>
    <form class="row gap-3" action="valida_login.php" method="post" autocomplete="off" name="ds_email" novalidate>
      <div>
        <label class="form-label" for="email">Endereço de email</label>
        <input class="form-control" id="email" type="email" placeholder="seu@email.com" autocomplete="off" name="ds_email">
      </div>
      <div>
        <div class="input-group">
          <input class="form-control" id="password" type="password" placeholder="Sua senha" autocomplete="off" name="ds_senha">
          <span class="input-group-text">
            <button class="bg-transparent border-0 p-0 link-secondary" type="button" data-coreui-toggle="tooltip" aria-label="Show password" data-coreui-original-title="Show password">
              <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:16px; height:16px;">
                <path class="ci-primary" fill="var(--ci-primary-color, currentcolor)" d="M256 144.927a103.309 103.309 0 1 0 103.309 103.309A103.426 103.426 0 0 0 256 144.927m0 174.618a71.309 71.309 0 1 1 71.309-71.309A71.39 71.39 0 0 1 256 319.545"></path>
                <path class="ci-primary" fill="var(--ci-primary-color, currentcolor)" d="m397.222 131.1-.218-.223c-77.75-77.749-204.258-77.749-282.008 0L16 233.79v28.893l98.778 102.689.218.222a199.41 199.41 0 0 0 282.008 0l99-102.911V233.79ZM464 249.79l-89.732 93.285a167.41 167.41 0 0 1-236.536 0L48 249.79v-3.107l89.729-93.283c65.247-65.13 171.3-65.13 236.542 0L464 246.683Z"></path>
                <path class="ci-primary" fill="var(--ci-primary-color, currentcolor)" d="M240 232h32v32h-32z"></path>
              </svg>
            </button>
          </span>
        </div>
      </div>
      <div>
        <button class="btn btn-primary w-100" type="submit">Entrar</button>
      </div>
    </form>

    <?php
      if (isset($_SESSION['erro_login'])) {
        echo "<p class='text-danger text-center mb-0'>" . $_SESSION['erro_login'] . "</p>";
        unset($_SESSION['erro_login']);
      }
    ?>
  </div>
</div>
</div>


<?php require __DIR__ . '/../app/views/partials/footer.php'; ?>
