
import { Request, Response } from "express";
import { ResultSetHeader, RowDataPacket } from "mysql2";
import banco from "../configuracao/banco_dados";
import { validarEmail } from "../cadastro/validacoes_cadastro";

interface Cliente extends RowDataPacket {
  id: number;
  nome: string;
  email: string;
  telefone: string | null;
}

interface Orcamento extends RowDataPacket {
  id: number;
  id_cliente: number;
  servico: string | null;
  mensagem: string;
  criado_em: Date;
  status: string;
}

interface Total extends RowDataPacket {
  total: number;
}

interface ClienteId extends RowDataPacket {
  id: number;
}

class ErroValidacao extends Error {}

function validarId(valor: string): number | null {
  if (!/^[1-9]\d*$/.test(valor)) {
    return null;
  }

  const id = Number(valor);

  return Number.isSafeInteger(id) ? id : null;
}

function validarPaginacao(req: Request) {
  const paginaTexto = req.query.pagina ?? "1";
  const limiteTexto = req.query.limite ?? "10";

  if (
    typeof paginaTexto !== "string" ||
    typeof limiteTexto !== "string" ||
    !/^[1-9]\d*$/.test(paginaTexto) ||
    !/^[1-9]\d*$/.test(limiteTexto)
  ) {
    throw new ErroValidacao("Paginação inválida.");
  }

  const pagina = Number(paginaTexto);
  const limite = Number(limiteTexto);

  if (
    !Number.isSafeInteger(pagina) ||
    !Number.isSafeInteger(limite) ||
    limite > 100
  ) {
    throw new ErroValidacao(
      "Página inválida ou limite superior a 100."
    );
  }

  const offset = (pagina - 1) * limite;

  if (!Number.isSafeInteger(offset)) {
    throw new ErroValidacao("Página fora do intervalo.");
  }

  return { pagina, limite, offset };
}

function validarDadosCliente(corpo: unknown) {
  if (
    !corpo ||
    typeof corpo !== "object" ||
    Array.isArray(corpo)
  ) {
    throw new ErroValidacao("Informe os dados do cliente.");
  }

  const dados = corpo as Record<string, unknown>;

  const nome = dados.nome;
  const email = dados.email;
  const telefone = dados.telefone;

  if (typeof nome !== "string") {
    throw new ErroValidacao("Nome obrigatório.");
  }

  const nomeLimpo = nome.trim();

  if (
    nomeLimpo.length < 3 ||
    nomeLimpo.length > 100
  ) {
    throw new ErroValidacao(
      "O nome deve ter entre 3 e 100 caracteres."
    );
  }

  if (typeof email !== "string") {
    throw new ErroValidacao("E-mail obrigatório.");
  }

  const emailLimpo = email.trim().toLowerCase();

  if (
    emailLimpo.length > 100 ||
    !validarEmail(emailLimpo)
  ) {
    throw new ErroValidacao("E-mail inválido.");
  }

  let telefoneLimpo: string | null = null;

  if (telefone !== undefined && telefone !== null) {
    if (typeof telefone !== "string") {
      throw new ErroValidacao("Telefone inválido.");
    }

    telefoneLimpo = telefone.trim() || null;

    if (
      telefoneLimpo !== null &&
      telefoneLimpo.length > 20
    ) {
      throw new ErroValidacao(
        "O telefone deve ter no máximo 20 caracteres."
      );
    }
  }

  return {
    nome: nomeLimpo,
    email: emailLimpo,
    telefone: telefoneLimpo
  };
}

async function buscarCliente(id: number): Promise<Cliente | null> {
  const [clientes] = await banco.execute<Cliente[]>(
    `SELECT id, nome, email, telefone
     FROM clientes
     WHERE id = ?
     LIMIT 1`,
    [id]
  );

  return clientes[0] ?? null;
}

async function emailEmUso(
  email: string,
  ignorarId?: number
): Promise<boolean> {
  let sql = `
    SELECT id
    FROM clientes
    WHERE email = ?
  `;

  const parametros: Array<string | number> = [email];

  if (ignorarId !== undefined) {
    sql += " AND id <> ?";
    parametros.push(ignorarId);
  }

  sql += " LIMIT 1";

  const [clientes] = await banco.execute<ClienteId[]>(
    sql,
    parametros
  );

  return clientes.length > 0;
}

function tratarErro(res: Response, erro: unknown): void {
  if (erro instanceof ErroValidacao) {
    res.status(400).json({
      mensagem: erro.message
    });
    return;
  }

  const codigo =
    typeof erro === "object" &&
    erro !== null &&
    "code" in erro
      ? erro.code
      : null;

  if (codigo === "ER_DUP_ENTRY") {
    res.status(409).json({
      mensagem: "E-mail já cadastrado."
    });
    return;
  }

  if (
    codigo === "ER_ROW_IS_REFERENCED_2" ||
    codigo === "ER_ROW_IS_REFERENCED"
  ) {
    res.status(409).json({
      mensagem:
        "Não é possível excluir um cliente que possui registros relacionados."
    });
    return;
  }

  console.error("Erro no CRUD de clientes:", erro);

  res.status(500).json({
    mensagem: "Erro interno ao processar cliente."
  });
}

export async function listarClientes(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const { pagina, limite, offset } = validarPaginacao(req);

    const [totais] = await banco.execute<Total[]>(
      "SELECT COUNT(*) AS total FROM clientes"
    );

    const [clientes] = await banco.execute<Cliente[]>(
      `SELECT id, nome, email, telefone
       FROM clientes
       ORDER BY id DESC
       LIMIT ? OFFSET ?`,
      [limite, offset]
    );

    const total = totais[0].total;

    res.json({
      pagina,
      limite,
      total,
      totalPaginas: Math.ceil(total / limite),
      dados: clientes
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function consultarCliente(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    if (id === null) {
      res.status(400).json({
        mensagem: "ID inválido."
      });
      return;
    }

    const cliente = await buscarCliente(id);

    if (!cliente) {
      res.status(404).json({
        mensagem: "Cliente não encontrado."
      });
      return;
    }

    const [totais] = await banco.execute<Total[]>(
      `SELECT COUNT(*) AS total
       FROM contatos
       WHERE id_cliente = ?`,
      [id]
    );

    res.json({
      cliente,
      totalOrcamentos: totais[0].total
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function criarCliente(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const dados = validarDadosCliente(req.body);

    if (await emailEmUso(dados.email)) {
      res.status(409).json({
        mensagem: "E-mail já cadastrado."
      });
      return;
    }

    const [resultado] = await banco.execute<ResultSetHeader>(
      `INSERT INTO clientes
       (nome, email, telefone)
       VALUES (?, ?, ?)`,
      [
        dados.nome,
        dados.email,
        dados.telefone
      ]
    );

    res.status(201).json({
      mensagem: "Cliente cadastrado com sucesso.",
      cliente: {
        id: resultado.insertId,
        ...dados
      }
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function atualizarCliente(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    if (id === null) {
      res.status(400).json({
        mensagem: "ID inválido."
      });
      return;
    }

    const clienteExistente = await buscarCliente(id);

    if (!clienteExistente) {
      res.status(404).json({
        mensagem: "Cliente não encontrado."
      });
      return;
    }

    const dados = validarDadosCliente(req.body);

    if (await emailEmUso(dados.email, id)) {
      res.status(409).json({
        mensagem: "E-mail já cadastrado."
      });
      return;
    }

    await banco.execute<ResultSetHeader>(
      `UPDATE clientes
       SET nome = ?, email = ?, telefone = ?
       WHERE id = ?`,
      [
        dados.nome,
        dados.email,
        dados.telefone,
        id
      ]
    );

    res.json({
      mensagem: "Cliente atualizado com sucesso.",
      cliente: {
        id,
        ...dados
      }
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function excluirCliente(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    if (id === null) {
      res.status(400).json({
        mensagem: "ID inválido."
      });
      return;
    }

    const [resultado] = await banco.execute<ResultSetHeader>(
      "DELETE FROM clientes WHERE id = ?",
      [id]
    );

    if (resultado.affectedRows === 0) {
      res.status(404).json({
        mensagem: "Cliente não encontrado."
      });
      return;
    }

    res.json({
      mensagem: "Cliente excluído com sucesso."
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function listarOrcamentosCliente(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    if (id === null) {
      res.status(400).json({
        mensagem: "ID inválido."
      });
      return;
    }

    const cliente = await buscarCliente(id);

    if (!cliente) {
      res.status(404).json({
        mensagem: "Cliente não encontrado."
      });
      return;
    }

    const { pagina, limite, offset } = validarPaginacao(req);

    const [totais] = await banco.execute<Total[]>(
      `SELECT COUNT(*) AS total
       FROM contatos
       WHERE id_cliente = ?`,
      [id]
    );

    const [orcamentos] = await banco.execute<Orcamento[]>(
      `SELECT id, id_cliente, servico, mensagem,
              criado_em, status
       FROM contatos
       WHERE id_cliente = ?
       ORDER BY id DESC
       LIMIT ? OFFSET ?`,
      [id, limite, offset]
    );

    const total = totais[0].total;

    res.json({
      cliente: {
        id: cliente.id,
        nome: cliente.nome
      },
      pagina,
      limite,
      total,
      totalPaginas: Math.ceil(total / limite),
      dados: orcamentos
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}
