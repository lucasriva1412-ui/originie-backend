
import { Router } from "express";

import {
  cadastrar,
  entrar,
  consultarPerfil,
  atualizarPerfil
} from "../cadastro/controlador_cadastro";

import {
  verificarToken,
  verificarAdministrador
} from "../verificacao/verificar_token";

const rotas = Router();

rotas.post("/cadastro", cadastrar);

rotas.post("/login", entrar);

rotas.get("/perfil", verificarToken, consultarPerfil);

rotas.put("/perfil", verificarToken, atualizarPerfil);

rotas.get(
  "/admin/teste",
  verificarToken,
  verificarAdministrador,
  (_req, res) => {
    res.json({
      mensagem: "Acesso de administrador autorizado."
    });
  }
);

export default rotas;
