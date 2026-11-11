
import { Router } from "express";

import {
  listarServicos,
  listarServicosPublicos,
  consultarServico,
  criarServico,
  atualizarServico,
  excluirServico
} from "../servicos/controlador_servicos";

import {
  verificarToken,
  verificarAdministrador
} from "../verificacao/verificar_token";

const rotas = Router();

// Rota pública: não exige login
rotas.get("/publicos", listarServicosPublicos);

// Rotas protegidas por JWT
rotas.use(verificarToken);

rotas.get("/", listarServicos);
rotas.get("/:id", consultarServico);

// Operações exclusivas de administradores
rotas.post(
  "/",
  verificarAdministrador,
  criarServico
);

rotas.put(
  "/:id",
  verificarAdministrador,
  atualizarServico
);

rotas.delete(
  "/:id",
  verificarAdministrador,
  excluirServico
);

export default rotas;
