<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL - LOGIN / CADASTRO
 * ============================================================
 * Login de usuários e administradores
 * Cadastro público de usuários comuns
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$modo = $_GET['modo'] ?? 'login';

if ($modo !== 'cadastro') {
    $modo = 'login';
}


// ============================================================
// PROCESSAR FORMULÁRIO
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? 'login';


    // ========================================================
    // LOGIN
    // ========================================================

    if ($acao === 'login') {

        $modo = 'login';

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';


        // ----------------------------------------------------
        // VALIDAR CAMPOS
        // ----------------------------------------------------

        if (
            empty($email) ||
            empty($senha)
        ) {

            $mensagem_erro =
                'Email e senha são obrigatórios.';

        } elseif (!validarEmail($email)) {

            $mensagem_erro =
                'Informe um email válido.';

        } else {


            // ------------------------------------------------
            // AUTENTICAR USUÁRIO
            // ------------------------------------------------

            $resultado =
                autenticarUsuario(
                    $conexao,
                    $email,
                    $senha
                );


            if ($resultado['sucesso']) {

                // --------------------------------------------
                // CRIAR SESSÃO
                // --------------------------------------------

                iniciarSessaoUsuario(
                    $resultado['usuario']
                );


                // --------------------------------------------
                // IR PARA O SITE
                // --------------------------------------------

                header('Location: index.php');
                exit;


            } else {

                $mensagem_erro =
                    $resultado['mensagem'];
            }
        }
    }


    // ========================================================
    // CADASTRO
    // ========================================================

    elseif ($acao === 'cadastro') {

        $modo = 'cadastro';


        $nome =
            trim(
                $_POST['nome'] ?? ''
            );

        $email =
            trim(
                $_POST['email'] ?? ''
            );

        $senha =
            $_POST['senha'] ?? '';

        $confirmarSenha =
            $_POST['confirmar_senha'] ?? '';


        // ----------------------------------------------------
        // VALIDAR CAMPOS
        // ----------------------------------------------------

        if (
            empty($nome) ||
            empty($email) ||
            empty($senha) ||
            empty($confirmarSenha)
        ) {

            $mensagem_erro =
                'Preencha todos os campos.';

        }

        elseif (!validarNome($nome)) {

            $mensagem_erro =
                'Nome inválido. Use pelo menos 3 caracteres.';

        }

        elseif (!validarEmail($email)) {

            $mensagem_erro =
                'Informe um email válido.';

        }

        elseif (strlen($senha) < 6) {

            $mensagem_erro =
                'A senha deve possuir pelo menos 6 caracteres.';

        }

        elseif ($senha !== $confirmarSenha) {

            $mensagem_erro =
                'As senhas não coincidem.';

        }

        else {


            // ------------------------------------------------
            // CRIAR USUÁRIO
            // ------------------------------------------------
            //
            // IMPORTANTE:
            // O tipo é SEMPRE "usuario".
            //
            // Dessa forma, ninguém consegue criar uma conta
            // administrativa através do cadastro público.
            // ------------------------------------------------

            $resultado =
                criarUsuario(
                    $conexao,
                    $nome,
                    $email,
                    $senha,
                    'usuario'
                );


            if ($resultado['sucesso']) {


                // --------------------------------------------
                // BUSCAR USUÁRIO CRIADO
                // --------------------------------------------

                $usuario =
                    buscarUsuarioPorEmail(
                        $conexao,
                        $email
                    );


                if (!empty($usuario)) {

                    // ----------------------------------------
                    // CRIAR SESSÃO AUTOMATICAMENTE
                    // ----------------------------------------

                    iniciarSessaoUsuario(
                        $usuario
                    );


                    // ----------------------------------------
                    // IR PARA O SITE
                    // ----------------------------------------

                    header('Location: index.php');
                    exit;

                } else {

                    $mensagem_sucesso =
                        'Conta criada com sucesso! Agora você já pode entrar.';

                    $modo = 'login';
                }


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

        <?php

        echo $modo === 'cadastro'
            ? 'Criar Conta'
            : 'Login';

        ?>

        — Originie Audiovisual

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
         CSS
    ========================================================= -->

    <link rel="stylesheet" href="assets/css/login.css">

</head>


<body>


<!-- ============================================================
     CONTAINER
============================================================ -->

<div class="container-login">

    <div class="cartao-login">


        <!-- ====================================================
             LOGO
        ===================================================== -->

        <div class="logo-login">

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

        <div class="icone-login">

            <?php if ($modo === 'cadastro'): ?>

                <i class="bi bi-person-plus-fill"></i>

            <?php else: ?>

                <i class="bi bi-person-circle"></i>

            <?php endif; ?>

        </div>


        <!-- ====================================================
             TÍTULO
        ===================================================== -->

        <h1 class="titulo-login">

            <?php

            echo $modo === 'cadastro'
                ? 'Criar sua conta'
                : 'Bem-vindo';

            ?>

        </h1>


        <p class="subtitulo-login">

            <?php

            echo $modo === 'cadastro'
                ? 'Cadastre-se na Originie'
                : 'Entre para acessar a Originie';

            ?>

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
             FORMULÁRIO DE LOGIN
        ===================================================== -->

        <?php if ($modo === 'login'): ?>


            <form
                method="POST"
                action="login.php"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="login"
                >


                <!-- EMAIL -->

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
                        class="entrada-login"
                        placeholder="seu@email.com"
                        value="<?php

                            echo sanitizar(
                                $_POST['email'] ?? ''
                            );

                        ?>"
                        required
                        autofocus
                    >

                </div>


                <!-- SENHA -->

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
                        class="entrada-login"
                        placeholder="Sua senha"
                        required
                    >

                </div>


                <!-- BOTÃO -->

                <button
                    type="submit"
                    class="botao-entrar"
                >

                    <i
                        class="bi bi-box-arrow-in-right me-2"
                    ></i>

                    Entrar

                </button>


            </form>


            <!-- =================================================
                 CADASTRO
            ================================================== -->

            <div class="divisor">

                ou

            </div>


            <div class="area-cadastro">

                <div class="texto-cadastro">

                    Ainda não possui uma conta?

                </div>


                <a
                    href="login.php?modo=cadastro"
                    class="botao-secundario"
                >

                    <i
                        class="bi bi-person-plus me-2"
                    ></i>

                    Criar uma conta

                </a>

            </div>


            <!-- =================================================
                 DICA
            ================================================== -->

            <div class="dica-login">

                <i
                    class="bi bi-info-circle me-2"
                ></i>

                <strong>Acesso seguro</strong>

                <br>

                Entre com seu email e senha
                cadastrados para acessar o site.

            </div>


        <?php else: ?>


            <!-- =================================================
                 FORMULÁRIO DE CADASTRO
            ================================================== -->

            <form
                method="POST"
                action="login.php?modo=cadastro"
            >

                <input
                    type="hidden"
                    name="acao"
                    value="cadastro"
                >


                <!-- NOME -->

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
                        class="entrada-login"
                        placeholder="Seu nome completo"
                        value="<?php

                            echo sanitizar(
                                $_POST['nome'] ?? ''
                            );

                        ?>"
                        maxlength="100"
                        required
                        autofocus
                    >

                </div>


                <!-- EMAIL -->

                <div class="grupo-formulario">

                    <label for="campoEmailCadastro">

                        <i
                            class="bi bi-envelope me-1"
                        ></i>

                        Email

                    </label>


                    <input
                        type="email"
                        id="campoEmailCadastro"
                        name="email"
                        class="entrada-login"
                        placeholder="seu@email.com"
                        value="<?php

                            echo sanitizar(
                                $_POST['email'] ?? ''
                            );

                        ?>"
                        maxlength="150"
                        required
                    >

                </div>


                <!-- SENHA -->

                <div class="grupo-formulario">

                    <label for="campoSenhaCadastro">

                        <i
                            class="bi bi-key me-1"
                        ></i>

                        Senha

                    </label>


                    <input
                        type="password"
                        id="campoSenhaCadastro"
                        name="senha"
                        class="entrada-login"
                        placeholder="Mínimo de 6 caracteres"
                        minlength="6"
                        required
                    >

                </div>


                <!-- CONFIRMAR SENHA -->

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
                        class="entrada-login"
                        placeholder="Digite a senha novamente"
                        minlength="6"
                        required
                    >

                </div>


                <!-- BOTÃO CADASTRAR -->

                <button
                    type="submit"
                    class="botao-entrar"
                >

                    <i
                        class="bi bi-person-plus me-2"
                    ></i>

                    Criar conta

                </button>


            </form>


            <!-- =================================================
                 VOLTAR PARA LOGIN
            ================================================== -->

            <div class="divisor">

                já possui uma conta?

            </div>


            <div class="area-cadastro">

                <a
                    href="login.php"
                    class="botao-secundario"
                >

                    <i
                        class="bi bi-box-arrow-in-right me-2"
                    ></i>

                    Voltar para o login

                </a>

            </div>


            <!-- =================================================
                 DICA
            ================================================== -->

            <div class="dica-login">

                <i
                    class="bi bi-shield-check me-2"
                ></i>

                <strong>Cadastro seguro</strong>

                <br>

                Sua senha é protegida com
                criptografia antes de ser salva.

            </div>


        <?php endif; ?>


        <!-- ====================================================
             VOLTAR PARA O SITE
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