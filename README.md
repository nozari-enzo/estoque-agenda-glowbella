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
- Se aparecer **"Este banco foi criado com migrations que não existem mais"**, a estrutura do banco mudou (ex.: refatoração do banco). Recrie com o comando `--reset --seed` acima. Isso apaga os dados locais de teste.
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
│   ├── repositories/       SQL de cada módulo (ex.: produtos/produtos.php)
│   ├── services/           Regras de negócio (ex.: auth.php, login e sessão)
│   ├── support/            Funções comuns (funcoes.php e api.php)
│   └── views/partials/     Estrutura HTML comum (header.php e footer.php)
├── public/                 Páginas acessadas pelo navegador
│   ├── index.php           Home
│   ├── api/                APIs em JSON usadas pelas telas (auth.php, produtos.php...)
│   ├── produtos/           Catálogo e protótipo de cadastro
│   ├── clientes/           Lista, cadastro e edição de clientes (tela de referência)
│   ├── estoque/
│   ├── esterilizacao/      Protótipo de histórico de ciclos
│   ├── agendamentos/
│   ├── financeiro/         Protótipo de receitas, joias e gastos
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

As telas de produtos, estoque, esterilização e financeiro incluem protótipos visuais com dados demonstrativos. Os filtros e a exibição dos campos de lote/validade rodam somente no navegador; as telas ainda não persistem dados. O Financeiro demonstra a separação entre valor do procedimento, venda da joia e compras, com referências à Agenda. Com as APIs prontas, a integração de cada tela segue o guia [`docs/front.md`](docs/front.md).

Cada módulo usa o mesmo nome em todas as camadas:

- `public/<modulo>/`: as páginas (lista, criar, editar).
- `public/assets/js/<modulo>.js`: o JavaScript da tela (formulários e botões que chamam a API).
- `public/api/<modulo>.php`: API em JSON do módulo, chamada pelas telas com `glowApi()`.
- `app/repositories/<modulo>/`: consultas e gravações no MySQL usando PDO.
- `app/services/`: regras de negócio que envolvem mais de uma tabela (ex.: estoque, conflito de agenda).
- `app/views/partials/`: header e footer comuns.

As APIs, a função `glowApi()` e o passo a passo para criar uma API nova estão em [`docs/api.md`](docs/api.md).
O padrão para fazer as telas (lista em PHP, salvar pela API) está em [`docs/front.md`](docs/front.md).

Não crie arquivos vazios só para preencher essas pastas. Ao começar uma funcionalidade, adicione apenas as partes necessárias e mantenha o mesmo nome de módulo nas camadas. Assim, por exemplo, tudo relacionado a produtos pode ser encontrado procurando por `produtos` nesses caminhos.

### Fluxo de uma tela nova

**Guia completo, com exemplo testado: [`docs/front.md`](docs/front.md).** Resumo:

1. A página fica em `public/<modulo>/` e começa com `require` do `app/bootstrap/init.php` e do repository do módulo.
2. **Lista:** a página chama o repository (`listar_clientes($pdo)`) e mostra os dados com `e()`. Nada de SQL na página.
3. **Criar, editar, desativar:** o formulário chama a API (`public/api/<modulo>.php`) com `glowFormulario()` ou `glowApi()`. A API valida e aplica as regras.
4. O JavaScript da tela fica em `public/assets/js/<modulo>.js`, e o CSS em `public/assets/css/style.css`.

---

## Regras da equipe

1. **Banco de dados versionado.** Toda mudança no banco vira um arquivo `.sql` **numerado** em `database/migrations/` (ex.: `010_alteracao_exemplo.sql`, após a migration atual `009`). Ao puxar a `dev`, rode `database\migrar.php` para aplicar os arquivos novos. **Nunca edite um arquivo que já foi para a `dev`**: crie a próxima migration numerada.
2. **SQL só em `app/` (repositories e services), sempre com PDO e prepared statements.** Nunca nas páginas, e nunca com variáveis direto no SQL (evita SQL Injection). As páginas chamam as funções de `app/repositories/<modulo>/`.
   ```php
   $stmt = $pdo->prepare('SELECT * FROM produto WHERE cd_produto = ?');
   $stmt->execute([$cdProduto]);
   ```
3. **Sempre `e()` ao exibir dados** do usuário ou do banco (evita XSS): `<?= e($cliente['nm_cliente']) ?>`. No JavaScript, use `textContent`, nunca `innerHTML` com dados da API.
4. **Gravar sempre pela API.** Formulários de criar, editar e desativar usam `glowFormulario()` / `glowApi()`, que já enviam o token CSRF (veja [`docs/front.md`](docs/front.md)). Se um formulário precisar fazer POST para a própria página, ele tem `<?= csrf_campo() ?>` e o processamento começa com `csrf_validar();`.
5. **Senhas** só com `password_hash()` / `password_verify()`.
6. **Header e footer sempre via `require`**, nunca copiados.
7. **Links sempre com `url()`**: `<a href="<?= url('produtos/') ?>">`.
8. **Visual padronizado:** antes de criar uma tela, abra o **Guia de componentes** (http://localhost/estoque-agenda-glowbella/public/componentes.php) e copie o HTML de lá. Cores e estilos novos vão em `public/assets/css/style.css`, nunca em `style=""` dentro do HTML.

## Conferência visual

As páginas devem ser abertas no XAMPP e conferidas em desktop e celular. Para gerar capturas locais e verificar se o Bootstrap carregou, se houve erro JavaScript ou se apareceu rolagem horizontal:

1. Instale o Node.js LTS na máquina de desenvolvimento. Ele é usado apenas pelas ferramentas de conferência; o sistema continua rodando em PHP no XAMPP.
2. Na primeira configuração do repositório, execute `npm.cmd ci` e `npx.cmd playwright install chromium`.
3. Inicie o Apache no XAMPP e rode `npm.cmd run visual:esterilizacao` na raiz do projeto.

As imagens são salvas em `artifacts/visual/`, pasta local ignorada pelo Git. Para conferir outra tela, use `npm.cmd run visual -- financeiro/`. A URL base padrão é `http://localhost/glowbella`; se ela for diferente, defina `BASE_URL` antes de rodar o comando. A página usa arquivos do Bootstrap e fontes por CDN, então a primeira captura precisa de acesso à internet.

Para capturar todas as telas alteradas nesta etapa, use `npm.cmd run visual:all`.

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
