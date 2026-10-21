<?php
/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL - CONCLUIR ORÇAMENTO
 * ============================================================
 */

session_start();

/*
|--------------------------------------------------------------------------
| VERIFICAR LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usuario_admin']) ||
    empty($_SESSION['usuario_admin']['id'])
) {
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| CONEXÃO
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/functions.php';


/*
|--------------------------------------------------------------------------
| PEGAR ID DO ORÇAMENTO
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


/*
|--------------------------------------------------------------------------
| VALIDAR ID
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    header('Location: admin_orcamentos.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| ATUALIZAR STATUS
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE contatos
    SET status = ?
    WHERE id = ?
";


$stmt = $conexao->prepare($sql);


if (!$stmt) {

    die(
        'Erro ao preparar a atualização: ' .
        $conexao->error
    );
}


$status = 'Concluído';


$stmt->bind_param(
    'si',
    $status,
    $id
);


if ($stmt->execute()) {

    $stmt->close();

    header('Location: admin_orcamentos.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| ERRO
|--------------------------------------------------------------------------
*/

$erro = $stmt->error;

$stmt->close();

die(
    'Erro ao concluir o orçamento: ' .
    $erro
);