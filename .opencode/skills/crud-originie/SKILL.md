---
name: crud-originie
description: Usar ao criar ou alterar CRUDs de serviços, clientes, orçamentos e projetos finalizados no backend Originie.
---

# CRUD Originie

Use esta skill ao implementar, revisar ou estender os CRUDs de **serviços**, **clientes**, **orçamentos** e **projetos finalizados**.

## Quando usar

- Criar/alterar endpoints ou controladores em `src/servicos`, `src/clientes`, `src/orcamentos`, `src/projetos`.
- Ajustar rotas em `src/rotas/rotas_servicos.ts`, `rotas_clientes.ts`, `rotas_orcamentos.ts`, `rotas_projetos.ts`.
- Implementar listagem, detalhe, criação, atualização ou exclusão com regras de negócio e banco.

## Regras obrigatórias

- **Domínios cobertos**: serviços, clientes, orçamentos e projetos finalizados. Manter o padrão do projeto: rota em `src/rotas/rotas_<dominio>.ts` e lógica em `src/<dominio>/controlador_*.ts`.
- **Validação**: validar campos obrigatórios, tipos, formatos e limites antes de acessar o banco. Retornar `400` com mensagem clara em português para entrada inválida.
- **SQL parametrizado**: usar `mysql2/promise` com consultas parametrizadas (`?` e array de valores). Nunca concatenar/interpolar entrada do usuário em SQL.
- **Paginação**: toda listagem deve suportar paginação (`limit`/`offset` ou página/tamanho), com padrão sensato e limite máximo para evitar leitura ilimitada. Retornar metadados úteis quando aplicável (total, página, limite).
- **Status HTTP**: usar códigos corretos: `400` entrada inválida, `401` não autenticado, `403` sem permissão, `404` recurso inexistente, `409` conflito/regra de negócio duplicidade ou estado inválido. Sucesso: `200`, `201` ou `204` conforme o caso.
- **Relacionamentos**: respeitar relações entre clientes, serviços, orçamentos e projetos. Validar existência de IDs relacionados antes de criar/vincular; tratar FK/conflitos com erro adequado (`400`/`404`/`409`).
- **Transações**: operações que alteram várias tabelas ou exigem consistência atômica devem usar transação (`beginTransaction`, `commit`, `rollback`) com conexão dedicada do pool e liberação no `finally`.
- **Autenticação e autorização**: rotas privadas protegidas por `verificarToken`. Rotas administrativas/restritas protegidas por `verificarAdministrador`. Não confiar em verificação apenas no frontend.
- **Recursos inexistentes**: em buscar/atualizar/excluir por id, verificar se o registro existe; retornar `404` quando não existir. Não reportar sucesso para alvo inexistente.
- **Exclusões destrutivas**: não implementar ou executar exclusões destrutivas sem autorização explícita. Preferir desativação/soft-delete quando o domínio já adotar esse padrão; ao excluir, confirmar impacto e dependências.

## Padrões do projeto

- Banco: pool em `src/configuracao/banco_dados.ts`.
- Mensagens e nomenclatura em português, consistentes com controladores existentes.
- Preservar compatibilidade com o MySQL existente; não alterar schema/estrutura sem autorização explícita.
- Após alterações, executar `npm run build`.
