<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL - HEADER
 * ============================================================
 */

// ============================================================
// CAMINHO BASE DO PROJETO
// ============================================================

$base_url = '/originie_atualizado/';


// ============================================================
// INICIAR SESSÃO
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ============================================================
// VERIFICAR USUÁRIO LOGADO
// ============================================================

$usuarioLogado = false;
$usuarioAtual = null;
$usuarioAdmin = false;

if (
    isset($_SESSION['usuario']) &&
    is_array($_SESSION['usuario']) &&
    !empty($_SESSION['usuario']['id'])
) {

    $usuarioLogado = true;

    $usuarioAtual = $_SESSION['usuario'];

    if (
        isset($usuarioAtual['tipo']) &&
        $usuarioAtual['tipo'] === 'admin'
    ) {
        $usuarioAdmin = true;
    }
}


// ============================================================
// NOME DO USUÁRIO
// ============================================================

$nomeUsuario = 'Usuário';

if ($usuarioLogado) {
    $nomeUsuario = $usuarioAtual['nome'] ?? 'Usuário';
}


// ============================================================
// PÁGINA ATUAL
// ============================================================

$pagina_atual = $pagina_atual ?? '';


// ============================================================
// TÍTULO DA PÁGINA
// ============================================================

$titulo_pagina = $titulo_pagina ?? 'Originie';

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
        echo htmlspecialchars(
            $titulo_pagina,
            ENT_QUOTES,
            'UTF-8'
        );
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
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- ========================================================
         CSS PRINCIPAL
    ========================================================= -->

    <link
        rel="stylesheet"
        href="<?php echo $base_url; ?>style.css"
    >

</head>


<body class="d-flex flex-column min-vh-100">


<!-- ============================================================
     BARRA DE NAVEGAÇÃO
============================================================ -->

<nav
    class="navbar navbar-expand-lg navbar-dark sticky-top"
    id="barraNavegacao"
>

    <div class="container">


        <!-- ====================================================
             LOGO
        ===================================================== -->

        <a
            class="navbar-brand"
            href="<?php echo $base_url; ?>index.php"
        >

            <img
                src="<?php echo $base_url; ?>logo.png"
                alt="Originie Audiovisual"
                height="36"
            >

        </a>


        <!-- ====================================================
             BOTÃO HAMBÚRGUER
        ===================================================== -->

        <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuNavegacao"
            aria-controls="menuNavegacao"
            aria-expanded="false"
            aria-label="Abrir menu"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- ====================================================
             MENU
        ===================================================== -->

        <div
            class="collapse navbar-collapse"
            id="menuNavegacao"
        >

            <ul
                class="navbar-nav ms-auto align-items-lg-center gap-lg-1"
            >


                <!-- =================================================
                     INÍCIO
                ================================================== -->

                <li class="nav-item">

                    <a
                        class="nav-link link-nav <?php
                            echo $pagina_atual === 'inicio'
                                ? 'ativo'
                                : '';
                        ?>"
                        href="<?php echo $base_url; ?>index.php"
                    >

                        Início

                    </a>

                </li>


                <!-- =================================================
                     SOBRE
                ================================================== -->

                <li class="nav-item">

                    <a
                        class="nav-link link-nav <?php
                            echo $pagina_atual === 'sobre'
                                ? 'ativo'
                                : '';
                        ?>"
                        href="<?php echo $base_url; ?>sobre.php"
                    >

                        Sobre

                    </a>

                </li>


                <!-- =================================================
                     SERVIÇOS
                ================================================== -->

                <li class="nav-item">

                    <a
                        class="nav-link link-nav <?php
                            echo $pagina_atual === 'servicos'
                                ? 'ativo'
                                : '';
                        ?>"
                        href="<?php echo $base_url; ?>servicos.php"
                    >

                        Serviços

                    </a>

                </li>


                <!-- =================================================
                     ÁREA ADMINISTRATIVA
                     SOMENTE ADMINISTRADOR
                ================================================== -->

                <?php if ($usuarioAdmin): ?>

                    <li class="nav-item">

                        <a
                            class="nav-link link-nav <?php
                                echo $pagina_atual === 'admin'
                                    ? 'ativo'
                                    : '';
                            ?>"
                            href="<?php echo $base_url; ?>admin_dashboard.php"
                        >

                            <i class="bi bi-shield-lock me-1"></i>

                            Área Admin

                        </a>

                    </li>

                <?php endif; ?>


                <!-- =================================================
                     USUÁRIO LOGADO
                ================================================== -->

                <?php if ($usuarioLogado): ?>

                    <li class="nav-item ms-lg-2">

                        <a
                            href="<?php echo $base_url; ?>conta.php"
                            class="nav-link link-nav <?php
                                echo $pagina_atual === 'conta'
                                    ? 'ativo'
                                    : '';
                            ?>"
                        >

                            <i class="bi bi-person-circle me-1"></i>

                            Minha Conta

                        </a>

                    </li>


                <?php else: ?>


                    <!-- =================================================
                         VISITANTE
                    ================================================== -->

                    <li class="nav-item ms-lg-2">

                        <a
                            href="<?php echo $base_url; ?>login.php"
                            class="nav-link link-nav <?php
                                echo $pagina_atual === 'login'
                                    ? 'ativo'
                                    : '';
                            ?>"
                        >

                            <i class="bi bi-person-circle me-1"></i>

                            Entrar

                        </a>

                    </li>

                <?php endif; ?>


                <!-- =================================================
                     ORÇAMENTO
                ================================================== -->

                <li class="nav-item ms-lg-2">

                    <a
                        class="botao-vermelho <?php
                            echo $pagina_atual === 'contato'
                                ? 'ativo'
                                : '';
                        ?>"
                        href="<?php echo $base_url; ?>contato.php"
                    >

                        Orçamento

                        <i class="bi bi-arrow-right ms-1"></i>

                    </a>

                </li>


            </ul>

        </div>

    </div>

</nav>