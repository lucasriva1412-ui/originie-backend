
import { Router } from "express";

import {
  listarClientes,
  consultarCliente,
  criarCliente,
  atualizarCliente,
  excluirCliente,
  listarOrcamentosCliente
} from "../clientes/controlador_clientes";

import {
  verificarToken,
  verificarAdministrador
} from "../verificacao/verificar_token";

const rotas = Router();

rotas.use(verificarToken);
rotas.use(verificarAdministrador);

rotas.get("/", listarClientes);

rotas.get("/:id/orcamentos", listarOrcamentosCliente);

rotas.get("/:id", consultarCliente);

rotas.post("/", criarCliente);

rotas.put("/:id", atualizarCliente);

rotas.delete("/:id", excluirCliente);

export default rotas;
