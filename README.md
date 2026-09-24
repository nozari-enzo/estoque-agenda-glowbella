# estoque-agenda-glowbella
Sistema de gerenciamento para uma loja de brincos e piercings, desenvolvido para facilitar o controle de estoque, cadastro de produtos, clientes e organização de agendamentos.

## Tecnologias

- **Back-end:** PHP puro (8.2 ou superior) com PDO
- **Front-end:** HTML, CSS, JavaScript e Bootstrap 5
- **Banco de dados:** MySQL / MariaDB
- **Ambiente local:** XAMPP

---

## Como rodar o projeto

### 1. Instalar o XAMPP

Baixe em https://www.apachefriends.org e instale. **Toda a equipe deve usar a mesma versão.**

Abra o **XAMPP Control Panel** e clique em **Start** no **Apache** e no **MySQL**.

### 2. Clonar o repositório dentro do `htdocs`

```bash
cd C:\xampp\htdocs
git clone https://github.com/nozari-enzo/estoque-agenda-glowbella.git
cd estoque-agenda-glowbella
git checkout dev
```

### 3. Criar o arquivo de configuração

Copie `config/config.example.php` para `config/config.php`:

```bash
copy config\config.example.php config\config.php
```

Se você usa o XAMPP com o padrão (usuário `root` sem senha, projeto em `htdocs/estoque-agenda-glowbella`), não precisa mudar nada. Caso contrário, ajuste os dados no `config.php`.

> O `config.php` **não vai para o GitHub**. Cada um tem o seu.

### 4. Criar o banco de dados

Com o MySQL ligado no XAMPP, rode no terminal (dentro da pasta do projeto):

```bash
C:\xampp\php\php.exe database\migrar.php --seed
```

Isso cria o banco `glowbella`, todas as tabelas e os dados de exemplo.

- **Sempre que puxar a `dev`**, rode de novo `C:\xampp\php\php.exe database\migrar.php`. Ele só executa as migrations novas.
- Para **apagar tudo e recriar do zero**: `C:\xampp\php\php.exe database\migrar.php --reset --seed`
- **Login de teste** (criado pelo seed): `admin@glowbella.com` / `glowbella123`

<details>
<summary>Alternativa sem terminal (phpMyAdmin)</summary>

1. Acesse http://localhost/phpmyadmin, aba **SQL**, cole o conteúdo de `database/migrations/000_criar_banco.sql` e clique em **Executar**
2. Selecione o banco **glowbella** e, na aba **SQL**, execute os outros arquivos de `database/migrations/` **em ordem numérica**
3. Execute `database/seed.sql` para ter dados de exemplo

Não misture os dois jeitos: quem usa o phpMyAdmin deve continuar usando o phpMyAdmin.
</details>

O diagrama e as regras do banco estão em [`docs/banco-de-dados.md`](docs/banco-de-dados.md).

### 5. Acessar

- Sistema: http://localhost/estoque-agenda-glowbella/public/
- Teste do ambiente: http://localhost/estoque-agenda-glowbella/public/teste-conexao.php

Se a página de teste mostrar as versões do PHP e do MySQL, está tudo certo.

---

## Estrutura de pastas

```
estoque-agenda-glowbella/
├── config/                 Configuração (config.php é local e não vai pro Git)
├── includes/               Arquivos PHP compartilhados (não acessíveis pelo navegador)
│   ├── init.php            Carrega config, sessão, funções e conexão ($pdo)
│   ├── conexao.php         Conexão PDO com o MySQL
│   ├── funcoes.php         Funções úteis: e(), url(), redirecionar(), flash(), csrf_*()
│   ├── header.php          Início do HTML, Bootstrap e menu
│   └── footer.php          Rodapé e scripts
├── public/                 Páginas acessadas pelo navegador
│   ├── index.php           Home
│   ├── produtos/           Um módulo por pasta (index.php, criar.php, editar.php, excluir.php)
│   ├── clientes/
│   ├── estoque/
│   ├── agendamentos/
│   ├── assets/             css/, js/, img/
│   └── uploads/            Fotos enviadas pelo sistema (não vão pro Git)
├── database/
│   ├── migrations/         Arquivos SQL numerados (000_, 001_, 002_...)
│   └── seed.sql            Dados de exemplo
└── docs/                   Documentação e status reports das sprints
```

### Modelo de página

Toda página nova em `public/<modulo>/` segue este formato:

```php
<?php
require __DIR__ . '/../../includes/init.php';

// lógica da página (consultas, processamento de formulário...)
$stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
$stmt->execute([$_GET['id'] ?? 0]);
$produto = $stmt->fetch();

$titulo = 'Editar produto';
require __DIR__ . '/../../includes/header.php';
?>

<h1><?= e($produto['nome']) ?></h1>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
```

---

## Regras da equipe

1. **Banco de dados versionado.** Toda mudança no banco vira um arquivo `.sql` **numerado** em `database/migrations/` (ex.: `003_criar_clientes.sql`). Ao puxar a `dev`, rode `database\migrar.php` para aplicar os arquivos novos. **Nunca edite um arquivo que já foi para a `dev`**: para alterar algo, crie um novo (ex.: `007_adiciona_foto_produtos.sql`).
2. **Sempre PDO com prepared statements.** Nunca coloque variáveis direto no SQL (evita SQL Injection).
   ```php
   $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
   $stmt->execute([$id]);
   ```
3. **Sempre `e()` ao exibir dados** do usuário ou do banco (evita XSS): `<?= e($cliente['nome']) ?>`
4. **Todo formulário POST tem `<?= csrf_campo() ?>`** e o processamento começa com `csrf_validar();`.
5. **Senhas** só com `password_hash()` / `password_verify()`.
6. **Header e footer sempre via `require`**, nunca copiados.
7. **Links sempre com `url()`**: `<a href="<?= url('produtos/') ?>">`.

---

## Fluxo de trabalho com Git

```
main  ← PR no fim de cada sprint (versão estável)
 └─ dev  ← PR de cada feature (revisado por outro integrante)
     └─ feature/<nome>
```

1. Atualize a `dev` e crie sua branch a partir dela:
   ```bash
   git checkout dev
   git pull origin dev
   git checkout -b feature/cadastro-produtos
   ```
2. Faça commits pequenos e com mensagens claras:
   ```bash
   git add .
   git commit -m "US06 - adiciona listagem de produtos"
   ```
3. Envie a branch e abra um **Pull Request para a `dev`** no GitHub:
   ```bash
   git push -u origin feature/cadastro-produtos
   ```
4. Outro integrante revisa e aprova o PR. Depois do merge, a branch pode ser apagada.
5. No fim da sprint, é aberto um PR da `dev` para a `main`.

**Nunca faça push direto na `main`.**
