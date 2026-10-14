# estoque-agenda-glowbella
Sistema de gerenciamento para uma loja de brincos e piercings, desenvolvido para facilitar o controle de estoque, cadastro de produtos, clientes e organização de agendamentos.

## Tecnologias

- **Back-end:** PHP puro (8.0 ou superior) com PDO
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
├── app/                    Código compartilhado do servidor (bloqueado no navegador)
│   ├── bootstrap/init.php  Inicializa config, sessão, funções e conexão ($pdo)
│   ├── infrastructure/     Integração com o MySQL (conexao.php)
│   ├── support/            Funções comuns (funcoes.php)
│   └── views/partials/     Estrutura HTML comum (header.php e footer.php)
├── public/                 Páginas acessadas pelo navegador
│   ├── index.php           Home
│   ├── produtos/           Catálogo e protótipo de cadastro
│   ├── clientes/
│   ├── estoque/
│   ├── esterilizacao/      Protótipo de histórico de ciclos
│   ├── agendamentos/
│   ├── assets/             CSS, JavaScript e imagens
│   └── uploads/            Fotos enviadas pelo sistema (não vão pro Git)
├── database/
│   ├── migrations/         Arquivos SQL numerados (000_, 001_, 002_...)
│   └── seed.sql            Dados de exemplo
└── docs/                   Documentação e status reports das sprints
```

### Onde procurar cada coisa

- **Tela ou URL:** `public/` — cada módulo tem sua própria pasta; CSS, JavaScript e imagens ficam em `public/assets/`.
- **Interface:** HTML renderizado pelas páginas PHP, Bootstrap 5 carregado pelo layout, estilos locais em `public/assets/css/style.css` e comportamentos de interface em `public/assets/js/app.js`.
- **Configuração e conexão:** `config/` guarda as opções locais; `app/infrastructure/conexao.php` abre a conexão PDO.
- **Inicialização e funções comuns:** `app/bootstrap/` e `app/support/`.
- **Cabeçalho e rodapé compartilhados:** `app/views/partials/`.
- **Tabelas e alterações no banco:** `database/migrations/`. Depois de publicada na branch da equipe, uma migration não deve ser editada; crie a próxima migration numerada.
- **Regras e decisões do produto:** `docs/`.

As páginas continuam acessíveis por `public/`. A pasta `app/` organiza o código usado por elas e não deve conter arquivos acessados diretamente pelo navegador. Ao implementar uma funcionalidade com regras ou consultas maiores, mantenha a tela em `public/<modulo>/` e separe a lógica do HTML dentro de `app/` para que a equipe encontre cada responsabilidade com facilidade.

As telas de produtos, estoque e esterilização incluem protótipos visuais com dados demonstrativos. Os filtros e a exibição dos campos de lote/validade rodam somente no navegador; os botões de cadastro, movimentação e registro ainda não persistem dados. A integração deve ser feita depois que as regras e tabelas do back-end forem definidas.

Para as próximas funcionalidades, use esta divisão quando ela fizer sentido:

- `public/<modulo>/`: entrada da URL e resposta HTTP.
- `app/controllers/<modulo>/`: fluxo da tela e validação dos dados recebidos.
- `app/repositories/<modulo>/`: consultas e gravações no MySQL usando PDO.
- `app/views/<modulo>/`: HTML da tela; layouts comuns ficam em `app/views/partials/`.

Não crie arquivos vazios só para preencher essas pastas. Ao começar uma funcionalidade, adicione apenas as partes necessárias e mantenha o mesmo nome de módulo nas camadas. Assim, por exemplo, tudo relacionado a produtos pode ser encontrado procurando por `produtos` nesses caminhos.

### Fluxo de uma tela nova

1. A URL e o ponto de entrada ficam em `public/<modulo>/`.
2. O ponto de entrada carrega `app/bootstrap/init.php`.
3. O controller do módulo valida a requisição e coordena a ação; o repository concentra o SQL e usa PDO.
4. A view do módulo monta o HTML. Ela inclui os layouts compartilhados de `app/views/partials/`.
5. CSS, JavaScript e imagens usados pelo navegador ficam em `public/assets/`.

Para uma página realmente simples, não é necessário criar todas as camadas. Em telas com cadastro, edição ou regras de negócio, siga essa separação para que consultas, validações e HTML não se acumulem no mesmo arquivo.

---

## Regras da equipe

1. **Banco de dados versionado.** Toda mudança no banco vira um arquivo `.sql` **numerado** em `database/migrations/` (ex.: `008_alteracao_exemplo.sql`, após a migration atual `007`). Ao puxar a `dev`, rode `database\migrar.php` para aplicar os arquivos novos. **Nunca edite um arquivo que já foi para a `dev`**: crie a próxima migration numerada.
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
8. **Visual padronizado:** antes de criar uma tela, abra o **Guia de componentes** (http://localhost/estoque-agenda-glowbella/public/componentes.php) e copie o HTML de lá. Cores e estilos novos vão em `public/assets/css/style.css`, nunca em `style=""` dentro do HTML.

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
4. Preencha o modelo do PR (ele aparece automaticamente). Outro integrante revisa e aprova. Depois do merge, a branch pode ser apagada.
5. Se o PR trouxe migrations novas, todos rodam `database\migrar.php` depois de puxar a `dev`.
6. No fim da sprint, é aberto um PR da `dev` para a `main`.

**Nunca faça push direto na `main`.**
