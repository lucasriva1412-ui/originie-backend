
import { Request, Response } from "express";
import { ResultSetHeader, RowDataPacket } from "mysql2";
import { PoolConnection } from "mysql2/promise";
import banco from "../configuracao/banco_dados";

interface Orcamento extends RowDataPacket {
  id: number;
  id_cliente: number;
  servico: string | null;
  mensagem: string;
  status: string;
  criado_em: Date;
  nome_cliente: string;
  email_cliente: string;
  telefone_cliente: string | null;
}

interface RegistroId extends RowDataPacket {
  id: number;
}

interface Servico extends RowDataPacket {
  id: number;
  nome: string;
}

interface Total extends RowDataPacket {
  total: number;
}

class ErroHttp extends Error {
  constructor(
    public status: number,
    mensagem: string
  ) {
    super(mensagem);
  }
}

function validarId(valor: unknown): number {
  const valido =
    typeof valor === "number" ||
    (typeof valor === "string" && /^[1-9]\d*$/.test(valor));

  const id = valido ? Number(valor) : NaN;

  if (!Number.isSafeInteger(id) || id <= 0) {
    throw new ErroHttp(400, "ID inválido.");
  }

  return id;
}

function validarPaginacao(req: Request) {
  const pagina = validarId(req.query.pagina ?? "1");
  const limite = validarId(req.query.limite ?? "10");

  if (limite > 100) {
    throw new ErroHttp(400, "O limite máximo é 100.");
  }

  const offset = (pagina - 1) * limite;

  if (!Number.isSafeInteger(offset)) {
    throw new ErroHttp(400, "Página fora do intervalo.");
  }

  return { pagina, limite, offset };
}

function validarDados(corpo: unknown) {
  if (
    !corpo ||
    typeof corpo !== "object" ||
    Array.isArray(corpo)
  ) {
    throw new ErroHttp(400, "Informe os dados do orçamento.");
  }

  const dados = corpo as Record<string, unknown>;

  const idCliente = validarId(dados.id_cliente);
  const mensagem = dados.mensagem;
  const status = dados.status ?? "Pendente";
  const lista = dados.servicosIds;

  if (
    typeof mensagem !== "string" ||
    mensagem.trim().length === 0 ||
    mensagem.length > 5000
  ) {
    throw new ErroHttp(
      400,
      "A mensagem é obrigatória e deve ter até 5000 caracteres."
    );
  }

  const statusPermitidos = [
    "Pendente",
    "Em andamento",
    "Concluído",
    "Cancelado"
  ];

  if (
    typeof status !== "string" ||
    !statusPermitidos.includes(status)
  ) {
    throw new ErroHttp(400, "Status inválido.");
  }

  if (
    !Array.isArray(lista) ||
    lista.length === 0 ||
    lista.length > 25
  ) {
    throw new ErroHttp(
      400,
      "Informe entre 1 e 25 serviços."
    );
  }

  const servicosIds = lista.map(validarId);

  if (new Set(servicosIds).size !== servicosIds.length) {
    throw new ErroHttp(
      400,
      "Não repita serviços no mesmo orçamento."
    );
  }

  return {
    idCliente,
    mensagem: mensagem.trim(),
    status,
    servicosIds
  };
}

async function validarRelacionamentos(
  conexao: PoolConnection,
  idCliente: number,
  servicosIds: number[]
): Promise<string> {
  const [clientes] = await conexao.execute<RegistroId[]>(
    "SELECT id FROM clientes WHERE id = ? LIMIT 1",
    [idCliente]
  );

  if (!clientes.length) {
    throw new ErroHttp(404, "Cliente não encontrado.");
  }

  const marcadores = servicosIds.map(() => "?").join(", ");

  const [servicos] = await conexao.execute<Servico[]>(
    `SELECT id, nome
     FROM servicos_cat
     WHERE id IN (${marcadores})`,
    servicosIds
  );

  if (servicos.length !== servicosIds.length) {
    throw new ErroHttp(
      400,
      "Um ou mais serviços não existem."
    );
  }

  const nomesPorId = new Map(
    servicos.map(servico => [servico.id, servico.nome])
  );

  const nomes = servicosIds.map(
    id => nomesPorId.get(id)!
  );

  const descricaoServicos = nomes.join(", ");

  if (descricaoServicos.length > 100) {
    throw new ErroHttp(
      400,
      "Os nomes dos serviços ultrapassam o limite de 100 caracteres."
    );
  }

  return descricaoServicos;
}

async function vincularServicos(
  conexao: PoolConnection,
  idOrcamento: number,
  servicosIds: number[]
): Promise<void> {
  for (const idServico of servicosIds) {
    await conexao.execute(
      `INSERT INTO servicos_interesse
       (id_contato, id_servico)
       VALUES (?, ?)`,
      [idOrcamento, idServico]
    );
  }
}

function tratarErro(res: Response, erro: unknown): void {
  if (erro instanceof ErroHttp) {
    res.status(erro.status).json({
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

  if (
    codigo === "ER_ROW_IS_REFERENCED_2" ||
    codigo === "ER_NO_REFERENCED_ROW_2"
  ) {
    res.status(409).json({
      mensagem: "Conflito com registros relacionados."
    });
    return;
  }

  console.error("Erro no CRUD de orçamentos:", erro);

  res.status(500).json({
    mensagem: "Erro interno ao processar orçamento."
  });
}

export async function listarOrcamentos(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const { pagina, limite, offset } = validarPaginacao(req);

    const [totais] = await banco.execute<Total[]>(
      "SELECT COUNT(*) AS total FROM contatos"
    );

    const [orcamentos] = await banco.execute<Orcamento[]>(
      `SELECT
         c.id,
         c.id_cliente,
         c.servico,
         c.mensagem,
         c.status,
         c.criado_em,
         cl.nome AS nome_cliente,
         cl.email AS email_cliente,
         cl.telefone AS telefone_cliente
       FROM contatos c
       INNER JOIN clientes cl ON cl.id = c.id_cliente
       ORDER BY c.id DESC
       LIMIT ? OFFSET ?`,
      [limite, offset]
    );

    const total = totais[0].total;

    res.json({
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

export async function consultarOrcamento(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    const [orcamentos] = await banco.execute<Orcamento[]>(
      `SELECT
         c.id,
         c.id_cliente,
         c.servico,
         c.mensagem,
         c.status,
         c.criado_em,
         cl.nome AS nome_cliente,
         cl.email AS email_cliente,
         cl.telefone AS telefone_cliente
       FROM contatos c
       INNER JOIN clientes cl ON cl.id = c.id_cliente
       WHERE c.id = ?
       LIMIT 1`,
      [id]
    );

    if (!orcamentos.length) {
      throw new ErroHttp(404, "Orçamento não encontrado.");
    }

    const [servicos] = await banco.execute<Servico[]>(
      `SELECT s.id, s.nome
       FROM servicos_interesse si
       INNER JOIN servicos_cat s ON s.id = si.id_servico
       WHERE si.id_contato = ?
       ORDER BY s.nome`,
      [id]
    );

    res.json({
      orcamento: orcamentos[0],
      servicos
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function criarOrcamento(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const dados = validarDados(req.body);
    const conexao = await banco.getConnection();

    try {
      await conexao.beginTransaction();

      const nomesServicos = await validarRelacionamentos(
        conexao,
        dados.idCliente,
        dados.servicosIds
      );

      const [resultado] = await conexao.execute<ResultSetHeader>(
        `INSERT INTO contatos
         (id_cliente, servico, mensagem, status)
         VALUES (?, ?, ?, ?)`,
        [
          dados.idCliente,
          nomesServicos,
          dados.mensagem,
          dados.status
        ]
      );

      await vincularServicos(
        conexao,
        resultado.insertId,
        dados.servicosIds
      );

      await conexao.commit();

      res.status(201).json({
        mensagem: "Orçamento cadastrado com sucesso.",
        id: resultado.insertId
      });
    } catch (erro) {
      await conexao.rollback();
      throw erro;
    } finally {
      conexao.release();
    }
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function atualizarOrcamento(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);
    const dados = validarDados(req.body);
    const conexao = await banco.getConnection();

    try {
      await conexao.beginTransaction();

      const [existentes] = await conexao.execute<RegistroId[]>(
        "SELECT id FROM contatos WHERE id = ? FOR UPDATE",
        [id]
      );

      if (!existentes.length) {
        throw new ErroHttp(404, "Orçamento não encontrado.");
      }

      const nomesServicos = await validarRelacionamentos(
        conexao,
        dados.idCliente,
        dados.servicosIds
      );

      await conexao.execute(
        `UPDATE contatos
         SET id_cliente = ?, servico = ?, mensagem = ?, status = ?
         WHERE id = ?`,
        [
          dados.idCliente,
          nomesServicos,
          dados.mensagem,
          dados.status,
          id
        ]
      );

      await conexao.execute(
        "DELETE FROM servicos_interesse WHERE id_contato = ?",
        [id]
      );

      await vincularServicos(
        conexao,
        id,
        dados.servicosIds
      );

      await conexao.commit();

      res.json({
        mensagem: "Orçamento atualizado com sucesso."
      });
    } catch (erro) {
      await conexao.rollback();
      throw erro;
    } finally {
      conexao.release();
    }
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function excluirOrcamento(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);
    const conexao = await banco.getConnection();

    try {
      await conexao.beginTransaction();

      const [existentes] = await conexao.execute<RegistroId[]>(
        "SELECT id FROM contatos WHERE id = ? FOR UPDATE",
        [id]
      );

      if (!existentes.length) {
        throw new ErroHttp(404, "Orçamento não encontrado.");
      }

      const [projetos] = await conexao.execute<RegistroId[]>(
        `SELECT id
         FROM projetos_finalizados
         WHERE id_contato = ?
         LIMIT 1`,
        [id]
      );

      if (projetos.length) {
        throw new ErroHttp(
          409,
          "Este orçamento possui um projeto finalizado e não pode ser excluído."
        );
      }

      await conexao.execute(
        "DELETE FROM servicos_interesse WHERE id_contato = ?",
        [id]
      );

      await conexao.execute(
        "DELETE FROM contatos WHERE id = ?",
        [id]
      );

      await conexao.commit();

      res.json({
        mensagem: "Orçamento excluído com sucesso."
      });
    } catch (erro) {
      await conexao.rollback();
      throw erro;
    } finally {
      conexao.release();
    }
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function concluirOrcamento(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    const [existentes] = await banco.execute<RegistroId[]>(
      "SELECT id FROM contatos WHERE id = ? LIMIT 1",
      [id]
    );

    if (!existentes.length) {
      throw new ErroHttp(404, "Orçamento não encontrado.");
    }

    await banco.execute(
      `UPDATE contatos
       SET status = 'Concluído'
       WHERE id = ?`,
      [id]
    );

    res.json({
      mensagem: "Orçamento concluído com sucesso."
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}
