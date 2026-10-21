<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * EXCLUIR SERVIÇO
 * ============================================================
 */


// ============================================================
// INICIAR SESSÃO
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ============================================================
// CONEXÃO E FUNÇÕES
// ============================================================

require_once 'conexao.php';
require_once 'functions.php';


// ============================================================
// VERIFICAR ADMINISTRADOR
// ============================================================

if (
    !isset($_SESSION['usuario']) ||
    !is_array($_SESSION['usuario']) ||
    empty($_SESSION['usuario']['id']) ||
    !isset($_SESSION['usuario']['tipo']) ||
    $_SESSION['usuario']['tipo'] !== 'admin'
) {

    header('Location: login.php');

    exit;
}


// ============================================================
// RECEBER ID
// ============================================================

$id_servico =
    isset($_GET['id'])
        ? (int) $_GET['id']
        : 0;


// ============================================================
// VALIDAR ID
// ============================================================

if ($id_servico <= 0) {

    header(
        'Location: admin_dashboard.php?erro=' .
        urlencode(
            'Serviço inválido.'
        )
    );

    exit;
}


// ============================================================
// VERIFICAR SE O SERVIÇO EXISTE
// ============================================================

$servico =
    buscarServicoPorId(
        $conexao,
        $id_servico
    );


if (empty($servico)) {

    header(
        'Location: admin_dashboard.php?erro=' .
        urlencode(
            'Serviço não encontrado.'
        )
    );

    exit;
}


// ============================================================
// INICIAR TRANSAÇÃO
// ============================================================

$conexao->begin_transaction();


try {


    // ========================================================
    // APAGAR RELAÇÕES COM SERVICOS_INTERESSE
    // ========================================================

    $sqlInteresses = "
        DELETE FROM servicos_interesse
        WHERE id_servico = ?
    ";


    $stmtInteresses =
        $conexao->prepare(
            $sqlInteresses
        );


    if (!$stmtInteresses) {

        throw new Exception(
            'Erro ao preparar exclusão dos vínculos.'
        );
    }


    $stmtInteresses->bind_param(
        'i',
        $id_servico
    );


    if (!$stmtInteresses->execute()) {

        throw new Exception(
            'Erro ao excluir vínculos do serviço.'
        );
    }


    $stmtInteresses->close();


    // ========================================================
    // APAGAR SERVIÇO
    // ========================================================

    $sqlServico = "
        DELETE FROM servicos_cat
        WHERE id = ?
    ";


    $stmtServico =
        $conexao->prepare(
            $sqlServico
        );


    if (!$stmtServico) {

        throw new Exception(
            'Erro ao preparar exclusão do serviço.'
        );
    }


    $stmtServico->bind_param(
        'i',
        $id_servico
    );


    if (!$stmtServico->execute()) {

        throw new Exception(
            'Erro ao excluir o serviço.'
        );
    }


    // ========================================================
    // VERIFICAR SE REALMENTE APAGOU
    // ========================================================

    if ($stmtServico->affected_rows <= 0) {

        throw new Exception(
            'Nenhum serviço foi excluído.'
        );
    }


    $stmtServico->close();


    // ========================================================
    // CONFIRMAR TRANSAÇÃO
    // ========================================================

    $conexao->commit();


    // ========================================================
    // VOLTAR PARA DASHBOARD
    // ========================================================

    header(
        'Location: admin_dashboard.php?mensagem=' .
        urlencode(
            'Serviço excluído com sucesso.'
        )
    );

    exit;


} catch (Throwable $erro) {


    // ========================================================
    // DESFAZER ALTERAÇÕES
    // ========================================================

    $conexao->rollback();


    // ========================================================
    // REDIRECIONAR COM ERRO
    // ========================================================

    header(
        'Location: admin_dashboard.php?erro=' .
        urlencode(
            $erro->getMessage()
        )
    );

    exit;

}