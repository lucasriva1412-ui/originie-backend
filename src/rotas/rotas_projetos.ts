
import { Router } from "express";

import {
  listarProjetos,
  consultarProjeto,
  criarProjeto,
  atualizarProjeto,
  excluirProjeto
} from "../projetos/controlador_projetos";

import {
  verificarToken,
  verificarAdministrador
} from "../verificacao/verificar_token";

const rotas = Router();

rotas.use(verificarToken);
rotas.use(verificarAdministrador);

rotas.get("/", listarProjetos);
rotas.get("/:id", consultarProjeto);
rotas.post("/", criarProjeto);
rotas.put("/:id", atualizarProjeto);
rotas.delete("/:id", excluirProjeto);

export default rotas;
