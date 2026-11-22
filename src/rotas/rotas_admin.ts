
import { Router } from "express";

import {
  consultarDashboard
} from "../admin/controlador_dashboard";

import {
  verificarToken,
  verificarAdministrador
} from "../verificacao/verificar_token";

const rotas = Router();

rotas.use(verificarToken);
rotas.use(verificarAdministrador);

rotas.get("/dashboard", consultarDashboard);

export default rotas;
