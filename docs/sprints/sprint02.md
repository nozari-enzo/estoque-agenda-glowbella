# Status Report da Sprint 2

## 1. Identificação

- **Projeto:** GlowBella
- **Número da Sprint:** 2
- **Período:** 24/09 a 08/10
- **Integrantes:** Pedro (Product Owner), Amanda (Scrum Master), Enzo, Rafael, Yuri e Davi (Desenvolvedores)
- **Scrum Master da Sprint:** Amanda

### Meta da Sprint

> Informe a meta definida no planejamento da sprint que está sendo finalizada.

**Meta:**

Montar a base técnica do sistema (estrutura do projeto, banco de dados, layout e repositório organizado) e entregar o primeiro protótipo funcional da página inicial.

---

## 2. Resultado da Sprint

### Situação da meta

- [X] Alcançada
- [ ] Parcialmente alcançada
- [ ] Não alcançada

### Resultado alcançado

> Descreva brevemente quais funcionalidades ou resultados foram concluídos e estão funcionando ao final da sprint.

**Resultado:**

- Projeto em PHP puro rodando no XAMPP, com conexão ao MySQL e passo a passo de instalação no README.
- Banco de dados modelado com 7 tabelas (usuários, categorias, produtos, clientes, serviços, movimentações de estoque e agendamentos), script de migrations e dados de exemplo.
- Layout padrão com identidade visual da GlowBella, menu responsivo e guia de componentes para a equipe.
- Página inicial com atalhos para Agenda, Clientes, Produtos e Estoque, exibindo os totais do banco.
- Repositório organizado com o fluxo `feature → dev → main`, modelo de Pull Request e branches `main` e `dev` protegidas (1 aprovação obrigatória).
- Entrega revisada e integrada na `dev` (PR #7) e na `main` (PR #9).

### Principal dificuldade ou impedimento

> Informe somente a dificuldade ou o impedimento que mais afetou a sprint. Caso não tenha ocorrido, escreva “Nenhum impedimento relevante”.

**Dificuldade ou impedimento:**

Configuração inicial do ambiente e do repositório: permissões de acesso ao GitHub e diferenças entre os ambientes locais (ex.: usuário do MySQL com senha no XAMPP) atrasaram o primeiro envio e o teste do código.

---

## 3. Itens planejados e situação final

**Utilize apenas os seguintes status:**

- Concluído
- Em andamento
- Não iniciado
- Bloqueado

| User Story ou item | Responsável(is) | Situação final | Observação |
|---|---|---|---|
| US02 – Página Home |  | Concluído | Atalhos para os módulos com totais do banco. Ajustes finos conforme o wireframe podem ser feitos nas próximas sprints |
| US03 – Organização do repositório |  | Concluído | Fluxo `feature → dev → main`, modelo de PR e branches protegidas |
| US04 – Setup inicial do projeto |  | Concluído | Testado no XAMPP (Windows) |
| US05 – Layout base e identidade visual |  | Concluído | Inclui guia de componentes em `public/componentes.php` |
| US06 – Primeiras ideias Banco de Dados |  | Concluído | Diagrama e regras em `docs/banco-de-dados.md` |

> Inclua somente as User Stories ou os itens principais planejados para a sprint.  
> Não copie todas as tarefas menores do quadro Kanban.

---

## 4. Evidências e qualidade

### Evidências

- **Repositório:** https://github.com/nozari-enzo/estoque-agenda-glowbella
- **Quadro Kanban:** https://trello.com/invite/b/6aabeabdfff125fed81d0d55/ATTIe411ab9da7c4a26ee0ecf4b85fd53eafD0E7022E/glow-bella
- **Deploy ou instruções para executar o projeto:** instruções disponíveis no README (seção "Como rodar o projeto")
- **Outras evidências, se necessárias:**
  - PR #7 (entrega na `dev`): https://github.com/nozari-enzo/estoque-agenda-glowbella/pull/7
  - PR #9 (entrega na `main`): https://github.com/nozari-enzo/estoque-agenda-glowbella/pull/9
  - Modelagem do banco: `docs/banco-de-dados.md`

### Checklist de qualidade

- [X] Os itens marcados como concluídos atendem aos critérios de aceite.
- [X] As funcionalidades entregues foram testadas pela equipe.
- [X] O código atualizado está no repositório oficial.
- [ ] Os problemas conhecidos estão registrados no Kanban ou no repositório.

### Problemas conhecidos

> Informe os problemas que permanecem no incremento. Caso não tenham sido identificados, escreva “Nenhum problema conhecido”.

**Registro:**

- As páginas de Produtos, Clientes, Estoque e Agenda ainda são provisórias ("Em construção"); serão desenvolvidas nas próximas sprints.
- O usuário de teste criado pelos dados de exemplo (`admin@glowbella.com`) deve ter a senha trocada antes do deploy.
- A numeração das User Stories precisa ser revisada no Trello para as próximas sprints.

---

## 5. Retrospectiva da Sprint

### Manter

> O que funcionou bem e deve continuar?

**Registro:**

Revisão do código por Pull Request antes de integrar na `dev` e na `main`, com descrição padronizada e passo a passo de teste.

### Melhorar

> O que precisa mudar na próxima sprint?

**Registro:**

Uso do GitHub na revisão: houve um PR aberto para a branch errada e revisões enviadas como comentário em vez de aprovação.
Comunicação e registro de documentos muito tardia.
Falta de reuniões de retrospectiva e daily.

### Agir

> Qual ação concreta a equipe adotará na próxima sprint?

**Ação:**

Cada integrante cria sua própria branch `feature/<nome>` a partir da `dev`, abre PRs pequenos para a `dev` seguindo o modelo do repositório e revisa os PRs dos colegas usando a opção "Approve".

---

## 6. Planejamento da próxima Sprint

### Meta da próxima Sprint

> Escreva um resultado claro e verificável que a equipe pretende alcançar.

**Meta:**

Banco de dados cadastrado e repositório organizado com duas pastas Front-End e Back-End

### Itens inicialmente selecionados

| User Story ou item | Responsável(is), se definido(s) | Resultado esperado |
|---|---|---|
| Cadastro de produtos |  | Listar, buscar, cadastrar, editar e desativar produtos |
| Cadastro de agendamentos |  | Listar, buscar, cadastrar, editar agendamentos | |
| Criar duas pastas Front-End e Back-End


> Esta é uma seleção inicial. O planejamento poderá ser ajustado pela equipe no início da próxima sprint.

### Riscos ou impedimentos previstos

> Informe os principais fatores que podem dificultar o cumprimento da próxima meta. Caso nenhum risco tenha sido identificado, escreva “Nenhum risco identificado”.

**Riscos:**

Conflitos de código com várias pessoas trabalhando em paralelo pela primeira vez; para reduzir, a equipe vai manter os PRs pequenos e atualizar as branches com a `dev` com frequência.
