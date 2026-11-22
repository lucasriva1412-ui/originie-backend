---
name: docker-originie
description: Usar ao configurar, revisar ou operar Docker Compose, Nginx, MySQL e integração local do backend Originie.
---

# Docker Originie

Use esta skill ao trabalhar com Docker, Docker Compose, Nginx, MySQL e ambiente local/integrado do projeto Originie.

## Quando usar

- Ajustar ou revisar `Dockerfile`, `originie_docker/docker-compose.yml`, `originie_docker/nginx.conf` ou fluxo de execução em containers.
- Diagnosticar integração entre frontend React, backend Express, MySQL e Nginx.
- Orientar inicialização/testes seguros do ambiente Docker.

## Regras obrigatórias

- **Organização do Docker Compose**: manter serviços claramente definidos (`nginx`, `mysql`, `backend`), com dependências, healthcheck e redes explícitas conforme `originie_docker/docker-compose.yml`. Não alterar estrutura/serviços sem necessidade e sem autorização quando houver impacto.
- **Integração React + backend + MySQL + Nginx**: Nginx expõe a borda e faz proxy de `/api/` para o backend; backend se comunica com MySQL pela rede interna. Frontend React consome a API via CORS/origem configurada (`CORS_ORIGIN`).
- **Proxy reverso**: Nginx deve encaminhar `/api/` para `backend:3000`, preservando headers relevantes (`Host`, `X-Real-IP`, `X-Forwarded-For`, `X-Forwarded-Proto`). Não expor MySQL desnecessariamente.
- **Redes internas e externas**: usar separação edge/interna; serviços que não precisam ser acessados externamente devem ficar restritos à rede interna. MySQL não deve ser publicado publicamente sem necessidade explícita.
- **Persistência com volumes**: dados do MySQL devem persistir em volume nomeado (ex.: `originie_mysql_data`). Não remover volumes, não usar `docker compose down -v`, `docker volume rm` ou ações equivalentes sem aprovação explícita.
- **Variáveis de ambiente**: segredos/configurações sensíveis via variáveis de ambiente (`MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`, `JWT_SECRET`, `CORS_ORIGIN`, etc.). Nunca hardcode de credenciais; não ler/exibir `.env`.
- **Banco e dados**: não excluir bancos, tabelas, volumes ou dados sem aprovação. Não executar comandos destrutivos no MySQL/Docker.
- **Testes e inicialização seguros**: antes de subir, conferir variáveis necessárias e impacto. Preferir `docker compose up -d --build` apenas quando apropriado; validar com logs/healthcheck e `GET /api/health`. Em mudanças de Dockerfile, considerar `npm run build`/build da imagem.

## Padrões do projeto

- Compose: `originie_docker/docker-compose.yml`; Nginx: `originie_docker/nginx.conf`.
- Backend no container: `DB_HOST=mysql`, `DB_PORT=3306`; local pode usar host/porta diferentes.
- Dockerfile raiz copia `package*.json`, `tsconfig.json`, `src/` e compila para `dist/`.
- Não modificar Docker Compose ou dados do MySQL sem autorização explícita do usuário.
