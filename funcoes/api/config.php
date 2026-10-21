<?php

$host = 'localhost';
$dbname = 'originie';
$user = 'root';
$pass = '';

try {

    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $pdo->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );

} catch (PDOException $erro) {

    http_response_code(500);

    header(
        'Content-Type: application/json; charset=UTF-8'
    );

    echo json_encode(
        [
            'sucesso' => false,
            'mensagem' => 'Erro ao conectar com o banco de dados.'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}