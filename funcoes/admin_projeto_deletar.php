<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * EXCLUIR PROJETO FINALIZADO
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
        urlencode('Registro inválido.')
    );

    exit;
}


// ============================================================
// DESCOBRIR CONTATO
// ============================================================

$sqlBuscar = "
    SELECT id_contato

    FROM projetos_finalizados

    WHERE id = ?

    LIMIT 1
";


$stmtBuscar =
    $conexao->prepare(
        $sqlBuscar
    );

$stmtBuscar->bind_param(
    'i',
    $id
);

$stmtBuscar->execute();

$registro =
    $stmtBuscar
        ->get_result()
        ->fetch_assoc();

$stmtBuscar->close();


if (!$registro) {

    header(
        'Location: admin_financeiro.php?erro=' .
        urlencode(
            'Projeto não encontrado.'
        )
    );

    exit;
}


$idContato =
    (int) $registro['id_contato'];


// ============================================================
// TRANSAÇÃO
// ============================================================

$conexao->begin_transaction();


try {


    // ========================================================
    // APAGAR REGISTRO FINANCEIRO
    // ========================================================

    $sqlDelete = "
        DELETE FROM projetos_finalizados
        WHERE id = ?
    ";


    $stmtDelete =
        $conexao->prepare(
            $sqlDelete
        );


    $stmtDelete->bind_param(
        'i',
        $id
    );


    if (!$stmtDelete->execute()) {

        throw new Exception(
            'Erro ao excluir o projeto.'
        );
    }


    $stmtDelete->close();


    // ========================================================
    // VOLTAR ORÇAMENTO PARA PENDENTE
    // ========================================================

    $status =
        'Pendente';


    $sqlContato = "
        UPDATE contatos

        SET status = ?

        WHERE id = ?
    ";


    $stmtContato =
        $conexao->prepare(
            $sqlContato
        );


    $stmtContato->bind_param(
        'si',
        $status,
        $idContato
    );


    if (!$stmtContato->execute()) {

        throw new Exception(
            'Erro ao atualizar orçamento.'
        );
    }


    $stmtContato->close();


    $conexao->commit();


    header(
        'Location: admin_financeiro.php?mensagem=' .
        urlencode(
            'Registro excluído e orçamento reaberto.'
        )
    );

    exit;


} catch (Throwable $erro) {


    $conexao->rollback();


    header(
        'Location: admin_financeiro.php?erro=' .
        urlencode(
            $erro->getMessage()
        )
    );

    exit;

}