# Status Report da Sprint 4

## 1. Identificação

- **Projeto:** GlowBella
- **Número da Sprint:** 4
- **Período:** [Inserir Período]
- **Integrantes:** Pedro (Product Owner), Amanda (Scrum Master), Enzo, Victor de Leon, Rafael, Yuri e Davi (Desenvolvedores)
- **Scrum Master da Sprint:** [Nome do Scrum Master]

### Meta da Sprint

> Informe a meta definida no planejamento da sprint que está sendo finalizada.

**Meta:**

Realizar a reestruturação e refatoração do banco de dados e atualizar o front-end dos módulos de Esterilização, Produtos e Estoque.

---

## 2. Resultado da Sprint

### Situação da meta

- [X] Alcançada
- [ ] Parcialmente alcançada
- [ ] Não alcançada

### Resultado alcançado

> Descreva brevemente quais funcionalidades ou resultados foram concluídos e estão funcionando ao final da sprint.

**Resultado:**

- Estruturação e refatoração completa do banco de dados para melhoria de desempenho, integridade relacional e suporte às novas regras do sistema.
- Atualização e refatoração da interface (front-end) da página de **Esterilização**.
- Atualização e melhorias no front-end da página de **Produtos**.
- Atualização visual e de usabilidade do front-end da página de **Estoque**.
- Integração das novas alterações no ambiente de desenvolvimento e testes.

### Principal dificuldade ou impedimento

> Informe somente a dificuldade ou o impedimento que mais afetou a sprint. Caso não tenha ocorrido, escreva “Nenhum impedimento relevante”.

**Dificuldade ou impedimento:**

Adaptação do código existente à nova estrutura refatorada do banco de dados sem quebrar as rotas e funções já implementadas.

---

## 3. Itens planejados e situação final

**Utilize apenas os seguintes status:**

- Concluído
- Em andamento
- Não iniciado
- Bloqueado

| User Story ou item | Responsável(is) | Situação final | Observação |
|---|---|---|---|
| Estruturação do banco de dados |  | Concluído | Novas tabelas e relacionamentos organizados |
| Refatoração do banco anterior |  | Concluído | Ajustes de tipos de dados, chaves estrangeiras e limpeza de campos obsoletos |
| Front-end: Página de Esterilização |  | Concluído | Interface atualizada e alinhada ao fluxo de esterilização |
| Front-end: Página de Produtos |  | Concluído | Atualizações na listagem e elementos visuais da tela |
| Front-end: Página de Estoque |  | Concluído | Ajustes na interface de movimentação e visualização do estoque |

---

## 4. Evidências e qualidade

### Evidências

- **Repositório:** https://github.com/nozari-enzo/estoque-agenda-glowbella
- **Quadro Kanban:** https://trello.com/invite/b/6aabeabdfff125fed81d0d55/ATTIe411ab9da7c4a26ee0ecf4b85fd53eafD0E7022E/glow-bella
- **Deploy ou instruções para executar o projeto:** instruções atualizadas no README
- **Outras evidências, se necessárias:**
  - Script de migração do banco refatorado
  - PRs de integração do front-end na branch `dev`

### Checklist de qualidade

- [X] Os itens marcados como concluídos atendem aos critérios de aceite.
- [X] As funcionalidades entregues foram testadas pela equipe.
- [X] O código atualizado está no repositório oficial.
- [X] Os problemas conhecidos estão registrados no Kanban ou no repositório.

### Problemas conhecidos

> Informe os problemas que permanecem no incremento. Caso não tenham sido identificados, escreva “Nenhum problema conhecido”.

**Registro:**

- Validação final de integração do back-end com o front-end refatorado em alguns fluxos secundários de estoque.

---

## 5. Retrospectiva da Sprint

### Manter

> O que funcionou bem e deve continuar?

**Registro:**

Trabalho em branches padronizadas e alinhamento rápido entre modelagem de banco e design de interface.

### Melhorar

> O que precisa mudar na próxima sprint?

**Registro:**

Sincronização entre as mudanças de schema no banco de dados e os scripts locais dos desenvolvedores para evitar falhas durante os testes.

### Agir

> Qual ação concreta a equipe adotará na próxima sprint?

**Ação:**

Atualizar o script de seed/migrations e avisar a equipe imediatamente a cada alteração efetuada na estrutura do banco.

---

## 6. Planejamento da próxima Sprint

### Meta da próxima Sprint

> Escreva um resultado claro e verificável que a equipe pretende alcançar.

**Meta:**

Conectar as páginas de front-end atualizadas (Esterilização, Produtos e Estoque) às rotas e consultas da nova estrutura do banco de dados.

### Itens inicialmente selecionados

| User Story ou item | Responsável(is), se definido(s) | Resultado esperado |
|---|---|---|
| Integração Back-End / BD: Produtos |  | Realizar CRUD completo consumindo o novo banco |
| Integração Back-End / BD: Estoque |  | Registrar entradas e saídas no banco refatorado |
| Integração Back-End / BD: Esterilização |  | Persistir o ciclo e status de esterilização |

### Riscos ou impedimentos previstos

> Informe os principais fatores que podem dificultar me a próxima meta. Caso nenhum risco tenha sido identificado, escreva “Nenhum risco identificado”.

**Riscos:**

Incompatibilidade de tipos de dados entre os inputs das telas e a nova estrutura do banco de dados.