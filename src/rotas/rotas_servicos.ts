
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

// Área pública
rotas.get("/publicos", listarServicosPublicos);

// Área administrativa
rotas.use(verificarToken);
rotas.use(verificarAdministrador);

rotas.get("/", listarServicos);
rotas.get("/:id", consultarServico);
rotas.post("/", criarServico);
rotas.put("/:id", atualizarServico);
rotas.delete("/:id", excluirServico);

export default rotas;
