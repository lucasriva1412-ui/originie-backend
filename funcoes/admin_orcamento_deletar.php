<?php

session_start();

require_once 'conexao.php';
require_once 'functions.php';


if (
    !isset($_SESSION['usuario']) ||
    !isset($_SESSION['usuario']['tipo']) ||
    $_SESSION['usuario']['tipo'] !== 'admin'
) {
    header('Location: login.php');
    exit;
}


$idContato = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($idContato <= 0) {

    header(
        'Location: admin_orcamentos.php?erro=' .
        urlencode('Orçamento inválido.')
    );

    exit;
}


try {

    $conexao->begin_transaction();


    /*
    ==========================================================
    EXCLUIR RELACIONAMENTOS COM SERVIÇOS
    ==========================================================
    */

    $sqlInteresses = "
        DELETE FROM servicos_interesse
        WHERE id_contato = ?
    ";

    $stmtInteresses = $conexao->prepare($sqlInteresses);

    if (!$stmtInteresses) {
        throw new Exception(
            'Erro ao preparar exclusão dos serviços relacionados.'
        );
    }

    $stmtInteresses->bind_param(
        'i',
        $idContato
    );

    if (!$stmtInteresses->execute()) {

        $erro = $stmtInteresses->error;

        $stmtInteresses->close();

        throw new Exception(
            'Erro ao excluir serviços relacionados: ' .
            $erro
        );
    }

    $stmtInteresses->close();


    /*
    ==========================================================
    EXCLUIR ORÇAMENTO
    ==========================================================
    */

    $sqlContato = "
        DELETE FROM contatos
        WHERE id = ?
    ";

    $stmtContato = $conexao->prepare($sqlContato);

    if (!$stmtContato) {
        throw new Exception(
            'Erro ao preparar exclusão do orçamento.'
        );
    }

    $stmtContato->bind_param(
        'i',
        $idContato
    );

    if (!$stmtContato->execute()) {

        $erro = $stmtContato->error;

        $stmtContato->close();

        throw new Exception(
            'Erro ao excluir orçamento: ' .
            $erro
        );
    }


    if ($stmtContato->affected_rows === 0) {

        $stmtContato->close();

        throw new Exception(
            'Orçamento não encontrado.'
        );
    }

    $stmtContato->close();


    /*
    ==========================================================
    CONFIRMAR ALTERAÇÕES
    ==========================================================
    */

    $conexao->commit();


    header(
        'Location: admin_orcamentos.php?mensagem=' .
        urlencode('Orçamento excluído com sucesso.')
    );

    exit;


} catch (Throwable $erro) {

    try {

        $conexao->rollback();

    } catch (Throwable $erroRollback) {
    }


    header(
        'Location: admin_orcamentos.php?erro=' .
        urlencode($erro->getMessage())
    );

    exit;
}