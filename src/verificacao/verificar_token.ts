
import { Request, Response, NextFunction } from "express";
import jwt, { JwtPayload } from "jsonwebtoken";

interface DadosToken extends JwtPayload {
  id: number;
  tipo: "usuario" | "admin";
}

export function verificarToken(
  req: Request,
  res: Response,
  next: NextFunction
): void {
  const autorizacao = req.headers.authorization;

  if (!autorizacao?.startsWith("Bearer ")) {
    res.status(401).json({
      mensagem: "Você precisa estar logado para acessar esta página."
    });
    return;
  }

  const token = autorizacao.slice(7);
  const chaveSecreta = process.env.JWT_SECRET;

  if (!chaveSecreta) {
    res.status(500).json({
      mensagem: "Chave de autenticação não configurada."
    });
    return;
  }

  try {
    const dados = jwt.verify(token, chaveSecreta, {
      algorithms: ["HS256"]
    }) as DadosToken;

    if (
      !Number.isInteger(dados.id) ||
      dados.id <= 0 ||
      (dados.tipo !== "usuario" && dados.tipo !== "admin")
    ) {
      res.status(401).json({
        mensagem: "Token inválido."
      });
      return;
    }

    req.usuario = {
      id: dados.id,
      tipo: dados.tipo
    };

    next();
  } catch {
    res.status(401).json({
      mensagem: "Sua sessão é inválida ou expirou."
    });
  }
}

export function verificarAdministrador(
  req: Request,
  res: Response,
  next: NextFunction
): void {
  if (!req.usuario) {
    res.status(401).json({
      mensagem: "Usuário não autenticado."
    });
    return;
  }

  if (req.usuario.tipo !== "admin") {
    res.status(403).json({
      mensagem: "Acesso permitido apenas para administradores."
    });
    return;
  }

  next();
}
