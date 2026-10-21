<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * ORÇAMENTOS
 * ============================================================
 */


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (
    !isset($_SESSION['usuario']) ||
    !is_array($_SESSION['usuario']) ||
    empty($_SESSION['usuario']['id']) ||
    ($_SESSION['usuario']['tipo'] ?? '') !== 'admin'
) {

    header('Location: login.php');
    exit;
}


require_once 'conexao.php';
require_once 'functions.php';


$usuario =
    $_SESSION['usuario'];


// ============================================================
// ORDEM
// ============================================================

$ordem =
    $_GET['ordem']
    ?? 'recentes';


$ordenacao =
    $ordem === 'antigos'
        ? 'ASC'
        : 'DESC';


// ============================================================
// BUSCAR ORÇAMENTOS
// ============================================================

$sql = "
    SELECT

        contatos.id,
        contatos.id_cliente,
        contatos.servico,
        contatos.mensagem,
        contatos.criado_em,
        contatos.status,

        clientes.nome,
        clientes.email,
        clientes.telefone

    FROM contatos

    INNER JOIN clientes
        ON clientes.id = contatos.id_cliente

    ORDER BY contatos.criado_em $ordenacao
";


$resultado =
    $conexao->query(
        $sql
    );


$orcamentos = [];


if ($resultado) {

    while (
        $linha =
            $resultado->fetch_assoc()
    ) {

        $orcamentos[] =
            $linha;
    }
}


// ============================================================
// CONTADORES
// ============================================================

$totalOrcamentos =
    count(
        $orcamentos
    );


$pendentes = 0;

$concluidos = 0;


foreach ($orcamentos as $orcamento) {

    $status =
        strtolower(
            trim(
                $orcamento['status']
                ?? ''
            )
        );


    if (
        $status === 'concluído' ||
        $status === 'concluido'
    ) {

        $concluidos++;

    } else {

        $pendentes++;
    }
}

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
        Orçamentos — Originie Audiovisual
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
        href="assets/css/admin_orcamentos.css"
    >

</head>


<body>


<div class="barra-admin">

    <div class="container">

        <div class="barra-admin-conteudo">


            <h1 class="titulo-dashboard">

                <i class="bi bi-file-earmark-text me-2"></i>

                Orçamentos

            </h1>


            <div class="usuario-info">


                <div>

                    <strong>

                        <?php
                        echo sanitizar(
                            $usuario['nome']
                            ?? 'Administrador'
                        );
                        ?>

                    </strong>

                    <br>

                    <small>

                        <?php
                        echo sanitizar(
                            $usuario['email']
                            ?? ''
                        );
                        ?>

                    </small>

                </div>


                <a
                    href="logout.php"
                    class="botao-sair"
                >

                    <i class="bi bi-box-arrow-right"></i>

                    Sair

                </a>


            </div>

        </div>

    </div>

</div>



<div class="container conteudo-orcamentos">


    <div class="cabecalho">


        <div>

            <span class="etiqueta-pagina">

                GERENCIAMENTO

            </span>


            <h2 class="titulo-secao">

                Orçamentos Recebidos

            </h2>

        </div>


        <div class="acoes-cabecalho">


            <a
                href="admin_financeiro.php"
                class="botao-financeiro"
            >

                <i class="bi bi-cash-stack"></i>

                Financeiro

            </a>


            <a
                href="admin_dashboard.php"
                class="botao-voltar"
            >

                <i class="bi bi-arrow-left"></i>

                Dashboard

            </a>


        </div>

    </div>



    <?php if (!empty($_GET['mensagem'])): ?>

        <div class="alert alert-success">

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



    <div class="estatisticas">


        <div class="card-estatistica">

            <div class="numero-estatistica">

                <?php
                echo $totalOrcamentos;
                ?>

            </div>

            <div class="texto-estatistica">

                Total de Orçamentos

            </div>

        </div>


        <div class="card-estatistica">

            <div class="numero-estatistica">

                <?php
                echo $pendentes;
                ?>

            </div>

            <div class="texto-estatistica">

                Pendentes

            </div>

        </div>


        <div class="card-estatistica">

            <div class="numero-estatistica">

                <?php
                echo $concluidos;
                ?>

            </div>

            <div class="texto-estatistica">

                Concluídos

            </div>

        </div>


    </div>



    <div class="barra-filtro">


        <div>

            <label for="ordem">

                Ordenar por:

            </label>


            <select
                id="ordem"
                class="select-filtro"
                onchange="alterarOrdem(this.value)"
            >


                <option
                    value="recentes"
                    <?php
                    echo $ordem === 'recentes'
                        ? 'selected'
                        : '';
                    ?>
                >
                    Mais recentes
                </option>


                <option
                    value="antigos"
                    <?php
                    echo $ordem === 'antigos'
                        ? 'selected'
                        : '';
                    ?>
                >
                    Mais antigos
                </option>


            </select>

        </div>


        <div class="quantidade-orcamentos">

            <?php
            echo $totalOrcamentos;
            ?>

            orçamento(s)

        </div>


    </div>



    <div class="tabela-orcamentos">


        <?php if (empty($orcamentos)): ?>


            <div class="sem-dados">

                <i class="bi bi-inbox"></i>

                <h4>
                    Nenhum orçamento recebido
                </h4>

            </div>


        <?php else: ?>


            <div class="table-responsive">


                <table class="table">


                    <thead>

                        <tr>

                            <th>Cliente</th>

                            <th>Telefone</th>

                            <th>Serviço</th>

                            <th>Mensagem</th>

                            <th>Data</th>

                            <th>Status</th>

                            <th>Ações</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($orcamentos as $orcamento): ?>


                        <?php

                        $status =
                            strtolower(
                                trim(
                                    $orcamento['status']
                                    ?? 'Pendente'
                                )
                            );


                        $concluido =
                            $status === 'concluído'
                            ||
                            $status === 'concluido';

                        ?>


                        <tr>


                            <td>

                                <div class="nome-cliente">

                                    <?php
                                    echo sanitizar(
                                        $orcamento['nome']
                                    );
                                    ?>

                                </div>


                                <div class="email-cliente">

                                    <?php
                                    echo sanitizar(
                                        $orcamento['email']
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <?php

                                echo sanitizar(
                                    $orcamento['telefone']
                                    ?: 'Não informado'
                                );

                                ?>

                            </td>


                            <td>

                                <span class="servico-orcamento">

                                    <?php
                                    echo sanitizar(
                                        $orcamento['servico']
                                        ?? 'Não informado'
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <div class="mensagem-orcamento">

                                    <?php

                                    echo nl2br(
                                        sanitizar(
                                            $orcamento['mensagem']
                                            ?? ''
                                        )
                                    );

                                    ?>

                                </div>

                            </td>


                            <td>

                                <?php
                                echo formatarData(
                                    $orcamento['criado_em']
                                );
                                ?>

                            </td>


                            <td>


                                <?php if ($concluido): ?>

                                    <span
                                        class="status status-concluido"
                                    >

                                        Concluído

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="status status-pendente"
                                    >

                                        Pendente

                                    </span>

                                <?php endif; ?>


                            </td>


                            <td>

                                <div class="acoes">


                                    <?php if (!$concluido): ?>


                                        <a
                                            href="admin_projeto_concluir.php?id=<?php
                                            echo (int) $orcamento['id'];
                                            ?>"
                                            class="btn-concluir"
                                        >

                                            <i class="bi bi-check-lg"></i>

                                            Concluir

                                        </a>


                                    <?php else: ?>


                                        <a
                                            href="admin_financeiro.php"
                                            class="btn-ver-financeiro"
                                        >

                                            <i class="bi bi-cash-stack"></i>

                                            Financeiro

                                        </a>


                                    <?php endif; ?>


                                    <a
                                        href="admin_orcamento_deletar.php?id=<?php
                                        echo (int) $orcamento['id'];
                                        ?>"
                                        class="btn-deletar"
                                        onclick="return confirmarExclusao();"
                                    >

                                        <i class="bi bi-trash"></i>

                                        Excluir

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </div>

</div>


<script>

function alterarOrdem(ordem) {

    window.location.href =
        'admin_orcamentos.php?ordem=' +
        encodeURIComponent(ordem);

}


function confirmarExclusao() {

    return confirm(
        'Deseja realmente excluir este orçamento?'
    );

}

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>