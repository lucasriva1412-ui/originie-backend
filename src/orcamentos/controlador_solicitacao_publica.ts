
import { Request, Response } from "express";
import { ResultSetHeader, RowDataPacket } from "mysql2";
import banco from "../configuracao/banco_dados";

interface RegistroId extends RowDataPacket {
  id: number;
}

interface ServicoAtivo extends RowDataPacket {
  id: number;
  nome: string;
}

export async function solicitarOrcamento(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const corpo = req.body;

    if (!corpo || typeof corpo !== "object" || Array.isArray(corpo)) {
      res.status(400).json({ mensagem: "Dados invalidos." });
      return;
    }

    const { nome, email, telefone, mensagem, servicosIds } = corpo;

    if (
      typeof nome !== "string" ||
      !nome.trim() ||
      nome.trim().length > 100 ||
      typeof email !== "string" ||
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim()) ||
      email.trim().length > 100 ||
      typeof mensagem !== "string" ||
      !mensagem.trim() ||
      mensagem.length > 5000 ||
      (telefone != null &&
        (typeof telefone !== "string" || telefone.length > 20))
    ) {
      res.status(400).json({
        mensagem: "Nome, email, telefone ou mensagem invalidos."
      });
      return;
    }

    if (
      !Array.isArray(servicosIds) ||
      servicosIds.length < 1 ||
      servicosIds.length > 25 ||
      !servicosIds.every(
        (id: unknown) =>
          typeof id === "number" &&
          Number.isSafeInteger(id) &&
          id > 0
      ) ||
      new Set(servicosIds).size !== servicosIds.length
    ) {
      res.status(400).json({
        mensagem: "Selecione servicos validos, sem repeticoes."
      });
      return;
    }

    const nomeLimpo = nome.trim();
    const emailLimpo = email.trim().toLowerCase();
    const telefoneLimpo = telefone?.trim() || null;
    const mensagemLimpa = mensagem.trim();

    const conexao = await banco.getConnection();

    try {
      await conexao.beginTransaction();

      const marcadores = servicosIds.map(() => "?").join(", ");

      const [servicos] = await conexao.execute<ServicoAtivo[]>(
        `SELECT id, nome
         FROM servicos_cat
         WHERE ativo = 1 AND id IN (${marcadores})`,
        servicosIds
      );

      if (servicos.length !== servicosIds.length) {
        await conexao.rollback();
        res.status(400).json({
          mensagem: "Um ou mais servicos nao estao disponiveis."
        });
        return;
      }

      const nomesPorId = new Map(
        servicos.map(servico => [servico.id, servico.nome])
      );

      const descricaoServicos = servicosIds
        .map(id => nomesPorId.get(id))
        .join(", ");

      if (descricaoServicos.length > 100) {
        await conexao.rollback();
        res.status(400).json({
          mensagem: "Selecione menos servicos para este orcamento."
        });
        return;
      }

      const [clientes] = await conexao.execute<RegistroId[]>(
        `SELECT id FROM clientes
         WHERE email = ?
         LIMIT 1 FOR UPDATE`,
        [emailLimpo]
      );

      let idCliente: number;

      if (clientes.length > 0) {
        idCliente = clientes[0].id;
      } else {
        const [novoCliente] = await conexao.execute<ResultSetHeader>(
          `INSERT INTO clientes (nome, email, telefone)
           VALUES (?, ?, ?)`,
          [nomeLimpo, emailLimpo, telefoneLimpo]
        );

        idCliente = novoCliente.insertId;
      }

      const [novoOrcamento] = await conexao.execute<ResultSetHeader>(
        `INSERT INTO contatos
         (id_cliente, servico, mensagem, status)
         VALUES (?, ?, ?, 'Pendente')`,
        [idCliente, descricaoServicos, mensagemLimpa]
      );

      for (const idServico of servicosIds) {
        await conexao.execute(
          `INSERT INTO servicos_interesse
           (id_contato, id_servico)
           VALUES (?, ?)`,
          [novoOrcamento.insertId, idServico]
        );
      }

      await conexao.commit();

      res.status(201).json({
        mensagem: "Solicitacao de orcamento enviada com sucesso.",
        id: novoOrcamento.insertId
      });

    } catch (erro) {
      await conexao.rollback();
      throw erro;
    } finally {
      conexao.release();
    }

  } catch (erro) {
    console.error("Erro na solicitacao publica:", erro);

    res.status(500).json({
      mensagem: "Nao foi possivel enviar o orcamento."
    });
  }
}
