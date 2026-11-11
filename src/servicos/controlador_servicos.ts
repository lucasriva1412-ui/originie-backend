
import { Request, Response } from "express";
import { ResultSetHeader, RowDataPacket } from "mysql2";
import banco from "../configuracao/banco_dados";

interface Servico extends RowDataPacket {
  id: number;
  nome: string;
  slug: string;
  descricao: string;
  preco: string;
  categoria: string;
  video: string | null;
  ativo: number;
}

interface Total extends RowDataPacket {
  total: number;
}

class ErroValidacao extends Error {}

function validarId(valor: string): number | null {
  if (!/^[1-9]\d*$/.test(valor)) return null;

  const id = Number(valor);
  return Number.isSafeInteger(id) ? id : null;
}

function validarDados(dados: any) {
  if (!dados || typeof dados !== "object" || Array.isArray(dados)) {
    throw new ErroValidacao("Informe os dados do serviço.");
  }

  const { nome, slug, descricao, preco, categoria, video, ativo } = dados;

  if (
    typeof nome !== "string" ||
    nome.trim().length === 0 ||
    nome.trim().length > 100
  ) {
    throw new ErroValidacao("Nome inválido. Limite de 100 caracteres.");
  }

  if (
    typeof slug !== "string" ||
    !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug) ||
    slug.length > 100
  ) {
    throw new ErroValidacao(
      "Slug inválido. Use letras minúsculas, números e hífens."
    );
  }

  if (
    typeof descricao !== "string" ||
    descricao.trim().length === 0
  ) {
    throw new ErroValidacao("Descrição obrigatória.");
  }

  if (
    typeof categoria !== "string" ||
    categoria.trim().length === 0 ||
    categoria.trim().length > 50
  ) {
    throw new ErroValidacao("Categoria inválida.");
  }

  const valorPreco =
    typeof preco === "number" || typeof preco === "string"
      ? Number(preco)
      : NaN;

  if (
    !Number.isFinite(valorPreco) ||
    valorPreco < 0 ||
    valorPreco > 99999999.99 ||
    Math.abs(valorPreco * 100 - Math.round(valorPreco * 100)) > 0.000001
  ) {
    throw new ErroValidacao(
      "Preço inválido. Informe um valor positivo ou zero, com até duas casas decimais."
    );
  }

  if (
    video !== undefined &&
    video !== null &&
    (typeof video !== "string" || video.length > 255)
  ) {
    throw new ErroValidacao("Vídeo inválido.");
  }

  if (
    ativo !== undefined &&
    ativo !== true &&
    ativo !== false &&
    ativo !== 0 &&
    ativo !== 1
  ) {
    throw new ErroValidacao("O campo ativo deve ser booleano.");
  }

  return {
    nome: nome.trim(),
    slug,
    descricao: descricao.trim(),
    preco: valorPreco.toFixed(2),
    categoria: categoria.trim(),
    video: video || null,
    ativo: ativo === false || ativo === 0 ? 0 : 1
  };
}

function tratarErro(res: Response, erro: unknown): void {
  if (erro instanceof ErroValidacao) {
    res.status(400).json({ mensagem: erro.message });
    return;
  }

  console.error("Erro no CRUD de serviços:", erro);

  res.status(500).json({
    mensagem: "Erro interno ao processar serviço."
  });
}

export async function listarServicos(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const paginaTexto = req.query.pagina ?? "1";
    const limiteTexto = req.query.limite ?? "10";

    if (
      typeof paginaTexto !== "string" ||
      typeof limiteTexto !== "string" ||
      !/^[1-9]\d*$/.test(paginaTexto) ||
      !/^[1-9]\d*$/.test(limiteTexto)
    ) {
      res.status(400).json({
        mensagem: "Parâmetros de paginação inválidos."
      });
      return;
    }

    const pagina = Number(paginaTexto);
    const limite = Number(limiteTexto);

    if (
      !Number.isSafeInteger(pagina) ||
      !Number.isSafeInteger(limite) ||
      limite > 100
    ) {
      res.status(400).json({
        mensagem: "Página inválida ou limite acima de 100."
      });
      return;
    }

    const deslocamento = (pagina - 1) * limite;

    if (!Number.isSafeInteger(deslocamento)) {
      res.status(400).json({
        mensagem: "Página fora do intervalo permitido."
      });
      return;
    }

    const [totais] = await banco.execute<Total[]>(
      "SELECT COUNT(*) AS total FROM servicos_cat"
    );

    const total = totais[0].total;

    const [servicos] = await banco.execute<Servico[]>(
      `SELECT id, nome, slug, descricao, preco, categoria, video, ativo
       FROM servicos_cat
       ORDER BY id DESC
       LIMIT ? OFFSET ?`,
      [limite, deslocamento]
    );

    res.json({
      pagina,
      limite,
      total,
      totalPaginas: Math.ceil(total / limite),
      dados: servicos
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function consultarServico(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    if (id === null) {
      res.status(400).json({ mensagem: "ID inválido." });
      return;
    }

    const [servicos] = await banco.execute<Servico[]>(
      `SELECT id, nome, slug, descricao, preco, categoria, video, ativo
       FROM servicos_cat
       WHERE id = ?`,
      [id]
    );

    if (!servicos.length) {
      res.status(404).json({
        mensagem: "Serviço não encontrado."
      });
      return;
    }

    res.json({ servico: servicos[0] });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function criarServico(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const dados = validarDados(req.body);

    const [resultado] = await banco.execute<ResultSetHeader>(
      `INSERT INTO servicos_cat
       (nome, slug, descricao, preco, categoria, video, ativo)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        dados.nome,
        dados.slug,
        dados.descricao,
        dados.preco,
        dados.categoria,
        dados.video,
        dados.ativo
      ]
    );

    res.status(201).json({
      mensagem: "Serviço cadastrado com sucesso.",
      id: resultado.insertId
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function atualizarServico(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    if (id === null) {
      res.status(400).json({ mensagem: "ID inválido." });
      return;
    }

    const dados = validarDados(req.body);

    const [resultado] = await banco.execute<ResultSetHeader>(
      `UPDATE servicos_cat
       SET nome = ?, slug = ?, descricao = ?, preco = ?,
           categoria = ?, video = ?, ativo = ?
       WHERE id = ?`,
      [
        dados.nome,
        dados.slug,
        dados.descricao,
        dados.preco,
        dados.categoria,
        dados.video,
        dados.ativo,
        id
      ]
    );

    if (resultado.affectedRows === 0) {
      res.status(404).json({
        mensagem: "Serviço não encontrado."
      });
      return;
    }

    res.json({
      mensagem: "Serviço atualizado com sucesso."
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}

export async function excluirServico(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const id = validarId(req.params.id);

    if (id === null) {
      res.status(400).json({ mensagem: "ID inválido." });
      return;
    }

    const [resultado] = await banco.execute<ResultSetHeader>(
      "DELETE FROM servicos_cat WHERE id = ?",
      [id]
    );

    if (resultado.affectedRows === 0) {
      res.status(404).json({
        mensagem: "Serviço não encontrado."
      });
      return;
    }

    res.json({
      mensagem: "Serviço excluído com sucesso."
    });
  } catch (erro) {
    tratarErro(res, erro);
  }
}


export async function listarServicosPublicos(
  req: Request,
  res: Response
): Promise<void> {
  try {
    const paginaTexto = req.query.pagina ?? "1";
    const limiteTexto = req.query.limite ?? "10";

    if (
      typeof paginaTexto !== "string" ||
      typeof limiteTexto !== "string" ||
      !/^[1-9]\d*$/.test(paginaTexto) ||
      !/^[1-9]\d*$/.test(limiteTexto)
    ) {
      res.status(400).json({
        mensagem: "Paginação inválida."
      });
      return;
    }

    const pagina = Number(paginaTexto);
    const limite = Number(limiteTexto);

    if (
      !Number.isSafeInteger(pagina) ||
      !Number.isSafeInteger(limite) ||
      limite > 100
    ) {
      res.status(400).json({
        mensagem: "Página ou limite inválido."
      });
      return;
    }

    const offset = (pagina - 1) * limite;

    if (!Number.isSafeInteger(offset)) {
      res.status(400).json({
        mensagem: "Página fora do intervalo."
      });
      return;
    }

    const [totais] = await banco.execute<Total[]>(
      `SELECT COUNT(*) AS total
       FROM servicos_cat
       WHERE ativo = 1`
    );

    const [servicos] = await banco.execute<Servico[]>(
      `SELECT
         id,
         nome,
         slug,
         descricao,
         preco,
         categoria,
         video
       FROM servicos_cat
       WHERE ativo = 1
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
      dados: servicos
    });
  } catch (erro) {
    console.error("Erro ao listar serviços públicos:", erro);

    res.status(500).json({
      mensagem: "Erro interno ao buscar serviços."
    });
  }
}
