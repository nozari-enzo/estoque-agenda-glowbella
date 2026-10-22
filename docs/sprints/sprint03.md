# Status Report da Sprint 3

## 1. Identificação

- **Projeto:** GlowBella
- **Número da Sprint:** 3
- **Período:** 08/10 a 22/10
- **Integrantes:** Pedro (Product Owner), Amanda (Scrum Master), Enzo, Rafael, Yuri e Davi (Desenvolvedores)
- **Scrum Master da Sprint:** Amanda

### Meta da Sprint

> Informe a meta definida no planejamento da sprint que está sendo finalizada.

**Meta:**

Banco de dados cadastrado e repositório organizado com duas pastas Front-End e Back-End.

---

## 2. Resultado da Sprint

### Situação da meta

- [ ] Alcançada
- [X] Parcialmente alcançada
- [ ] Não alcançada

### Resultado alcançado

> Descreva brevemente quais funcionalidades ou resultados foram concluídos e estão funcionando ao final da sprint.

**Resultado:**

- Código reorganizado em duas partes: `app/` com o código do servidor (inicialização, conexão com o banco, funções de apoio e partes comuns do layout) e `public/` com as telas, CSS, JavaScript e imagens.
- Protótipo visual da tela de Produtos, com catálogo, busca, filtros e formulário de "Novo produto".
- Protótipo visual da tela de Estoque, incluindo a exibição de lote e validade.
- Novo módulo de Esterilização (protótipo), com histórico de ciclos, indicadores e validade dos pacotes.
- README atualizado com a nova estrutura de pastas e onde encontrar cada parte do código.
- Status report da Sprint 2 ajustado (PR #12).
- Entrega revisada, aprovada e integrada na `dev` (PR #13).

### Principal dificuldade ou impedimento

> Informe somente a dificuldade ou o impedimento que mais afetou a sprint. Caso não tenha ocorrido, escreva “Nenhum impedimento relevante”.

**Dificuldade ou impedimento:**

As regras de negócio de estoque (lote e validade) e de esterilização ainda não foram definidas com a cliente, por isso as telas ficaram como protótipos visuais e a gravação no banco não pôde ser implementada.

---

## 3. Itens planejados e situação final

**Utilize apenas os seguintes status:**

- Concluído
- Em andamento
- Não iniciado
- Bloqueado

| User Story ou item | Responsável(is) | Situação final | Observação |
|---|---|---|---|
| Cadastro de produtos |  | Em andamento | Listagem, busca, filtros e formulário prototipados; ainda não grava no banco |
| Cadastro de agendamentos |  | Não iniciado | A página da Agenda continua como "Em construção" |
| Criar duas pastas Front-End e Back-End |  | Concluído | `public/` (telas e assets) e `app/` (código do servidor) |
| Protótipos de Estoque e Esterilização (não planejado) |  | Concluído | Telas demonstrativas; regras e tabelas ainda precisam ser definidas |

> Inclua somente as User Stories ou os itens principais planejados para a sprint.  
> Não copie todas as tarefas menores do quadro Kanban.

---

## 4. Evidências e qualidade

### Evidências

- **Repositório:** https://github.com/nozari-enzo/estoque-agenda-glowbella
- **Quadro Kanban:** https://trello.com/invite/b/6aabeabdfff125fed81d0d55/ATTIe411ab9da7c4a26ee0ecf4b85fd53eafD0E7022E/glow-bella
- **Deploy ou instruções para executar o projeto:** instruções disponíveis no README (seção "Como rodar o projeto")
- **Outras evidências, se necessárias:**
  - PR #13 (protótipos e reorganização de pastas): https://github.com/nozari-enzo/estoque-agenda-glowbella/pull/13
  - PR #12 (ajuste do status report da Sprint 2): https://github.com/nozari-enzo/estoque-agenda-glowbella/pull/12

### Checklist de qualidade

- [X] Os itens marcados como concluídos atendem aos critérios de aceite.
- [X] As funcionalidades entregues foram testadas pela equipe.
- [X] O código atualizado está no repositório oficial.
- [ ] Os problemas conhecidos estão registrados no Kanban ou no repositório.

### Problemas conhecidos

> Informe os problemas que permanecem no incremento. Caso não tenham sido identificados, escreva “Nenhum problema conhecido”.

**Registro:**

- As telas de Produtos, Estoque e Esterilização usam dados demonstrativos: os botões de cadastrar, movimentar e registrar ainda não salvam nada.
- Lote, validade e ciclos de esterilização ainda não existem no banco de dados; será preciso criar novas migrations.
- As páginas de Clientes e Agenda continuam como "Em construção".
- A entrega da Sprint 3 está na `dev`, mas ainda não foi levada para a `main`.
- O usuário de teste criado pelos dados de exemplo (`admin@glowbella.com`) deve ter a senha trocada antes do deploy.

---

## 5. Retrospectiva da Sprint

### Manter

> O que funcionou bem e deve continuar?

**Registro:**

- Uso do modelo de Pull Request, com descrição do que foi feito e passo a passo de teste.
- Revisão e aprovação antes do merge na `dev`.
- Organização do código em pastas, que facilita encontrar cada parte do sistema.

### Melhorar

> O que precisa mudar na próxima sprint?

**Registro:**

- A entrega ficou concentrada em um único PR grande, que misturou a reorganização de pastas com três telas novas, o que dificulta a revisão.
- As regras de negócio não foram validadas com a cliente antes de começar as telas.
- O cadastro de agendamentos, que estava planejado, não foi iniciado.

### Agir

> Qual ação concreta a equipe adotará na próxima sprint?

**Ação:**

No início da sprint, o Product Owner valida com a cliente as regras de estoque e esterilização, e cada integrante assume ao menos uma tarefa, entregando um PR por tela ou funcionalidade.

---

## 6. Planejamento da próxima Sprint

### Meta da próxima Sprint

> Escreva um resultado claro e verificável que a equipe pretende alcançar.

**Meta:**

Cadastro de produtos e de agendamentos gravando no banco de dados, e regras de estoque e esterilização definidas com a cliente.

### Itens inicialmente selecionados

| User Story ou item | Responsável(is), se definido(s) | Resultado esperado |
|---|---|---|
| Cadastro de produtos (gravação no banco) |  | Cadastrar, listar, editar e desativar produtos salvando no MySQL |
| Cadastro de agendamentos |  | Cadastrar, listar e editar agendamentos, sem permitir conflito de horário |
| Regras de estoque e esterilização |  | Regras validadas com a cliente e migrations das novas tabelas criadas |

> Esta é uma seleção inicial. O planejamento poderá ser ajustado pela equipe no início da próxima sprint.

### Riscos ou impedimentos previstos

> Informe os principais fatores que podem dificultar o cumprimento da próxima meta. Caso nenhum risco tenha sido identificado, escreva “Nenhum risco identificado”.

**Riscos:**

Demora na resposta da cliente sobre as regras de estoque e esterilização, o que pode atrasar a criação das tabelas; para reduzir, a equipe começa pelo cadastro de produtos e de agendamentos, que já têm tabelas no banco.
