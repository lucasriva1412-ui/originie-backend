
import { Router } from "express";

import {
  listarServicos,
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

rotas.use(verificarToken);

rotas.get("/", listarServicos);

rotas.get("/:id", consultarServico);

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
