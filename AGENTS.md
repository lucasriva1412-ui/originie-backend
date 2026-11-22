# AGENTS.md

## Projeto e arquitetura
- **Backend Originie Audiovisual**: API REST em Express 4 + TypeScript (`strict`, módulos `node16`), consumida por um **frontend React** via HTTP.
- **Entrada**: `src/server.ts` (`import "dotenv/config"`), escuta em `0.0.0.0` na `PORT` (padrão 3000), monta rotas em `/api/*`; `GET /api/health` verifica a conexão com o banco (`SELECT 1`).
- **Estrutura**: rotas em `src/rotas/` (ex.: `rotas_autenticacao.ts`, `rotas_servicos.ts`, `rotas_clientes.ts`, `rotas_orcamentos.ts`, `rotas_projetos.ts`); handlers/controladores de domínio em `src/<dominio>/controlador_*.ts` (`cadastro`, `clientes`, `orcamentos`, `projetos`, `servicos`); banco/autenticação/configuração compartilhados em `src/configuracao/`, `src/verificacao/`; tipos em `src/tipos/` (ex.: `express.d.ts` amplia `req.usuario`).
- **Dados**: MySQL acessado via pool `mysql2/promise` em `src/configuracao/banco_dados.ts`; dump de referência de schema/procedures em `banco_dados/originie.sql` (compatível com MySQL). Preservar a compatibilidade com o schema/comportamento MySQL existente.
- **Autenticação**: JWT HS256 assinado/verificado com `JWT_SECRET`, senhas com hash via `bcrypt`; middleware de token em `src/verificacao/verificar_token.ts` espera `Authorization: Bearer <token>` e define `req.usuario {id, tipo}`.
- **Integração com o frontend**: CORS controlado por `CORS_ORIGIN` (origens separadas por vírgula, padrão `http://localhost:5173` para desenvolvimento local em React). Manter contratos da API e caminhos `/api/*` estáveis, salvo atualização sincronizada do frontend.

## Docker / Nginx
- `originie_docker/docker-compose.yml`: `nginx` (`127.0.0.1:8080:80`, monta `originie_docker/nginx.conf`, faz proxy de `/api/` para `backend:3000`), `mysql:8.0` e `backend` (build a partir da raiz do repositório via `Dockerfile`, `node:22-alpine`, executa `dist/server.js` compilado).
- Redes do compose: edge + interna (MySQL na rede interna); o backend usa `DB_HOST=mysql`, `DB_PORT=3306` dentro do container. O compose exige variáveis de ambiente (ex.: `MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`, `JWT_SECRET`, `CORS_ORIGIN`).
- Host/porta do banco local (fora do Docker) podem diferir do compose (seguir os padrões de `banco_dados.ts` e o ambiente local). Não remover volumes Docker nem realizar operações destrutivas na stack sem autorização explícita.

## Comandos
- Instalar: `npm install`
- Desenvolvimento (watch): `npm run dev` (`tsx watch src/server.ts`)
- Build: `npm run build` (`tsc` → `dist/`)
- Iniciar em produção: `npm start` (`node dist/server.js`)
- Não há scripts de lint/teste no `package.json`; para verificação de tipos, usar `npx tsc --noEmit` quando necessário.
- **Após alterações no código**, executar `npm run build` para garantir que o backend continua compilando.

## Regras de CRUD: serviços, clientes, orçamentos e projetos finalizados
Aplicar estas regras ao criar ou modificar endpoints/handlers dos quatro domínios (`src/servicos`, `src/clientes`, `src/orcamentos`, `src/projetos` e seus `rotas_*`):

- **Validação de entradas**: validar e normalizar todas as entradas da requisição (body/params/query) com respostas 4xx claras; rejeitar tipos/intervalos inválidos e campos obrigatórios ausentes. Manter mensagens ao usuário consistentes com o estilo em português já existente.
- **Segurança SQL**: usar **apenas consultas parametrizadas/prepared statements** (placeholders `?` com array de valores); nunca interpolar entrada do usuário em SQL.
- **Autenticação**: proteger rotas privadas com `verificarToken` (JWT). Endpoints públicos devem ser explicitamente intencionais.
- **Autorização**: exigir `verificarAdministrador` (ou checagem de papel equivalente) em operações restritas/administrativas; nunca confiar apenas em verificações no cliente.
- **Paginação**: endpoints de listagem devem suportar paginação (ex.: `limit`/`offset` ou página/tamanho) com padrões sensatos e limites máximos para evitar leituras ilimitadas.
- **Recursos inexistentes**: tratar entidades não encontradas explicitamente (ex.: 404) em leitura/atualização/exclusão por id; não retornar sucesso para alvos inexistentes.
- **Compatibilidade com o banco**: **não** alterar estrutura/schema/migrations/procedures nem a semântica de `banco_dados/originie.sql` sem autorização explícita; preservar compatibilidade com o banco MySQL existente.

## Skills do OpenCode
As skills do projeto ficam em `.opencode/skills/<nome>/SKILL.md` e devem ser usadas conforme o contexto:

- **`autenticacao-segura`** (`.opencode/skills/autenticacao-segura/SKILL.md`): usar ao implementar ou revisar cadastro/login, validação de e-mail/CPF, bcrypt, JWT, controle de acesso admin/usuário comum, edição do próprio perfil, imutabilidade de e-mail e tratamento seguro de erros de autenticação.
- **`crud-originie`** (`.opencode/skills/crud-originie/SKILL.md`): usar ao criar ou alterar CRUDs de serviços, clientes, orçamentos e projetos finalizados; cobre validação, SQL parametrizado (`mysql2/promise`), paginação, códigos HTTP (400/401/403/404/409), relacionamentos, transações, proteção JWT/admin e proibição de exclusões destrutivas sem autorização.
- **`docker-originie`** (`.opencode/skills/docker-originie/SKILL.md`): usar ao configurar/revisar Docker Compose, Dockerfile, Nginx, MySQL e integração React/backend/MySQL/Nginx; cobre proxy reverso, redes internas/externas, volumes, variáveis de ambiente e procedimentos seguros de inicialização/teste, sem remover volumes/bancos sem aprovação.

## Guardrails (obrigatórios)
- **Segredos/credenciais**: não abrir, ler, imprimir ou modificar arquivos `.env`; não divulgar credenciais, tokens, segredos ou hashes de senha. Não acessar arquivos de credenciais para concluir tarefas.
- **Banco de dados**: não executar operações destrutivas no banco; não alterar estrutura/dados de forma destrutiva; não fazer mudanças de estrutura sem autorização explícita.
- **Docker**: não remover volumes Docker nem realizar operações destrutivas no Docker.
- **Git**: não criar commits nem fazer push sem autorização explícita do usuário.
- **Escopo**: quando solicitado, limitar alterações ao `AGENTS.md`, salvo autorização explícita em contrário.
