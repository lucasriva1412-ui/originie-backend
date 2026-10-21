<?php

// Dados do banco
$nomeServidor = "127.0.0.1";
$usuario = "LucasRiva";
$senha = "D1str1to";
$nomeBancoDados = "originie";

// Criar conexão
$conexao = new mysqli(
    $nomeServidor,
    $usuario,
    $senha,
    $nomeBancoDados
);

// Verificar conexão
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

// Configurar charset
$conexao->set_charset("utf8mb4");

?>