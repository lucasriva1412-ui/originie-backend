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

## Futuras skills (orientações)
Quando estas skills forem adicionadas, devem seguir e codificar os padrões reais do repositório:

- **`autenticacao-segura`**: emissão/verificação de JWT HS256 (`JWT_SECRET`, expiração/algoritmos conforme o código), tratamento de `Bearer`, `verificarToken`/`verificarAdministrador`, tipagem de `req.usuario`, hash/comparação de senhas com `bcrypt` e respostas de autenticação seguras sem vazar detalhes sensíveis.
- **`crud-originie`**: layout rota+controlador por domínio (`rotas_*` ↔ `controlador_*`), convenções da API em português, validação rigorosa de entradas, acesso MySQL parametrizado via pool compartilhado, rotas privadas protegidas por JWT, restrições administrativas, paginação em listagens e tratamento correto de 404 para recursos ausentes em serviços/clientes/orçamentos/projetos.
- **`docker-originie`**: modelo de build/execução do `Dockerfile` na raiz, serviços/redes do `originie_docker/docker-compose.yml`, configuração de proxy `/api` no Nginx, variáveis de ambiente para compose vs local e guardrails operacionais (sem remoção de volumes ou ações destrutivas no banco/stack sem autorização).

## Guardrails (obrigatórios)
- **Segredos/credenciais**: não abrir, ler, imprimir ou modificar arquivos `.env`; não divulgar credenciais, tokens, segredos ou hashes de senha. Não acessar arquivos de credenciais para concluir tarefas.
- **Banco de dados**: não executar operações destrutivas no banco; não alterar estrutura/dados de forma destrutiva; não fazer mudanças de estrutura sem autorização explícita.
- **Docker**: não remover volumes Docker nem realizar operações destrutivas no Docker.
- **Git**: não criar commits nem fazer push sem autorização explícita do usuário.
- **Escopo**: quando solicitado, limitar alterações ao `AGENTS.md`, salvo autorização explícita em contrário.
