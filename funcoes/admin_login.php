<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * LOGIN ADMINISTRATIVO
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
// SE JÁ FOR ADMINISTRADOR
// ============================================================

if (usuarioEhAdmin()) {

    header(
        'Location: admin_dashboard.php'
    );

    exit;
}


// ============================================================
// VARIÁVEIS
// ============================================================

$erro = '';

$email = '';


// ============================================================
// PROCESSAR LOGIN
// ============================================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $email =
        trim(
            $_POST['email'] ?? ''
        );

    $senha =
        $_POST['senha'] ?? '';


    // ========================================================
    // VALIDAR CAMPOS
    // ========================================================

    if (
        empty($email) ||
        empty($senha)
    ) {

        $erro =
            'Preencha o email e a senha.';

    } else {


        // ====================================================
        // AUTENTICAR ADMIN
        // ====================================================

        $resultado =
            autenticarAdmin(
                $conexao,
                $email,
                $senha
            );


        // ====================================================
        // LOGIN CORRETO
        // ====================================================

        if (
            isset($resultado['sucesso']) &&
            $resultado['sucesso'] === true &&
            isset($resultado['usuario']) &&
            is_array($resultado['usuario'])
        ) {


            // ================================================
            // CRIAR AS SESSÕES
            // ================================================

            iniciarSessaoAdmin(
                $resultado['usuario']
            );


            // ================================================
            // REDIRECIONAR
            // ================================================

            header(
                'Location: admin_dashboard.php'
            );

            exit;

        }


        // ====================================================
        // LOGIN INCORRETO
        // ====================================================

        $erro =
            $resultado['mensagem']
            ?? 'Email ou senha incorretos.';

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
        Login Administrativo — Originie Audiovisual
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >


    <!-- Google Fonts -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >


    <!-- CSS principal -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- CSS login administrativo -->

    <link
        rel="stylesheet"
        href="assets/css/admin_login.css"
    >

</head>


<body class="pagina-admin-login">


<div class="login-container">

    <div class="login-box">


        <!-- ====================================================
             TÍTULO
        ===================================================== -->

        <div class="login-titulo">

            <i class="bi bi-shield-lock"></i>

            <h1>
                Área Administrativa
            </h1>

            <p>
                Originie Audiovisual
            </p>

        </div>


        <!-- ====================================================
             ERRO
        ===================================================== -->

        <?php if (!empty($erro)): ?>

            <div class="alerta-erro">

                <i
                    class="bi bi-exclamation-circle me-2"
                ></i>

                <?php
                echo sanitizar($erro);
                ?>

            </div>

        <?php endif; ?>


        <!-- ====================================================
             FORMULÁRIO
        ===================================================== -->

        <form
            method="POST"
            action="admin_login.php"
        >


            <!-- EMAIL -->

            <div class="campo">

                <label for="email">

                    <i class="bi bi-envelope me-1"></i>

                    Email

                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php
                    echo sanitizar($email);
                    ?>"
                    placeholder="Digite seu email"
                    required
                    autocomplete="email"
                >

            </div>


            <!-- SENHA -->

            <div class="campo">

                <label for="senha">

                    <i class="bi bi-lock me-1"></i>

                    Senha

                </label>


                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    required
                    autocomplete="current-password"
                >

            </div>


            <!-- BOTÃO -->

            <button
                type="submit"
                class="botao-login"
            >

                <i
                    class="bi bi-box-arrow-in-right me-2"
                ></i>

                Entrar

            </button>


        </form>


        <!-- ====================================================
             VOLTAR
        ===================================================== -->

        <a
            href="index.php"
            class="voltar-site"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Voltar para o site

        </a>


    </div>

</div>


<!-- Bootstrap -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>