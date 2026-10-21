
import "dotenv/config";
import express from "express";
import cors from "cors";

import banco from "./configuracao/banco_dados";
import rotasAutenticacao from "./rotas/rotas_autenticacao";

const app = express();

const origensPermitidas = (
  process.env.CORS_ORIGIN ?? "http://localhost:5173"
)
  .split(",")
  .map(origem => origem.trim());

app.use(cors({
  origin: origensPermitidas
}));

app.use(express.json({ limit: "100kb" }));

app.get("/api/health", async (_req, res) => {
  try {
    await banco.query("SELECT 1");

    res.json({
      api: "ok",
      banco: "ok"
    });
  } catch (erro) {
    console.error("Erro na conexão com MySQL:", erro);

    res.status(503).json({
      api: "ok",
      banco: "indisponivel"
    });
  }
});

app.use("/api/auth", rotasAutenticacao);

const PORT = Number(process.env.PORT ?? 3000);

app.listen(PORT, "0.0.0.0", () => {
  console.log(`API Originie iniciada na porta ${PORT}`);
});
