
import { Request, Response } from "express";
import { RowDataPacket, ResultSetHeader } from "mysql2";
import bcrypt from "bcrypt";
import jwt from "jsonwebtoken";

import banco from "../configuracao/banco_dados";
import { UsuarioCadastro } from "./usuario_cadastro";
import { avaliarSenha } from "./validacoes_cadastro";

interface Usuario extends RowDataPacket {
  id: number;
  nome: string;
  cpf: string | null;
  email: string;
  senha: string;
  tipo: "usuario" | "admin";
}

function dadosPublicos(usuario: Usuario) {
  return {
    id: usuario.id,
    nome: usuario.nome,
    cpf: usuario.cpf,
    email: usuario.email,
    tipo: usuario.tipo
  };
}

function mensagemErroCadastro(erro: unknown): string | null {
  if (
    erro instanceof Error &&
    [
      "Nome inválido.",
      "O nome deve ter entre 3 e 100 caracteres.",
      "CPF inválido.",
      "E-mail inválido."
    ].includes(erro.message)
  ) {
    return erro.message;
  }

  return null;
}

function ehErroDuplicidade(erro: unknown): boolean {
  return (
    typeof erro === "object" &&
    erro !== null &&
    "code" in erro &&
    erro.code === "ER_DUP_ENTRY"
  );
}

export async function cadastrar(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const { nome, cpf, email, senha } = req.body ?? {};

    if (
      typeof nome !== "string" ||
      typeof cpf !== "string" ||
      typeof email !== "string" ||
      typeof senha !== "string"
    ) {
      res.status(400).json({
        mensagem: "Nome, CPF, e-mail e senha são obrigatórios."
      });
      return;
    }

    const usuarioCadastro = new UsuarioCadastro(
      nome,
      cpf,
      email
    );

    const avaliacaoSenha = avaliarSenha(senha);

    if (!avaliacaoSenha.valida) {
      res.status(400).json({
        mensagem: avaliacaoSenha.mensagem,
        nivelSenha: avaliacaoSenha.nivel
      });
      return;
    }

    const dados = usuarioCadastro.dadosParaCadastro;

    const senhaCriptografada = await bcrypt.hash(senha, 12);

    const [resultado] = await banco.execute<ResultSetHeader>(
      `INSERT INTO usuarios (nome, cpf, email, senha, tipo)
       VALUES (?, ?, ?, ?, 'usuario')`,
      [
        dados.nome,
        dados.cpf,
        dados.email,
        senhaCriptografada
      ]
    );

    res.status(201).json({
      mensagem: "Cadastro realizado com sucesso.",
      nivelSenha: avaliacaoSenha.nivel,
      usuario: {
        id: resultado.insertId,
        nome: dados.nome,
        cpf: dados.cpf,
        email: dados.email,
        tipo: "usuario"
      }
    });
  } catch (erro) {
    const mensagemValidacao = mensagemErroCadastro(erro);

    if (mensagemValidacao) {
      res.status(400).json({
        mensagem: mensagemValidacao
      });
      return;
    }

    if (ehErroDuplicidade(erro)) {
      res.status(409).json({
        mensagem: "E-mail ou CPF já cadastrado."
      });
      return;
    }

    console.error("Erro ao cadastrar:", erro);

    res.status(500).json({
      mensagem: "Erro interno ao realizar cadastro."
    });
  }
}

export async function entrar(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const { email, senha } = req.body ?? {};

    if (
      typeof email !== "string" ||
      typeof senha !== "string"
    ) {
      res.status(400).json({
        mensagem: "Informe o e-mail e a senha."
      });
      return;
    }

    const [usuarios] = await banco.execute<Usuario[]>(
      `SELECT id, nome, cpf, email, senha, tipo
       FROM usuarios
       WHERE email = ?
       LIMIT 1`,
      [email.trim().toLowerCase()]
    );

    const usuario = usuarios[0];

    if (
      !usuario ||
      !(await bcrypt.compare(senha, usuario.senha))
    ) {
      res.status(401).json({
        mensagem: "E-mail ou senha incorretos."
      });
      return;
    }

    const chaveSecreta = process.env.JWT_SECRET;

    if (!chaveSecreta) {
      res.status(500).json({
        mensagem: "Autenticação não configurada."
      });
      return;
    }

    const token = jwt.sign(
      {
        id: usuario.id,
        tipo: usuario.tipo
      },
      chaveSecreta,
      {
        expiresIn: "2h",
        algorithm: "HS256"
      }
    );

    res.json({
      mensagem: "Login realizado com sucesso.",
      token,
      usuario: dadosPublicos(usuario)
    });
  } catch (erro) {
    console.error("Erro ao entrar:", erro);

    res.status(500).json({
      mensagem: "Erro interno ao realizar login."
    });
  }
}

export async function consultarPerfil(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const [usuarios] = await banco.execute<Usuario[]>(
      `SELECT id, nome, cpf, email, tipo
       FROM usuarios
       WHERE id = ?
       LIMIT 1`,
      [req.usuario!.id]
    );

    if (!usuarios.length) {
      res.status(404).json({
        mensagem: "Usuário não encontrado."
      });
      return;
    }

    res.json({
      usuario: dadosPublicos(usuarios[0])
    });
  } catch (erro) {
    console.error("Erro ao consultar perfil:", erro);

    res.status(500).json({
      mensagem: "Erro interno ao consultar perfil."
    });
  }
}


export async function atualizarPerfil(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const {
      nome,
      cpf,
      senhaAtual,
      novaSenha
    } = req.body ?? {};

    if (
      Object.prototype.hasOwnProperty.call(
        req.body ?? {},
        "email"
      )
    ) {
      res.status(400).json({
        mensagem: "Não é permitido alterar o e-mail."
      });
      return;
    }

    if (
      typeof nome !== "string" ||
      typeof cpf !== "string" ||
      typeof senhaAtual !== "string" ||
      typeof novaSenha !== "string"
    ) {
      res.status(400).json({
        mensagem:
          "Nome, CPF, senha atual e nova senha são obrigatórios."
      });
      return;
    }

    const [usuarios] = await banco.execute<Usuario[]>(
      `SELECT id, nome, cpf, email, senha, tipo
       FROM usuarios
       WHERE id = ?
       LIMIT 1`,
      [req.usuario!.id]
    );

    const usuario = usuarios[0];

    if (!usuario) {
      res.status(404).json({
        mensagem: "Usuário não encontrado."
      });
      return;
    }

    const senhaCorreta = await bcrypt.compare(
      senhaAtual,
      usuario.senha
    );

    if (!senhaCorreta) {
      res.status(401).json({
        mensagem: "Senha atual incorreta."
      });
      return;
    }

    const usuarioCadastro = new UsuarioCadastro(
      nome,
      cpf,
      usuario.email
    );

    const avaliacaoSenha = avaliarSenha(novaSenha);

    if (!avaliacaoSenha.valida) {
      res.status(400).json({
        mensagem: avaliacaoSenha.mensagem,
        nivelSenha: avaliacaoSenha.nivel
      });
      return;
    }

    const dados = usuarioCadastro.dadosParaCadastro;

    const senhaCriptografada = await bcrypt.hash(
      novaSenha,
      12
    );

    await banco.execute(
      `UPDATE usuarios
       SET nome = ?, cpf = ?, senha = ?
       WHERE id = ?`,
      [
        dados.nome,
        dados.cpf,
        senhaCriptografada,
        req.usuario!.id
      ]
    );

    const [usuariosAtualizados] =
      await banco.execute<Usuario[]>(
        `SELECT id, nome, cpf, email, tipo
         FROM usuarios
         WHERE id = ?
         LIMIT 1`,
        [req.usuario!.id]
      );

    res.json({
      mensagem: "Perfil atualizado com sucesso.",
      nivelSenha: avaliacaoSenha.nivel,
      usuario: dadosPublicos(usuariosAtualizados[0])
    });
  } catch (erro) {
    const mensagemValidacao = mensagemErroCadastro(erro);

    if (mensagemValidacao) {
      res.status(400).json({
        mensagem: mensagemValidacao
      });
      return;
    }

    if (ehErroDuplicidade(erro)) {
      res.status(409).json({
        mensagem: "CPF já cadastrado."
      });
      return;
    }

    console.error("Erro ao atualizar perfil:", erro);

    res.status(500).json({
      mensagem: "Erro interno ao atualizar perfil."
    });
  }
}
