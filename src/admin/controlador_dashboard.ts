
import { Request, Response } from "express";
import { RowDataPacket } from "mysql2";
import banco from "../configuracao/banco_dados";

interface ResumoDashboard extends RowDataPacket {
  totalClientes: number;
  totalServicos: number;
  servicosAtivos: number;
  totalOrcamentos: number;
  orcamentosPendentes: number;
  projetosFinalizados: number;
  faturamentoTotal: string;
}

interface OrcamentoRecente extends RowDataPacket {
  id: number;
  nome_cliente: string;
  servico: string | null;
  status: string;
  criado_em: Date;
}

export async function consultarDashboard(
  _req: Request,
  res: Response
): Promise<void> {
  try {
    const [resumos] =
      await banco.execute<ResumoDashboard[]>(`
        SELECT
          (SELECT COUNT(*)
           FROM clientes) AS totalClientes,

          (SELECT COUNT(*)
           FROM servicos_cat) AS totalServicos,

          (SELECT COUNT(*)
           FROM servicos_cat
           WHERE ativo = 1) AS servicosAtivos,

          (SELECT COUNT(*)
           FROM contatos) AS totalOrcamentos,

          (SELECT COUNT(*)
           FROM contatos
           WHERE status = 'Pendente')
           AS orcamentosPendentes,

          (SELECT COUNT(*)
           FROM projetos_finalizados)
           AS projetosFinalizados,

          (SELECT COALESCE(SUM(valor_final), 0)
           FROM projetos_finalizados)
           AS faturamentoTotal
      `);

    const [orcamentosRecentes] =
      await banco.execute<OrcamentoRecente[]>(`
        SELECT
          c.id,
          cl.nome AS nome_cliente,
          c.servico,
          c.status,
          c.criado_em
        FROM contatos c
        INNER JOIN clientes cl
          ON cl.id = c.id_cliente
        ORDER BY c.criado_em DESC, c.id DESC
        LIMIT 5
      `);

    const resumo = resumos[0];

    res.json({
      mensagem: "Dashboard carregado com sucesso.",
      resumo: {
        totalClientes: resumo.totalClientes,
        totalServicos: resumo.totalServicos,
        servicosAtivos: resumo.servicosAtivos,
        totalOrcamentos: resumo.totalOrcamentos,
        orcamentosPendentes: resumo.orcamentosPendentes,
        projetosFinalizados: resumo.projetosFinalizados,
        faturamentoTotal: String(
          resumo.faturamentoTotal
        )
      },
      orcamentosRecentes
    });
  } catch (erro) {
    console.error("Erro ao consultar dashboard:", erro);

    res.status(500).json({
      mensagem: "Erro interno ao carregar dashboard."
    });
  }
}
