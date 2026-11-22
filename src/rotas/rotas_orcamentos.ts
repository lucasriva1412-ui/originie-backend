
import { Router } from "express";

import {
  listarOrcamentos,
  consultarOrcamento,
  criarOrcamento,
  atualizarOrcamento,
  excluirOrcamento,
  concluirOrcamento
} from "../orcamentos/controlador_orcamentos";

import {
  solicitarOrcamento
} from "../orcamentos/controlador_solicitacao_publica";

import {
  verificarToken,
  verificarAdministrador
} from "../verificacao/verificar_token";

const rotas = Router();

// Rota publica: visitante solicita orcamento sem login
rotas.post("/solicitar", solicitarOrcamento);

// Todas as rotas abaixo exigem administrador
rotas.use(verificarToken);
rotas.use(verificarAdministrador);

rotas.get("/", listarOrcamentos);
rotas.get("/:id", consultarOrcamento);
rotas.post("/", criarOrcamento);
rotas.put("/:id", atualizarOrcamento);
rotas.patch("/:id/concluir", concluirOrcamento);
rotas.delete("/:id", excluirOrcamento);

export default rotas;
