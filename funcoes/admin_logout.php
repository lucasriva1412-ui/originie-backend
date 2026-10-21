<?php
/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL - LOGOUT DO ADMINISTRADOR
 * ============================================================
 */

session_start();

// Limpar todas as variáveis da sessão
$_SESSION = [];

// Destruir a sessão
session_destroy();

// Voltar para a página de login
header('Location: login.php');
exit;
?>