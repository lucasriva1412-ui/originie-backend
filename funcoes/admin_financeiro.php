<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * FINANCEIRO
 * ============================================================
 */


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once 'conexao.php';
require_once 'functions.php';


// ============================================================
// ADMIN
// ============================================================

if (
    !isset($_SESSION['usuario']) ||
    !is_array($_SESSION['usuario']) ||
    empty($_SESSION['usuario']['id']) ||
    ($_SESSION['usuario']['tipo'] ?? '') !== 'admin'
) {

    header('Location: login.php');
    exit;
}


$usuario =
    $_SESSION['usuario'];


// ============================================================
// FILTROS
// ============================================================

$mes =
    isset($_GET['mes'])
        ? (int) $_GET['mes']
        : (int) date('m');


$ano =
    isset($_GET['ano'])
        ? (int) $_GET['ano']
        : (int) date('Y');


if ($mes < 1 || $mes > 12) {

    $mes =
        (int) date('m');
}


if ($ano < 2000 || $ano > 2100) {

    $ano =
        (int) date('Y');
}


// ============================================================
// MESES
// ============================================================

$meses = [

    1 => 'Janeiro',

    2 => 'Fevereiro',

    3 => 'Março',

    4 => 'Abril',

    5 => 'Maio',

    6 => 'Junho',

    7 => 'Julho',

    8 => 'Agosto',

    9 => 'Setembro',

    10 => 'Outubro',

    11 => 'Novembro',

    12 => 'Dezembro'

];


// ============================================================
// ESTATÍSTICAS
// ============================================================

$sqlStats = "
    SELECT

        COUNT(*) AS total,

        COALESCE(
            SUM(valor_final),
            0
        ) AS faturamento,

        COALESCE(
            AVG(valor_final),
            0
        ) AS media,

        COALESCE(
            MAX(valor_final),
            0
        ) AS maior

    FROM projetos_finalizados

    WHERE
        MONTH(data_conclusao) = ?
        AND
        YEAR(data_conclusao) = ?
";


$stmtStats =
    $conexao->prepare(
        $sqlStats
    );


$stmtStats->bind_param(
    'ii',
    $mes,
    $ano
);


$stmtStats->execute();


$stats =
    $stmtStats
        ->get_result()
        ->fetch_assoc();


$stmtStats->close();


$totalProjetos =
    (int) (
        $stats['total']
        ?? 0
    );


$faturamento =
    (float) (
        $stats['faturamento']
        ?? 0
    );


$ticketMedio =
    (float) (
        $stats['media']
        ?? 0
    );


$maiorProjeto =
    (float) (
        $stats['maior']
        ?? 0
    );


// ============================================================
// PROJETOS
// ============================================================

$sqlProjetos = "
    SELECT

        pf.id,
        pf.id_contato,
        pf.valor_final,
        pf.data_conclusao,
        pf.observacao,

        c.servico,

        cli.nome AS cliente,
        cli.email,
        cli.telefone

    FROM projetos_finalizados pf

    INNER JOIN contatos c
        ON c.id = pf.id_contato

    INNER JOIN clientes cli
        ON cli.id = c.id_cliente

    WHERE
        MONTH(pf.data_conclusao) = ?
        AND
        YEAR(pf.data_conclusao) = ?

    ORDER BY
        pf.data_conclusao DESC,
        pf.id DESC
";


$stmtProjetos =
    $conexao->prepare(
        $sqlProjetos
    );


$stmtProjetos->bind_param(
    'ii',
    $mes,
    $ano
);


$stmtProjetos->execute();


$resultadoProjetos =
    $stmtProjetos->get_result();


$projetos = [];


while (
    $linha =
        $resultadoProjetos->fetch_assoc()
) {

    $projetos[] =
        $linha;
}


$stmtProjetos->close();

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Financeiro — Originie
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >


    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <link
        rel="stylesheet"
        href="assets/css/admin_financeiro.css"
    >

</head>


<body class="pagina-financeiro">


<nav class="navbar-financeiro">

    <div class="container">

        <div class="navbar-financeiro-conteudo">


            <a
                href="admin_dashboard.php"
                class="logo-financeiro"
            >

                ORIGINIE

                <span>
                    ADMIN
                </span>

            </a>


            <div class="navbar-financeiro-acoes">


                <span class="usuario-financeiro">

                    <i class="bi bi-person-circle"></i>

                    <?php
                    echo sanitizar(
                        $usuario['nome']
                        ?? 'Administrador'
                    );
                    ?>

                </span>


                <a
                    href="admin_dashboard.php"
                    class="btn-financeiro-secundario"
                >

                    <i class="bi bi-speedometer2"></i>

                    Dashboard

                </a>


                <a
                    href="admin_orcamentos.php"
                    class="btn-financeiro-secundario"
                >

                    <i class="bi bi-clipboard-check"></i>

                    Orçamentos

                </a>


                <a
                    href="logout.php"
                    class="btn-financeiro-sair"
                >

                    Sair

                </a>


            </div>

        </div>

    </div>

</nav>



<main class="conteudo-financeiro">

    <div class="container">


        <div class="cabecalho-financeiro">


            <div>

                <span class="etiqueta-financeiro">

                    CONTROLE FINANCEIRO

                </span>


                <h1>

                    Projetos Finalizados

                </h1>


                <p>

                    <?php
                    echo $meses[$mes];
                    ?>

                    de

                    <?php
                    echo $ano;
                    ?>

                </p>

            </div>


            <a
                href="admin_orcamentos.php"
                class="btn-financeiro-principal"
            >

                <i class="bi bi-plus-circle"></i>

                Finalizar Projeto

            </a>


        </div>



        <?php if (!empty($_GET['mensagem'])): ?>

            <div class="alert alert-success">

                <i class="bi bi-check-circle me-2"></i>

                <?php
                echo sanitizar(
                    $_GET['mensagem']
                );
                ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($_GET['erro'])): ?>

            <div class="alert alert-danger">

                <?php
                echo sanitizar(
                    $_GET['erro']
                );
                ?>

            </div>

        <?php endif; ?>



        <!-- ====================================================
             CARDS FINANCEIROS
        ===================================================== -->

        <div class="cards-financeiro">


            <div class="card-financeiro">

                <div class="icone-financeiro">

                    <i class="bi bi-check2-circle"></i>

                </div>


                <div>

                    <span>

                        Projetos Finalizados

                    </span>

                    <strong>

                        <?php
                        echo $totalProjetos;
                        ?>

                    </strong>

                </div>

            </div>



            <div class="card-financeiro">

                <div class="icone-financeiro">

                    <i class="bi bi-cash-stack"></i>

                </div>


                <div>

                    <span>

                        Faturamento

                    </span>

                    <strong>

                        R$
                        <?php

                        echo number_format(
                            $faturamento,
                            2,
                            ',',
                            '.'
                        );

                        ?>

                    </strong>

                </div>

            </div>



            <div class="card-financeiro">

                <div class="icone-financeiro">

                    <i class="bi bi-calculator"></i>

                </div>


                <div>

                    <span>
                        Ticket Médio
                    </span>

                    <strong>

                        R$
                        <?php

                        echo number_format(
                            $ticketMedio,
                            2,
                            ',',
                            '.'
                        );

                        ?>

                    </strong>

                </div>

            </div>



            <div class="card-financeiro">

                <div class="icone-financeiro">

                    <i class="bi bi-graph-up-arrow"></i>

                </div>


                <div>

                    <span>
                        Maior Projeto
                    </span>

                    <strong>

                        R$
                        <?php

                        echo number_format(
                            $maiorProjeto,
                            2,
                            ',',
                            '.'
                        );

                        ?>

                    </strong>

                </div>

            </div>


        </div>



        <!-- ====================================================
             FILTRO
        ===================================================== -->

        <section class="painel-filtros-financeiro">


            <form method="GET">


                <div class="row g-3">


                    <div class="col-md-5">

                        <label
                            for="mes"
                            class="form-label"
                        >

                            Mês

                        </label>


                        <select
                            name="mes"
                            id="mes"
                            class="form-select campo-financeiro"
                        >


                            <?php foreach ($meses as $numero => $nome): ?>


                                <option
                                    value="<?php echo $numero; ?>"
                                    <?php
                                    echo $mes === $numero
                                        ? 'selected'
                                        : '';
                                    ?>
                                >

                                    <?php
                                    echo $nome;
                                    ?>

                                </option>


                            <?php endforeach; ?>


                        </select>

                    </div>



                    <div class="col-md-5">

                        <label
                            for="ano"
                            class="form-label"
                        >

                            Ano

                        </label>


                        <input
                            type="number"
                            name="ano"
                            id="ano"
                            class="form-control campo-financeiro"
                            value="<?php
                            echo $ano;
                            ?>"
                        >

                    </div>



                    <div
                        class="col-md-2 d-flex align-items-end"
                    >

                        <button
                            type="submit"
                            class="btn-financeiro-principal w-100"
                        >

                            <i class="bi bi-search"></i>

                            Filtrar

                        </button>

                    </div>


                </div>


            </form>

        </section>



        <!-- ====================================================
             LISTAGEM
        ===================================================== -->

        <section class="painel-financeiro">


            <div class="titulo-tabela-financeiro">

                <div>

                    <h2>

                        Projetos do período

                    </h2>


                    <span>

                        <?php
                        echo count(
                            $projetos
                        );
                        ?>

                        projeto(s)

                    </span>

                </div>

            </div>



            <div class="table-responsive">


                <table class="table tabela-financeiro">


                    <thead>

                        <tr>

                            <th>Cliente</th>

                            <th>Serviço</th>

                            <th>Valor</th>

                            <th>Conclusão</th>

                            <th>Observação</th>

                            <th>Ações</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (empty($projetos)): ?>


                        <tr>

                            <td
                                colspan="6"
                                class="sem-projetos"
                            >

                                <i class="bi bi-inbox"></i>

                                Nenhum projeto finalizado
                                nesse período.

                            </td>

                        </tr>


                    <?php else: ?>


                        <?php foreach ($projetos as $projeto): ?>


                            <tr>


                                <td>

                                    <strong>

                                        <?php
                                        echo sanitizar(
                                            $projeto['cliente']
                                        );
                                        ?>

                                    </strong>

                                    <small>

                                        <?php
                                        echo sanitizar(
                                            $projeto['email']
                                        );
                                        ?>

                                    </small>

                                </td>


                                <td>

                                    <?php
                                    echo sanitizar(
                                        $projeto['servico']
                                        ?? 'Não informado'
                                    );
                                    ?>

                                </td>


                                <td class="valor-projeto">

                                    R$

                                    <?php

                                    echo number_format(
                                        (float) $projeto['valor_final'],
                                        2,
                                        ',',
                                        '.'
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo date(
                                        'd/m/Y',
                                        strtotime(
                                            $projeto['data_conclusao']
                                        )
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo sanitizar(
                                        $projeto['observacao']
                                        ?: '-'
                                    );

                                    ?>

                                </td>


                                <td>

                                    <div class="acoes-financeiro">


                                        <a
                                            href="admin_projeto_editar.php?id=<?php
                                            echo (int) $projeto['id'];
                                            ?>"
                                            class="btn-editar-financeiro"
                                            title="Editar"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <a
                                            href="admin_projeto_deletar.php?id=<?php
                                            echo (int) $projeto['id'];
                                            ?>"
                                            class="btn-deletar-financeiro"
                                            title="Excluir"
                                            onclick="
                                                return confirm(
                                                    'Excluir este projeto? O orçamento voltará para Pendente.'
                                                );
                                            "
                                        >

                                            <i class="bi bi-trash"></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php endif; ?>


                    </tbody>


                </table>

            </div>

        </section>


    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>