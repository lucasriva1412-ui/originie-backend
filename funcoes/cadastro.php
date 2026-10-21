<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL - CADASTRO
 * ============================================================
 * Cadastro de usuários comuns
 */

session_start();

require_once 'conexao.php';
require_once 'functions.php';


// ============================================================
// SE JÁ ESTIVER LOGADO
// ============================================================

if (
    isset($_SESSION['usuario']) &&
    !empty($_SESSION['usuario']['id'])
) {

    header('Location: index.php');
    exit;
}


// ============================================================
// VARIÁVEIS
// ============================================================

$mensagem_erro = '';
$mensagem_sucesso = '';

$nome = '';
$email = '';


// ============================================================
// PROCESSAR CADASTRO
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';


    // ========================================================
    // VALIDAR CAMPOS
    // ========================================================

    if (
        empty($nome) ||
        empty($email) ||
        empty($senha) ||
        empty($confirmarSenha)
    ) {

        $mensagem_erro =
            'Preencha todos os campos.';

    }


    // ========================================================
    // VALIDAR NOME
    // ========================================================

    elseif (!validarNome($nome)) {

        $mensagem_erro =
            'Nome inválido. Informe seu nome completo.';

    }


    // ========================================================
    // VALIDAR EMAIL
    // ========================================================

    elseif (!validarEmail($email)) {

        $mensagem_erro =
            'Informe um email válido.';

    }


    // ========================================================
    // VALIDAR SENHA
    // ========================================================

    elseif (strlen($senha) < 6) {

        $mensagem_erro =
            'A senha deve possuir pelo menos 6 caracteres.';

    }


    // ========================================================
    // CONFIRMAR SENHA
    // ========================================================

    elseif ($senha !== $confirmarSenha) {

        $mensagem_erro =
            'As senhas não coincidem.';

    }


    // ========================================================
    // VERIFICAR EMAIL EXISTENTE
    // ========================================================

    else {

        $usuarioExistente =
            buscarUsuarioPorEmail(
                $conexao,
                $email
            );


        if (!empty($usuarioExistente)) {

            $mensagem_erro =
                'Este email já possui uma conta cadastrada.';

        }


        // ====================================================
        // CRIAR USUÁRIO
        // ====================================================

        else {

            /*
             * IMPORTANTE:
             *
             * A função correta existente no functions.php
             * é criarUsuario().
             *
             * O tipo "usuario" é definido aqui para impedir
             * que alguém consiga criar uma conta admin pelo
             * formulário público.
             */

            $resultado =
                criarUsuario(
                    $conexao,
                    $nome,
                    $email,
                    $senha,
                    'usuario'
                );


            if ($resultado['sucesso']) {

                $mensagem_sucesso =
                    'Conta criada com sucesso! Você já pode entrar.';

                /*
                 * Limpar os campos após o cadastro.
                 */
                $nome = '';
                $email = '';

            } else {

                $mensagem_erro =
                    $resultado['mensagem'];

            }

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
        Criar Conta — Originie Audiovisual
    </title>


    <!-- ========================================================
         BOOTSTRAP
    ========================================================= -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ========================================================
         BOOTSTRAP ICONS
    ========================================================= -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >


    <!-- ========================================================
         GOOGLE FONTS
    ========================================================= -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >


    <!-- ========================================================
         ESTILO
    ========================================================= -->

    <link rel="stylesheet" href="assets/css/cadastro.css">

</head>


<body>


<!-- ============================================================
     CADASTRO
============================================================ -->

<div class="container-cadastro">

    <div class="cartao-cadastro">


        <!-- ====================================================
             LOGO
        ===================================================== -->

        <div class="logo-cadastro">

            <?php if (file_exists('assets/Imagens/logo.png')): ?>

                <img
                    src="assets/Imagens/logo.png"
                    alt="Originie Audiovisual"
                >

            <?php endif; ?>

        </div>


        <!-- ====================================================
             ÍCONE
        ===================================================== -->

        <div class="icone-cadastro">

            <i class="bi bi-person-plus"></i>

        </div>


        <!-- ====================================================
             TÍTULO
        ===================================================== -->

        <h1 class="titulo-cadastro">

            Criar Conta

        </h1>


        <p class="subtitulo-cadastro">

            Faça seu cadastro na Originie

        </p>


        <!-- ====================================================
             MENSAGEM DE ERRO
        ===================================================== -->

        <?php if (!empty($mensagem_erro)): ?>

            <div class="alerta-erro">

                <i
                    class="bi bi-exclamation-circle me-2"
                ></i>

                <?php

                echo sanitizar(
                    $mensagem_erro
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- ====================================================
             MENSAGEM DE SUCESSO
        ===================================================== -->

        <?php if (!empty($mensagem_sucesso)): ?>

            <div class="alerta-sucesso">

                <i
                    class="bi bi-check-circle me-2"
                ></i>

                <?php

                echo sanitizar(
                    $mensagem_sucesso
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- ====================================================
             FORMULÁRIO
        ===================================================== -->

        <form
            method="POST"
            action=""
        >


            <!-- =================================================
                 NOME
            ================================================== -->

            <div class="grupo-formulario">

                <label for="campoNome">

                    <i
                        class="bi bi-person me-1"
                    ></i>

                    Nome

                </label>


                <input
                    type="text"
                    id="campoNome"
                    name="nome"
                    class="entrada-cadastro"
                    placeholder="Seu nome completo"
                    value="<?php echo sanitizar($nome); ?>"
                    maxlength="100"
                    required
                    autofocus
                >

            </div>


            <!-- =================================================
                 EMAIL
            ================================================== -->

            <div class="grupo-formulario">

                <label for="campoEmail">

                    <i
                        class="bi bi-envelope me-1"
                    ></i>

                    Email

                </label>


                <input
                    type="email"
                    id="campoEmail"
                    name="email"
                    class="entrada-cadastro"
                    placeholder="seu@email.com"
                    value="<?php echo sanitizar($email); ?>"
                    maxlength="150"
                    required
                >

            </div>


            <!-- =================================================
                 SENHA
            ================================================== -->

            <div class="grupo-formulario">

                <label for="campoSenha">

                    <i
                        class="bi bi-key me-1"
                    ></i>

                    Senha

                </label>


                <input
                    type="password"
                    id="campoSenha"
                    name="senha"
                    class="entrada-cadastro"
                    placeholder="Mínimo de 6 caracteres"
                    minlength="6"
                    required
                >

            </div>


            <!-- =================================================
                 CONFIRMAR SENHA
            ================================================== -->

            <div class="grupo-formulario">

                <label for="campoConfirmarSenha">

                    <i
                        class="bi bi-shield-check me-1"
                    ></i>

                    Confirmar senha

                </label>


                <input
                    type="password"
                    id="campoConfirmarSenha"
                    name="confirmar_senha"
                    class="entrada-cadastro"
                    placeholder="Digite a senha novamente"
                    minlength="6"
                    required
                >

            </div>


            <!-- =================================================
                 BOTÃO
            ================================================== -->

            <button
                type="submit"
                class="botao-cadastro"
            >

                <i
                    class="bi bi-person-plus me-2"
                ></i>

                Criar minha conta

            </button>


        </form>


        <!-- ====================================================
             JÁ POSSUI CONTA
        ===================================================== -->

        <div class="link-login">

            <p>

                Já possui uma conta?

            </p>


            <a href="login.php">

                <i
                    class="bi bi-box-arrow-in-right me-1"
                ></i>

                Entrar na minha conta

            </a>

        </div>


        <!-- ====================================================
             VOLTAR
        ===================================================== -->

        <div class="link-voltar">

            <a href="index.php">

                <i
                    class="bi bi-arrow-left me-1"
                ></i>

                Voltar para o site

            </a>

        </div>


    </div>

</div>


<!-- ============================================================
     BOOTSTRAP JS
============================================================ -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>