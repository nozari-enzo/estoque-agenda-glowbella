# Hospedagem: como instalar, atualizar e criar usuários

Guia para quem cuida do site no ar: **https://glowbella.rf.gd/estoque-agenda-glowbella**, na InfinityFree.

A InfinityFree **não tem terminal (SSH)**. Por isso, o `migrar.php` e o `criar_usuario.php` não rodam lá. As duas ferramentas abaixo rodam **no seu XAMPP** e geram um arquivo `.sql`, que você importa no phpMyAdmin da hospedagem.

| Ferramenta (roda no seu PC) | Para quê | Gera |
|---|---|---|
| `database\gerar_atualizacao.php` | Juntar as migrations que faltam num arquivo só | `atualizacao-hospedagem.sql` |
| `database\criar_usuario.php ... --sql` | Criar um usuário ou trocar a senha | `usuario-hospedagem.sql` |

Os dois arquivos aparecem na pasta do projeto e **não vão para o GitHub** (estão no `.gitignore`). Depois de importar, apague-os, porque o de usuário tem o hash da senha.

---

## 1. Regra principal: a hospedagem recebe a `main`

| Onde | O que tem | Quem usa |
|---|---|---|
| `dev` + XAMPP | Versão em desenvolvimento, pode quebrar | Equipe |
| `main` | Versão fechada no fim de cada sprint | — |
| **Hospedagem** | **A mesma versão da `main`** | Loja, PO, professor |

- **Atualize a hospedagem uma vez por sprint**, logo depois do merge `dev` → `main`. Não suba coisas da `dev` no meio da sprint. Upload manual e migration à mão são fáceis de errar, e quem está usando o site não pode ver o sistema mudando no meio do caminho.
- **Exceção:** um bug grave no site, ou uma demonstração marcada. Mesmo assim, a correção passa pela `main` antes.

---

## 2. Onde ficam as coisas na InfinityFree

No **painel da InfinityFree** (Client Area → a conta do site → **Control Panel**):

- **MySQL Databases:** nome do banco (`if0_XXXXXXX_...`), usuário (`if0_XXXXXXX`) e **MySQL Host** (`sqlXXX.infinityfree.com`). O link **phpMyAdmin** também fica aqui.
- **Senha do banco e do FTP:** é a senha da conta de hospedagem. Ela aparece na Client Area, em **Account Details** (botão "Show").
- **Arquivos:** pelo **Online File Manager** do painel ou por FTP (FileZilla, servidor `ftpupload.net`, porta 21, mesmo usuário e senha). O site fica dentro de `htdocs/`.

**Nunca coloque essas senhas no GitHub, no README ou no grupo.**

Estrutura no servidor:

```
htdocs/
└── estoque-agenda-glowbella/
    ├── index.php          → manda para public/login.php
    ├── app/               → protegido (.htaccess)
    ├── config/
    │   └── config.php     → SÓ existe no servidor (ver abaixo)
    ├── database/          → protegido (.htaccess)
    └── public/            → as páginas do sistema
```

### O `config/config.php` do servidor

Ele é diferente do seu XAMPP e **nunca deve ser sobrescrito** ao atualizar. Modelo, preenchido com os dados do painel:

```php
<?php
define('DB_HOST', 'sqlXXX.infinityfree.com');   // MySQL Host do painel
define('DB_PORT', '3306');
define('DB_NAME', 'if0_XXXXXXX_glowbella');      // nome completo do banco
define('DB_USER', 'if0_XXXXXXX');
define('DB_PASS', 'senha-da-conta');             // NUNCA no GitHub

define('BASE_URL', 'https://glowbella.rf.gd/estoque-agenda-glowbella/public');

define('APP_DEBUG', false);                       // false no ar: não mostra erros técnicos
define('APP_TIMEZONE', 'America/Sao_Paulo');
```

> Precisa ver o erro técnico de uma página? Mude `APP_DEBUG` para `true`, veja o erro e **volte para `false`**.

---

## 3. Atualizar a hospedagem (fim de sprint)

Antes, faça o merge da `dev` na `main` e baixe a `main` atualizada no seu PC:

```
git checkout main
git pull
```

### Passo 1: Backup do banco da hospedagem

phpMyAdmin da hospedagem → selecione o banco → **Exportar** → **Executar**. Guarde o `.sql` com a data no nome (ex.: `backup-glowbella-2026-12-03.sql`) fora do computador (Drive etc.).

### Passo 2: Descobrir a última migration que a hospedagem tem

No phpMyAdmin da hospedagem, aba **SQL**:

```sql
SELECT * FROM migracoes ORDER BY arquivo;
```

O número da última linha é o que vai no próximo passo (ex.: `009`).

> **Se der "Table 'migracoes' doesn't exist":** o banco foi montado à mão, sem registro. Use a tabela **"Histórico da hospedagem"** no fim deste guia. A instalação inicial (`main` de 19/11) foi até a **009**. Depois do primeiro pacote, a tabela `migracoes` passa a existir.

### Passo 3: Gerar o pacote de migrations (no seu PC)

Na pasta do projeto, com a `main` baixada:

```
C:\xampp\php\php.exe database\gerar_atualizacao.php --depois-de 009
```

Troque `009` pelo número do passo 2. Ele mostra quais migrations entraram no pacote e cria o `atualizacao-hospedagem.sql`. Se aparecer "Nenhuma migration depois da...", o banco já está em dia: pule o passo 5.

### Passo 4: Subir os arquivos

Pelo File Manager ou pelo FTP, envie para `htdocs/estoque-agenda-glowbella/`, **substituindo**:

- as pastas **`app/`**, **`public/`** e **`database/`** inteiras;
- o arquivo **`index.php`** da raiz.

**Cuidados:**
- **NÃO** envie nem substitua `config/config.php`. O do servidor tem os dados de lá.
- **NÃO** apague `public/uploads/` no servidor.
- **Confira os `.htaccess`:** as pastas `app/`, `config/` e `database/` têm um arquivo `.htaccess` que bloqueia o acesso pelo navegador. Alguns programas de FTP escondem arquivos que começam com ponto. No FileZilla, ative **Servidor → Forçar exibição de arquivos ocultos**.
- **Não envie** `.git/`, `node_modules/`, `atualizacao-hospedagem.sql` nem `usuario-hospedagem.sql`.

### Passo 5: Importar o pacote no banco

phpMyAdmin da hospedagem → selecione o banco → **Importar** → escolha `atualizacao-hospedagem.sql` → **Executar**. Importe **uma vez só**.

- **Deu erro "Duplicate column" ou "already exists"?** Essa migration já tinha sido aplicada. Pare e confira o passo 2. Nada é aplicado duas vezes.
- **Deu certo?** Apague o `atualizacao-hospedagem.sql` do seu PC.

> Faça os passos 4 e 5 em sequência. Código novo com o banco antigo dá erro, como "Unknown column 'vl_total'".

### Passo 6: Testar no ar

Abra o site, dê **Ctrl+F5** e confira:

- [ ] O login funciona.
- [ ] Início, Agenda, Clientes, Produtos, Estoque, Esterilização e Financeiro abrem sem erro.
- [ ] Salvar alguma coisa funciona (ex.: editar um cliente).
- [ ] Estes endereços dão **403 / Forbidden** (se abrir conteúdo, avise a equipe **na hora**):
  - `.../estoque-agenda-glowbella/config/config.php`
  - `.../estoque-agenda-glowbella/database/seed.sql`
  - `.../estoque-agenda-glowbella/app/services/auth.php`

### Passo 7: Anotar

Acrescente uma linha no **Histórico da hospedagem** (fim deste guia) e cite a atualização no status report da sprint.

---

## 4. Colocar em uso com os dados reais da loja

Hoje o banco da hospedagem tem os **dados de exemplo** (`seed`), inclusive o login `admin@glowbella.com` / `glowbella123`, que está escrito no README público. **Antes de a loja usar de verdade:**

1. **Backup**, se quiser guardar os testes: Exportar, como no passo 1 da seção 3.
2. **Esvaziar o banco:** phpMyAdmin → selecione o banco → aba **Estrutura** → **Marcar todos** → no menu "Com marcados", **Eliminar** → confirmar. O banco fica sem tabelas.
3. **Gerar o pacote completo, SEM `--seed`**, a partir da `main` atualizada:
   ```
   C:\xampp\php\php.exe database\gerar_atualizacao.php
   ```
   Sem `--depois-de`, ele inclui todas as migrations. Importe como no passo 5.
4. **Criar o usuário da dona** (seção 5).
5. **Entrar com ele** e fazer o cadastro inicial, nesta ordem:
   1. **Produtos → Categorias**
   2. **Produtos → Novo produto**, com a quantidade atual na "Quantidade inicial"
   3. **Clientes**
   4. **Agenda:** próximos atendimentos, com o valor
6. Fazer um **backup** logo depois do cadastro inicial.

Como não tem `seed`, **o admin de teste não existe** no banco real. Nunca use `--seed` com dados reais.

---

## 5. Usuários

Não existe cadastro de usuário pela tela, de propósito: ninguém de fora consegue criar conta.

### Criar um usuário, ou trocar a senha de quem esqueceu

No seu PC, na pasta do projeto:

```
C:\xampp\php\php.exe database\criar_usuario.php "Nome da Pessoa" email@dela.com --sql
```

1. Digite a senha quando ele pedir: mínimo 8 caracteres, e ela não aparece no histórico de comandos.
2. phpMyAdmin da hospedagem → banco → **Importar** → `usuario-hospedagem.sql` → **Executar**.
3. **Apague o `usuario-hospedagem.sql`** do seu PC.
4. Passe a senha para a pessoa **pessoalmente** ou por um canal privado, nunca no grupo.

Se o e-mail já existir, o mesmo comando **troca a senha** e reativa o usuário. É assim que se resolve "esqueci a senha".

> Sem o `--sql`, o comando grava no banco do **seu XAMPP** (útil para testar local).

### Tirar o acesso de alguém

phpMyAdmin da hospedagem, aba **SQL**:

```sql
UPDATE admin SET fg_status = 0 WHERE ds_email = 'email@dela.com';
```

O login dessa pessoa para de funcionar. Para devolver o acesso, rode o mesmo comando com `fg_status = 1`, ou gere uma senha nova com o `--sql`.

### Ver quem tem acesso

```sql
SELECT cd_usuario, nm_usuario, ds_email, fg_status FROM admin;
```

---

## 6. Backup

- **Quando:** antes de toda atualização, e pelo menos **uma vez por semana** quando a loja estiver usando.
- **Como:** phpMyAdmin da hospedagem → banco → **Exportar** → **Executar**.
- **Onde guardar:** fora do computador e fora da hospedagem (Drive da equipe, com acesso restrito). O backup tem dados das clientes (LGPD).
- **Restaurar:** esvazie o banco (seção 4, passo 2) e importe o backup.

A InfinityFree é gratuita e **não garante backup**. O backup de vocês é o único.

---

## 7. Problemas comuns

| O que aparece | Causa provável | O que fazer |
|---|---|---|
| "Unknown column ..." / "Table ... doesn't exist" | Código novo com banco antigo | Gerar e importar o pacote (seção 3, passos 2, 3 e 5) |
| "Erro ao conectar ao banco de dados" | Dados errados no `config.php` do servidor | Conferir host, nome do banco, usuário e senha no painel |
| Página em branco ou erro 500 | Erro de PHP escondido (`APP_DEBUG` = false) | Ligar `APP_DEBUG` = true, ver o erro, desligar de novo |
| Página sem estilo ou com comportamento antigo | Cache do navegador | Ctrl+F5 |
| `config.php` ou `seed.sql` abrem no navegador | `.htaccess` não foi enviado | Reenviar os `.htaccess` (passo 4) e testar de novo |
| "E-mail ou senha incorretos" com a senha certa | Usuário desativado ou não criado | `SELECT ... FROM admin` (seção 5) e gerar a senha de novo com `--sql` |
| "Duplicate column" ao importar | Migration já aplicada | Conferir `SELECT * FROM migracoes` antes de gerar o pacote |
| Login funciona, mas salvar dá erro | Sessão antiga | Clicar em Sair e entrar de novo |

---

## Histórico da hospedagem

Uma linha por atualização (a mais nova embaixo).

| Data | Versão da `main` | Última migration | Banco | Quem |
|---|---|---|---|---|
| ~19/11/2026 | PR #46 (login, todas as telas) | 009 | Teste (com `seed`) | Pedro |
| | | | | |

**Próxima atualização (pendente):** quando a `dev` atual for para a `main`, entram a busca de produto no Estoque (PR #47) e as vendas de balcão no Financeiro (PR #48), com a migration **010**. Comando do pacote: `--depois-de 009`.
