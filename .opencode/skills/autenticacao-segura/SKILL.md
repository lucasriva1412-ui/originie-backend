---
name: autenticacao-segura
description: Usar ao implementar ou revisar cadastro, login, JWT, bcrypt, perfis e controle de acesso no backend Originie.
---

# Autenticação Segura

Use esta skill ao trabalhar com autenticação, autorização, perfil de usuário e segurança de credenciais no backend Originie.

## Quando usar

- Criar ou alterar cadastro, login, perfil ou middlewares de autenticação/autorização.
- Revisar uso de JWT, bcrypt, validação de identidade e tratamento de erros de autenticação.
- Ajustar regras de acesso entre administrador e usuário comum.

## Regras obrigatórias

- **Cadastro e login**: validar `email` e `CPF` antes de consultar/persistir; rejeitar entradas inválidas com `400` e mensagens claras em português.
- **Senhas com bcrypt**: nunca armazenar ou comparar senha em texto puro. Usar `bcrypt` para hash no cadastro/alteração e `bcrypt.compare` no login. Não expor hash de senha em respostas, logs ou erros.
- **JWT**: assinar e verificar tokens com `JWT_SECRET`, algoritmo esperado (`HS256`, conforme padrão do projeto) e expiração definida. Ler token do header `Authorization: Bearer <token>`; validar payload (`id`, `tipo`) antes de confiar nele.
- **Controle de acesso**: rotas privadas exigem `verificarToken`. Operações restritas/administrativas exigem `verificarAdministrador` (ou checagem equivalente de `req.usuario.tipo`). Usuário comum não pode acessar recursos administrativos.
- **Perfil próprio**: usuário autenticado só pode consultar/editar o próprio perfil. Usar a identidade do token (`req.usuario.id`), nunca id confiado do cliente para ações de perfil próprio.
- **E-mail imutável**: proibir alteração de e-mail em atualização de perfil; retornar `400` quando o cliente tentar alterar.
- **Erros de autenticação**: retornar códigos adequados (`400` entrada inválida, `401` não autenticado/token inválido ou expirado, `403` sem permissão). Não vazar se o usuário existe além do necessário, não expor stack traces, segredos, tokens ou detalhes internos.
- **Segredos e credenciais**: nunca expor senhas, hashes, tokens, `JWT_SECRET` ou credenciais em código, logs, respostas, exemplos ou documentação. Não ler/exibir arquivos `.env`.

## Padrões do projeto

- Middleware: `src/verificacao/verificar_token.ts`.
- Tipagem de usuário autenticado: `src/tipos/express.d.ts` (`req.usuario`).
- Controlador de cadastro/auth: `src/cadastro/controlador_cadastro.ts`.
- Rotas de autenticação: `src/rotas/rotas_autenticacao.ts`.
- Consultas MySQL devem ser parametrizadas (`mysql2/promise`, placeholders `?`).
