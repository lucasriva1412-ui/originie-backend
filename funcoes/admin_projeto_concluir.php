<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * CONCLUIR PROJETO
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


// ============================================================
// ID DO CONTATO
// ============================================================

$idContato =
    isset($_GET['id'])
        ? (int) $_GET['id']
        : 0;


if ($idContato <= 0) {

    header(
        'Location: admin_orcamentos.php?erro=' .
        urlencode(
            'Orçamento inválido.'
        )
    );

    exit;
}


// ============================================================
// BUSCAR ORÇAMENTO
// ============================================================

$sql = "
    SELECT

        c.id,
        c.servico,
        c.mensagem,
        c.status,

        cli.nome AS cliente,
        cli.email,
        cli.telefone

    FROM contatos c

    INNER JOIN clientes cli
        ON cli.id = c.id_cliente

    WHERE c.id = ?

    LIMIT 1
";


$stmt =
    $conexao->prepare(
        $sql
    );


if (!$stmt) {

    die(
        'Erro ao preparar orçamento: ' .
        $conexao->error
    );
}


$stmt->bind_param(
    'i',
    $idContato
);


$stmt->execute();


$orcamento =
    $stmt
        ->get_result()
        ->fetch_assoc();


$stmt->close();


if (!$orcamento) {

    header(
        'Location: admin_orcamentos.php?erro=' .
        urlencode(
            'Orçamento não encontrado.'
        )
    );

    exit;
}


// ============================================================
// VERIFICAR SE JÁ EXISTE NO FINANCEIRO
// ============================================================

$sqlExiste = "
    SELECT id

    FROM projetos_finalizados

    WHERE id_contato = ?

    LIMIT 1
";


$stmtExiste =
    $conexao->prepare(
        $sqlExiste
    );


$stmtExiste->bind_param(
    'i',
    $idContato
);


$stmtExiste->execute();


$jaExiste =
    $stmtExiste
        ->get_result()
        ->fetch_assoc();


$stmtExiste->close();


if ($jaExiste) {

    header(
        'Location: admin_financeiro.php?erro=' .
        urlencode(
            'Este projeto já está no financeiro.'
        )
    );

    exit;
}


// ============================================================
// CAMPOS
// ============================================================

$erro = '';

$valorFinal = '';

$dataConclusao =
    date('Y-m-d');

$observacao = '';


// ============================================================
// POST
// ============================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $valorFinal =
        trim(
            $_POST['valor_final']
            ?? ''
        );


    $valorFinal =
        str_replace(
            ',',
            '.',
            $valorFinal
        );


    $dataConclusao =
        trim(
            $_POST['data_conclusao']
            ?? ''
        );


    $observacao =
        trim(
            $_POST['observacao']
            ?? ''
        );


    // ========================================================
    // VALIDAR
    // ========================================================

    if (
        $valorFinal === '' ||
        !is_numeric($valorFinal)
    ) {

        $erro =
            'Informe um valor válido.';

    } elseif (
        (float) $valorFinal < 0
    ) {

        $erro =
            'O valor não pode ser negativo.';

    } elseif (
        empty($dataConclusao)
    ) {

        $erro =
            'Informe a data de conclusão.';

    } else {


        $valorFinal =
            (float) $valorFinal;


        $conexao->begin_transaction();


        try {


            // =================================================
            // INSERIR PROJETO FINALIZADO
            // =================================================

            $sqlInsert = "
                INSERT INTO projetos_finalizados
                (
                    id_contato,
                    valor_final,
                    data_conclusao,
                    observacao
                )

                VALUES
                (?, ?, ?, ?)
            ";


            $stmtInsert =
                $conexao->prepare(
                    $sqlInsert
                );


            if (!$stmtInsert) {

                throw new Exception(
                    'Erro ao preparar projeto: ' .
                    $conexao->error
                );
            }


            $stmtInsert->bind_param(
                'idss',
                $idContato,
                $valorFinal,
                $dataConclusao,
                $observacao
            );


            if (!$stmtInsert->execute()) {

                throw new Exception(
                    'Erro ao inserir projeto: ' .
                    $stmtInsert->error
                );
            }


            $stmtInsert->close();



            // =================================================
            // ATUALIZAR STATUS
            // =================================================

            $status =
                'Concluído';


            $sqlStatus = "
                UPDATE contatos

                SET status = ?

                WHERE id = ?
            ";


            $stmtStatus =
                $conexao->prepare(
                    $sqlStatus
                );


            if (!$stmtStatus) {

                throw new Exception(
                    'Erro ao preparar atualização.'
                );
            }


            $stmtStatus->bind_param(
                'si',
                $status,
                $idContato
            );


            if (!$stmtStatus->execute()) {

                throw new Exception(
                    'Erro ao concluir orçamento.'
                );
            }


            $stmtStatus->close();



            // =================================================
            // CONFIRMAR
            // =================================================

            $conexao->commit();



            // =================================================
            // ABRIR O MESMO MÊS NO FINANCEIRO
            // =================================================

            $dataPartes =
                explode(
                    '-',
                    $dataConclusao
                );


            $anoFinanceiro =
                isset($dataPartes[0])
                    ? (int) $dataPartes[0]
                    : (int) date('Y');


            $mesFinanceiro =
                isset($dataPartes[1])
                    ? (int) $dataPartes[1]
                    : (int) date('m');


            header(
                'Location: admin_financeiro.php' .
                '?mes=' . $mesFinanceiro .
                '&ano=' . $anoFinanceiro .
                '&mensagem=' .
                urlencode(
                    'Projeto concluído e adicionado ao financeiro.'
                )
            );


            exit;


        } catch (Throwable $erroBanco) {


            $conexao->rollback();


            $erro =
                $erroBanco->getMessage();

        }

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
        Concluir Projeto — Originie
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


<main class="conteudo-financeiro">

    <div class="container">


        <div class="cabecalho-financeiro">


            <div>

                <span class="etiqueta-financeiro">

                    FINALIZAR PROJETO

                </span>


                <h1>

                    Concluir Projeto

                </h1>


                <p>

                    Registre o valor final para
                    adicionar o projeto ao financeiro.

                </p>

            </div>


            <a
                href="admin_orcamentos.php"
                class="btn-financeiro-secundario"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>


        </div>



        <?php if (!empty($erro)): ?>

            <div class="alert alert-danger">

                <?php
                echo sanitizar(
                    $erro
                );
                ?>

            </div>

        <?php endif; ?>



        <section class="painel-financeiro">


            <div class="resumo-projeto">

                <h2>

                    Dados do projeto

                </h2>


                <div class="info-projeto">


                    <div>

                        <span>
                            Cliente
                        </span>

                        <strong>

                            <?php
                            echo sanitizar(
                                $orcamento['cliente']
                            );
                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            Serviço
                        </span>

                        <strong>

                            <?php
                            echo sanitizar(
                                $orcamento['servico']
                                ?? 'Não informado'
                            );
                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            Email
                        </span>

                        <strong>

                            <?php
                            echo sanitizar(
                                $orcamento['email']
                            );
                            ?>

                        </strong>

                    </div>


                </div>

            </div>



            <form method="POST">


                <div class="row g-4">


                    <div class="col-md-6">

                        <label
                            for="valor_final"
                            class="form-label"
                        >

                            Valor final do projeto

                        </label>


                        <input
                            type="number"
                            name="valor_final"
                            id="valor_final"
                            class="form-control campo-financeiro"
                            min="0"
                            step="0.01"
                            value="<?php
                            echo sanitizar(
                                $valorFinal
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label
                            for="data_conclusao"
                            class="form-label"
                        >

                            Data de conclusão

                        </label>


                        <input
                            type="date"
                            name="data_conclusao"
                            id="data_conclusao"
                            class="form-control campo-financeiro"
                            value="<?php
                            echo sanitizar(
                                $dataConclusao
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="col-12">

                        <label
                            for="observacao"
                            class="form-label"
                        >

                            Observação

                        </label>


                        <textarea
                            name="observacao"
                            id="observacao"
                            class="form-control campo-financeiro"
                            rows="4"
                        ><?php
                        echo sanitizar(
                            $observacao
                        );
                        ?></textarea>

                    </div>


                </div>


                <div class="acoes-formulario-financeiro">


                    <a
                        href="admin_orcamentos.php"
                        class="btn-financeiro-secundario"
                    >

                        Cancelar

                    </a>


                    <button
                        type="submit"
                        class="btn-financeiro-principal"
                    >

                        <i class="bi bi-check-circle"></i>

                        Concluir Projeto

                    </button>


                </div>


            </form>


        </section>


    </div>

</main>


</body>

</html>