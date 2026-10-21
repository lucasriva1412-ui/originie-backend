<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * CONTA.PHP
 * ============================================================
 * Área da conta do usuário
 */

session_start();

require_once 'conexao.php';
require_once 'functions.php';


// ============================================================
// VERIFICAR LOGIN
// ============================================================

if (!usuarioLogado()) {

    header('Location: login.php');
    exit;
}


// ============================================================
// USUÁRIO ATUAL
// ============================================================

$usuarioSessao = obterUsuarioAtual();


// ============================================================
// BUSCAR DADOS ATUALIZADOS DO BANCO
// ============================================================

$usuarioBanco =
    obterUsuarioAtualBanco($conexao);


// Se não encontrar o usuário no banco,
// encerra a sessão e volta para o login.

if (empty($usuarioBanco)) {

    encerrarSessao();

    header('Location: login.php');
    exit;
}


// ============================================================
// DADOS DO USUÁRIO
// ============================================================

$nomeUsuario =
    $usuarioBanco['nome'] ?? '';

$emailUsuario =
    $usuarioBanco['email'] ?? '';

$tipoUsuario =
    $usuarioBanco['tipo'] ?? 'usuario';

$dataCadastro =
    $usuarioBanco['criado_em'] ?? '';


// ============================================================
// BUSCAR ORÇAMENTOS DO USUÁRIO
// ============================================================

$orcamentos =
    buscarOrcamentosUsuario(
        $conexao,
        $emailUsuario
    );


// ============================================================
// CONTADORES
// ============================================================

$totalOrcamentos =
    count($orcamentos);

$totalPendentes = 0;

$totalConcluidos = 0;


foreach ($orcamentos as $orcamento) {

    $status =
        strtolower(
            trim(
                $orcamento['status'] ?? ''
            )
        );


    if (
        $status === 'concluído' ||
        $status === 'concluido'
    ) {

        $totalConcluidos++;

    } else {

        $totalPendentes++;

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
        Minha Conta — Originie Audiovisual
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
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- ========================================================
         ESTILO
    ========================================================= -->

    <link rel="stylesheet" href="assets/css/conta.css">

</head>


<body>


<!-- ============================================================
     NAVBAR
============================================================ -->

<nav class="navbar-conta">

    <div class="container">

        <div
            class="d-flex justify-content-between align-items-center"
        >

            <a
                href="index.php"
                class="logo"
            >

                ORIGINIE
                <span>AUDIOVISUAL</span>

            </a>


            <div class="menu-direita">

                <span class="nome-navbar">

                    Olá,
                    <?php
                    echo sanitizar($nomeUsuario);
                    ?>

                </span>


                <a
                    href="index.php"
                    class="botao-sair"
                >

                    <i class="bi bi-house me-1"></i>

                    Site

                </a>


                <a
                    href="logout.php"
                    class="botao-sair"
                >

                    <i class="bi bi-box-arrow-right me-1"></i>

                    Sair

                </a>

            </div>

        </div>

    </div>

</nav>


<!-- ============================================================
     CONTEÚDO
============================================================ -->

<main>

    <div class="container-conta">


        <!-- ====================================================
             CABEÇALHO
        ===================================================== -->

        <div class="cabecalho-conta">

            <h1>

                MINHA
                <span>CONTA</span>

            </h1>


            <p>

                Gerencie seus dados e acompanhe seus orçamentos.

            </p>

        </div>


        <!-- ====================================================
             RESUMO
        ===================================================== -->

        <div class="resumo-grid">


            <!-- TOTAL -->

            <div class="resumo-card">

                <div class="resumo-icone">

                    <i class="bi bi-file-earmark-text"></i>

                </div>


                <div class="resumo-info">

                    <span class="resumo-numero">

                        <?php
                        echo $totalOrcamentos;
                        ?>

                    </span>

                    <span class="resumo-label">

                        Orçamentos enviados

                    </span>

                </div>

            </div>


            <!-- PENDENTES -->

            <div class="resumo-card">

                <div class="resumo-icone">

                    <i class="bi bi-hourglass-split"></i>

                </div>


                <div class="resumo-info">

                    <span class="resumo-numero">

                        <?php
                        echo $totalPendentes;
                        ?>

                    </span>

                    <span class="resumo-label">

                        Em andamento

                    </span>

                </div>

            </div>


            <!-- CONCLUÍDOS -->

            <div class="resumo-card">

                <div class="resumo-icone">

                    <i class="bi bi-check-circle"></i>

                </div>


                <div class="resumo-info">

                    <span class="resumo-numero">

                        <?php
                        echo $totalConcluidos;
                        ?>

                    </span>

                    <span class="resumo-label">

                        Concluídos

                    </span>

                </div>

            </div>

        </div>


        <!-- ====================================================
             GRID
        ===================================================== -->

        <div class="grid-conta">


            <!-- =================================================
                 DADOS DA CONTA
            ================================================== -->

            <div class="card-conta">

                <div class="card-titulo">

                    <i class="bi bi-person-circle"></i>

                    <h2>

                        MEUS DADOS

                    </h2>

                </div>


                <div class="card-conteudo">


                    <div class="avatar">

                        <i class="bi bi-person"></i>

                    </div>


                    <div class="perfil-nome">

                        <?php
                        echo sanitizar($nomeUsuario);
                        ?>

                    </div>


                    <div class="perfil-tipo">

                        <?php

                        if ($tipoUsuario === 'admin') {

                            echo 'Administrador';

                        } else {

                            echo 'Usuário';

                        }

                        ?>

                    </div>


                    <!-- EMAIL -->

                    <div class="informacao">

                        <span class="informacao-label">

                            Email

                        </span>


                        <span class="informacao-valor">

                            <?php
                            echo sanitizar($emailUsuario);
                            ?>

                        </span>

                    </div>


                    <!-- DATA -->

                    <div class="informacao">

                        <span class="informacao-label">

                            Conta criada em

                        </span>


                        <span class="informacao-valor">

                            <?php

                            echo sanitizar(
                                formatarData($dataCadastro)
                            );

                            ?>

                        </span>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 ORÇAMENTOS
            ================================================== -->

            <div class="card-conta">

                <div class="card-titulo">

                    <i class="bi bi-file-earmark-text"></i>

                    <h2>

                        MEUS ORÇAMENTOS

                    </h2>

                </div>


                <?php if (empty($orcamentos)): ?>


                    <!-- ========================================
                         NENHUM ORÇAMENTO
                    ========================================= -->

                    <div class="sem-orcamentos">

                        <i class="bi bi-inbox"></i>


                        <h3>

                            Nenhum orçamento ainda

                        </h3>


                        <p>

                            Você ainda não enviou nenhum pedido
                            de orçamento para a Originie.

                        </p>


                        <a
                            href="contato.php"
                            class="botao-servicos"
                        >

                            <i
                                class="bi bi-send me-1"
                            ></i>

                            Solicitar orçamento

                        </a>

                    </div>


                <?php else: ?>


                    <!-- ========================================
                         LISTA DE ORÇAMENTOS
                    ========================================= -->

                    <?php foreach ($orcamentos as $orcamento): ?>


                        <?php

                        $status =
                            $orcamento['status']
                            ?? 'Pendente';

                        $classeStatus =
                            classeStatusOrcamento(
                                $status
                            );

                        $textoStatus =
                            textoStatusOrcamento(
                                $status
                            );

                        ?>


                        <div class="orcamento">


                            <div class="orcamento-topo">


                                <div>

                                    <div class="orcamento-titulo">

                                        Orçamento #
                                        <?php
                                        echo (int)$orcamento['id'];
                                        ?>

                                    </div>


                                    <div class="orcamento-data">

                                        <i
                                            class="bi bi-calendar3 me-1"
                                        ></i>

                                        <?php

                                        echo sanitizar(
                                            formatarData(
                                                $orcamento['criado_em']
                                                ?? ''
                                            )
                                        );

                                        ?>

                                    </div>

                                </div>


                                <!-- STATUS -->

                                <span
                                    class="status <?php echo sanitizar($classeStatus); ?>"
                                >

                                    <?php

                                    if (
                                        $classeStatus ===
                                        'status-concluido'
                                    ) {

                                        echo
                                            '<i class="bi bi-check-circle"></i>';

                                    } else {

                                        echo
                                            '<i class="bi bi-clock"></i>';

                                    }

                                    ?>


                                    <?php

                                    echo sanitizar(
                                        $textoStatus
                                    );

                                    ?>

                                </span>

                            </div>


                            <!-- SERVIÇO -->

                            <div class="orcamento-servico">

                                <strong>

                                    Serviço:

                                </strong>


                                <?php

                                echo sanitizar(
                                    $orcamento['servico']
                                    ?? 'Não informado'
                                );

                                ?>

                            </div>


                            <!-- MENSAGEM -->

                            <?php

                            if (
                                !empty(
                                    $orcamento['mensagem']
                                    ?? ''
                                )
                            ):

                            ?>

                                <p class="orcamento-mensagem mt-3">

                                    <?php

                                    echo nl2br(
                                        sanitizar(
                                            $orcamento['mensagem']
                                        )
                                    );

                                    ?>

                                </p>

                            <?php endif; ?>


                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>


            </div>


        </div>


    </div>

</main>


<!-- ============================================================
     BOOTSTRAP JS
============================================================ -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>