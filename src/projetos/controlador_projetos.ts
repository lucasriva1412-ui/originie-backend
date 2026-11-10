
import { Request, Response } from "express";
import { ResultSetHeader, RowDataPacket } from "mysql2";
import banco from "../configuracao/banco_dados";

interface Projeto extends RowDataPacket {
  id: number;
  id_contato: number;
  valor_final: string;
  data_conclusao: string;
  observacao: string | null;
  criado_em: Date;
  servico: string | null;
  status: string;
  id_cliente: number;
  nome_cliente: string;
  email_cliente: string;
}

interface RegistroId extends RowDataPacket {
  id: number;
}

interface ReferenciaProjeto extends RowDataPacket {
  id: number;
  id_contato: number;
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
  const formatoValido =
    typeof valor === "number" ||
    (typeof valor === "string" && /^[1-9]\d*$/.test(valor));

  const id = formatoValido ? Number(valor) : NaN;

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

function validarValor(valor: unknown): string {
  if (
    typeof valor !== "number" &&
    typeof valor !== "string"
  ) {
    throw new ErroHttp(400, "Valor final inválido.");
  }

  const texto = String(valor);

  if (!/^(0|[1-9]\d{0,7})(\.\d{1,2})?$/.test(texto)) {
    throw new ErroHttp(
      400,
      "Informe um valor de 0 até 99999999.99, com no máximo duas casas decimais."
    );
  }

  const numero = Number(texto);

  if (!Number.isFinite(numero) || numero > 99999999.99) {
    throw new ErroHttp(400, "Valor final inválido.");
  }

  return numero.toFixed(2);
}

function validarData(valor: unknown): string {
  if (
    typeof valor !== "string" ||
    !/^\d{4}-\d{2}-\d{2}$/.test(valor)
  ) {
    throw new ErroHttp(
      400,
      "A data deve estar no formato AAAA-MM-DD."
    );
  }

  const [ano, mes, dia] = valor.split("-").map(Number);

  if (ano < 1000 || ano > 9999 || mes < 1 || mes > 12) {
    throw new ErroHttp(400, "Data de conclusão inválida.");
  }

  const data = new Date(Date.UTC(ano, mes - 1, dia));

  if (
    data.getUTCFullYear() !== ano ||
    data.getUTCMonth() !== mes - 1 ||
    data.getUTCDate() !== dia
  ) {
    throw new ErroHttp(400, "Data de conclusão inválida.");
  }

  return valor;
}

function validarDados(corpo: unknown, criacao: boolean) {
  if (
    !corpo ||
    typeof corpo !== "object" ||
    Array.isArray(corpo)
  ) {
    throw new ErroHttp(400, "Informe os dados do projeto.");
  }

  const dados = corpo as Record<string, unknown>;

  let idContato: number | null = null;

  if (criacao) {
    idContato = validarId(dados.id_contato);
  } else if (dados.id_contato !== undefined) {
    throw new ErroHttp(
      400,
      "Não é permitido trocar o orçamento de um projeto existente."
    );
  }

  const valorFinal = validarValor(dados.valor_final);
  const dataConclusao = validarData(dados.data_conclusao);

  const observacao = dados.observacao;

  if (
    observacao !== undefined &&
    observacao !== null &&
    typeof observacao !== "string"
  ) {
    throw new ErroHttp(400, "Observação inválida.");
  }

  const textoObservacao =
    typeof observacao === "string"
      ? observacao.trim()
      : "";

  if (textoObservacao.length > 255) {
    throw new ErroHttp(
      400,
      "A observação deve ter até 255 caracteres."
    );
  }

  return {
    idContato,
    valorFinal,
    dataConclusao,
    observacao: textoObservacao || null
  };
}

const consultaProjetos = `
  SELECT
    p.id,
    p.id_contato,
    p.valor_final,
    DATE_FORMAT(p.data_conclusao, '%Y-%m-%d')
      AS data_conclusao,
    p.observacao,
    p.criado_em,
    c.servico,
    c.status,
    cli.id AS id_cliente,
    cli.nome AS nome_cliente,
    cli.email AS email_cliente
  FROM projetos_finalizados p
  INNER JOIN contatos c
    ON c.id = p.id_contato
  INNER JOIN clientes cli
    ON cli.id = c.id_cliente
`;

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
    codigo === "ER_DUP_ENTRY" ||
    codigo === "ER_NO_REFERENCED_ROW_2" ||
    codigo === "ER_ROW_IS_REFERENCED_2"
  ) {
    res.status(409).json({
      mensagem: "Conflito com registros relacionados."
    });
    return;
  }

  console.error("Erro no CRUD de projetos:", erro);

  res.status(500).json({
    mensagem: "Erro interno ao processar projeto."
  });
}

export async function listarProjetos(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const { pagina, limite, offset } = validarPaginacao(req);

    const [totais] = await banco.execute<Total[]>(
      "SELECT COUNT(*) AS total FROM projetos_finalizados"
    );

    const [projetos] = await banco.execute<Projeto[]>(
      `${consultaProjetos}
       ORDER BY p.id DESC
       LIMIT ? OFFSET ?`,
      [limite, offset]
    );

    const total = totais[0].total;

    res.json({
      pagina,
      limite,
      total,
      totalPaginas: Math.ceil(total / limite),
      dados: projetos
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function consultarProjeto(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    const [projetos] = await banco.execute<Projeto[]>(
      `${consultaProjetos}
       WHERE p.id = ?
       LIMIT 1`,
      [id]
    );

    if (!projetos.length) {
      throw new ErroHttp(404, "Projeto não encontrado.");
    }

    res.json({
      projeto: projetos[0]
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function criarProjeto(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const dados = validarDados(req.body, true);
    const idContato = dados.idContato!;

    const conexao = await banco.getConnection();

    try {
      await conexao.beginTransaction();

      const [contatos] = await conexao.execute<RegistroId[]>(
        `SELECT id
         FROM contatos
         WHERE id = ?
         FOR UPDATE`,
        [idContato]
      );

      if (!contatos.length) {
        throw new ErroHttp(404, "Orçamento não encontrado.");
      }

      const [existentes] = await conexao.execute<RegistroId[]>(
        `SELECT id
         FROM projetos_finalizados
         WHERE id_contato = ?
         LIMIT 1`,
        [idContato]
      );

      if (existentes.length) {
        throw new ErroHttp(
          409,
          "Este orçamento já possui um projeto finalizado."
        );
      }

      const [resultado] = await conexao.execute<ResultSetHeader>(
        `INSERT INTO projetos_finalizados
         (id_contato, valor_final, data_conclusao, observacao)
         VALUES (?, ?, ?, ?)`,
        [
          idContato,
          dados.valorFinal,
          dados.dataConclusao,
          dados.observacao
        ]
      );

      await conexao.execute(
        `UPDATE contatos
         SET status = 'Concluído'
         WHERE id = ?`,
        [idContato]
      );

      await conexao.commit();

      res.status(201).json({
        mensagem: "Projeto finalizado com sucesso.",
        projeto: {
          id: resultado.insertId,
          id_contato: idContato,
          valor_final: dados.valorFinal,
          data_conclusao: dados.dataConclusao,
          observacao: dados.observacao
        }
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

export async function atualizarProjeto(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);
    const dados = validarDados(req.body, false);

    const [resultado] = await banco.execute<ResultSetHeader>(
      `UPDATE projetos_finalizados
       SET valor_final = ?,
           data_conclusao = ?,
           observacao = ?
       WHERE id = ?`,
      [
        dados.valorFinal,
        dados.dataConclusao,
        dados.observacao,
        id
      ]
    );

    if (resultado.affectedRows === 0) {
      const [existentes] = await banco.execute<RegistroId[]>(
        `SELECT id
         FROM projetos_finalizados
         WHERE id = ?
         LIMIT 1`,
        [id]
      );

      if (!existentes.length) {
        throw new ErroHttp(404, "Projeto não encontrado.");
      }
    }

    res.json({
      mensagem: "Projeto atualizado com sucesso."
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function excluirProjeto(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);
    const conexao = await banco.getConnection();

    try {
      await conexao.beginTransaction();

      const [referencias] =
        await conexao.execute<ReferenciaProjeto[]>(
          `SELECT id, id_contato
           FROM projetos_finalizados
           WHERE id = ?
           LIMIT 1`,
          [id]
        );

      if (!referencias.length) {
        throw new ErroHttp(404, "Projeto não encontrado.");
      }

      const idContato = referencias[0].id_contato;

      await conexao.execute(
        `SELECT id
         FROM contatos
         WHERE id = ?
         FOR UPDATE`,
        [idContato]
      );

      const [projetos] =
        await conexao.execute<ReferenciaProjeto[]>(
          `SELECT id, id_contato
           FROM projetos_finalizados
           WHERE id = ?
           FOR UPDATE`,
          [id]
        );

      if (!projetos.length) {
        throw new ErroHttp(404, "Projeto não encontrado.");
      }

      await conexao.execute(
        `DELETE FROM projetos_finalizados
         WHERE id = ?`,
        [id]
      );

      const [restantes] = await conexao.execute<RegistroId[]>(
        `SELECT id
         FROM projetos_finalizados
         WHERE id_contato = ?
         LIMIT 1`,
        [idContato]
      );

      if (!restantes.length) {
        await conexao.execute(
          `UPDATE contatos
           SET status = 'Pendente'
           WHERE id = ?`,
          [idContato]
        );
      }

      await conexao.commit();

      res.json({
        mensagem: "Projeto excluído e orçamento reaberto."
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
