<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * EDITAR PROJETO FINALIZADO
 * ============================================================
 */


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once 'conexao.php';
require_once 'functions.php';


if (
    !isset($_SESSION['usuario']) ||
    !is_array($_SESSION['usuario']) ||
    empty($_SESSION['usuario']['id']) ||
    ($_SESSION['usuario']['tipo'] ?? '') !== 'admin'
) {

    header('Location: login.php');
    exit;
}


$id =
    isset($_GET['id'])
        ? (int) $_GET['id']
        : 0;


if ($id <= 0) {

    header(
        'Location: admin_financeiro.php?erro=' .
        urlencode('Projeto inválido.')
    );

    exit;
}


// ============================================================
// BUSCAR
// ============================================================

$sql = "
    SELECT

        pf.*,

        c.servico,

        cli.nome AS cliente

    FROM projetos_finalizados pf

    INNER JOIN contatos c
        ON c.id = pf.id_contato

    INNER JOIN clientes cli
        ON cli.id = c.id_cliente

    WHERE pf.id = ?

    LIMIT 1
";


$stmt =
    $conexao->prepare(
        $sql
    );

$stmt->bind_param(
    'i',
    $id
);

$stmt->execute();

$projeto =
    $stmt
        ->get_result()
        ->fetch_assoc();

$stmt->close();


if (!$projeto) {

    header(
        'Location: admin_financeiro.php?erro=' .
        urlencode(
            'Projeto não encontrado.'
        )
    );

    exit;
}


$erro = '';


// ============================================================
// ATUALIZAR
// ============================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $valor =
        $_POST['valor_final']
        ?? '';

    $data =
        trim(
            $_POST['data_conclusao']
            ?? ''
        );

    $observacao =
        trim(
            $_POST['observacao']
            ?? ''
        );


    if (
        $valor === '' ||
        !is_numeric($valor) ||
        (float) $valor < 0
    ) {

        $erro =
            'Informe um valor válido.';

    } elseif ($data === '') {

        $erro =
            'Informe a data de conclusão.';

    } else {

        $valor =
            (float) $valor;


        $sqlAtualizar = "
            UPDATE projetos_finalizados

            SET
                valor_final = ?,
                data_conclusao = ?,
                observacao = ?

            WHERE id = ?
        ";


        $stmtAtualizar =
            $conexao->prepare(
                $sqlAtualizar
            );


        $stmtAtualizar->bind_param(
            'dssi',
            $valor,
            $data,
            $observacao,
            $id
        );


        if (
            $stmtAtualizar->execute()
        ) {

            $stmtAtualizar->close();


            header(
                'Location: admin_financeiro.php?mensagem=' .
                urlencode(
                    'Projeto atualizado com sucesso.'
                )
            );

            exit;
        }


        $erro =
            'Erro ao atualizar projeto.';

        $stmtAtualizar->close();

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
        Editar Projeto — Originie Audiovisual
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
                    EDITAR
                </span>

                <h1>
                    Editar Projeto
                </h1>

                <p>

                    <?php
                    echo sanitizar(
                        $projeto['cliente']
                    );
                    ?>

                    —

                    <?php
                    echo sanitizar(
                        $projeto['servico']
                    );
                    ?>

                </p>

            </div>

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


            <form method="POST">


                <div class="row g-4">


                    <div class="col-md-6">

                        <label class="form-label">
                            Valor final
                        </label>

                        <input
                            type="number"
                            name="valor_final"
                            class="form-control campo-financeiro"
                            step="0.01"
                            min="0"
                            value="<?php
                            echo sanitizar(
                                $projeto['valor_final']
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Data da conclusão
                        </label>

                        <input
                            type="date"
                            name="data_conclusao"
                            class="form-control campo-financeiro"
                            value="<?php
                            echo sanitizar(
                                $projeto['data_conclusao']
                            );
                            ?>"
                            required
                        >

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Observação
                        </label>

                        <textarea
                            name="observacao"
                            class="form-control campo-financeiro"
                            rows="4"
                        ><?php
                        echo sanitizar(
                            $projeto['observacao']
                            ?? ''
                        );
                        ?></textarea>

                    </div>


                </div>


                <div class="acoes-formulario-financeiro">


                    <a
                        href="admin_financeiro.php"
                        class="btn-financeiro-secundario"
                    >
                        Cancelar
                    </a>


                    <button
                        type="submit"
                        class="btn-financeiro-principal"
                    >

                        <i class="bi bi-save"></i>

                        Salvar alterações

                    </button>


                </div>


            </form>


        </section>


    </div>

</main>


</body>

</html>