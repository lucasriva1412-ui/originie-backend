<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL - PÁGINA INICIAL
 * ============================================================
 */

session_start();


// ============================================================
// VERIFICAR LOGIN
// ============================================================

if (
    !isset($_SESSION['usuario']) ||
    empty($_SESSION['usuario']['id'])
) {

    header('Location: login.php');

    exit;

}


$titulo_pagina = "Início";

$pagina_atual = "inicio";


require_once 'conexao.php';

require_once 'functions.php';


include 'header.php';


// ============================================================
// BUSCAR SERVIÇOS
// ============================================================

$servicos =
    buscarServicos($conexao) ?? [];


// ============================================================
// PEGAR OS 3 PRIMEIROS DESTAQUES
// ============================================================

$destaques =
    array_slice(
        $servicos,
        0,
        3
    );

?>


<!-- ============================================================
     HERÓI COM VÍDEO DE FUNDO
============================================================ -->

<section class="heroi-principal position-relative overflow-hidden">

    <video
        class="video-heroi"
        autoplay
        muted
        loop
        playsinline
    >

        <source
            src="Videos/HIGHLIGHTS.mp4"
            type="video/mp4"
        >

    </video>


    <div class="sobreposicao-heroi"></div>


    <div class="barra-gravacao d-flex align-items-center gap-2">

        <span class="ponto-gravacao"></span>

        <span class="texto-vermelho">
            REC
        </span>

    </div>


    <div class="container conteudo-heroi">

        <div class="row">

            <div class="col-lg-8">


                <p class="sobrancelha-heroi revelacao">

                    Produtora Audiovisual — Campo Mourão, PR

                </p>


                <h1 class="titulo-heroi revelacao d1">

                    Vídeos que<br>

                    <span class="texto-vermelho">
                        geram impacto.
                    </span>

                </h1>


                <p class="subtitulo-heroi revelacao d2">

                    Transformamos histórias, marcas e experiências
                    em vídeos com qualidade cinematográfica.

                </p>


                <div
                    class="d-flex flex-wrap gap-3 revelacao d3"
                >

                    <a
                        href="servicos.php"
                        class="botao-vermelho"
                    >

                        Ver Serviços

                        <i
                            class="bi bi-play-circle ms-1"
                        ></i>

                    </a>


                    <a
                        href="contato.php"
                        class="botao-contorno btn btn-lg"
                    >

                        Solicitar Orçamento

                    </a>

                </div>

            </div>

        </div>


        <!-- ====================================================
             ESTATÍSTICAS
        ===================================================== -->

        <div class="estatisticas-heroi revelacao d4">

            <div class="row g-0">


                <div class="col-4 estatistica-item">

                    <span class="numero-estatistica">

                        8

                        <span class="texto-vermelho">
                            +
                        </span>

                    </span>


                    <span class="etiqueta-estatistica">

                        Serviços

                    </span>

                </div>


                <div class="col-4 estatistica-item">

                    <span class="numero-estatistica">

                        Same

                        <span class="texto-vermelho">
                            Day
                        </span>

                    </span>


                    <span class="etiqueta-estatistica">

                        Entrega no mesmo dia

                    </span>

                </div>


                <div class="col-4 estatistica-item">

                    <span class="numero-estatistica">

                        100

                        <span class="texto-vermelho">
                            %
                        </span>

                    </span>


                    <span class="etiqueta-estatistica">

                        Qualidade Cinematográfica

                    </span>

                </div>


            </div>

        </div>

    </div>

</section>



<!-- ============================================================
     SERVIÇOS DESTAQUE
============================================================ -->

<section class="secao-clara py-5">

    <div class="container">


        <div class="row mb-5 revelacao">


            <div class="col-lg-6">


                <p class="etiqueta">

                    <i
                        class="bi bi-camera-video-fill me-2 texto-vermelho"
                    ></i>

                    O Que Fazemos

                </p>


                <h2 class="titulo-secao">

                    Soluções para cada

                    <span class="texto-vermelho">

                        momento.

                    </span>

                </h2>


            </div>


            <div
                class="col-lg-6 d-flex align-items-end justify-content-lg-end"
            >

                <a
                    href="servicos.php"
                    class="botao-contorno btn"
                >

                    Ver todos os serviços

                    <i
                        class="bi bi-arrow-right ms-1"
                    ></i>

                </a>

            </div>


        </div>


        <!-- ====================================================
             CARDS
        ===================================================== -->

        <div class="row g-4">


            <?php foreach ($destaques as $i => $s): ?>


                <div
                    class="col-md-4 revelacao delay-<?php echo (int)$i; ?>"
                >


                    <div class="cartao-servico h-100">


                        <?php if (!empty($s['video'])): ?>


                            <div class="ratio ratio-16x9">

                                <video
                                    class="video-cartao"
                                    muted
                                    loop
                                    playsinline
                                >

                                    <source
                                        src="<?php
                                            echo sanitizar(
                                                $s['video']
                                            );
                                        ?>"
                                        type="video/mp4"
                                    >

                                </video>

                            </div>


                        <?php else: ?>


                            <div
                                class="marcador-video d-flex align-items-center justify-content-center"
                            >

                                <i
                                    class="bi bi-camera-video texto-vermelho icone-video-vazio"
                                ></i>

                            </div>


                        <?php endif; ?>


                        <div class="corpo-cartao">


                            <span
                                class="badge fundo-vermelho mb-2"
                            >

                                <?php

                                echo sanitizar(
                                    $s['categoria']
                                    ?? 'Sem categoria'
                                );

                                ?>

                            </span>


                            <h5 class="titulo-cartao">

                                <?php

                                echo sanitizar(
                                    $s['nome']
                                );

                                ?>

                            </h5>


                            <p class="descricao-cartao">

                                <?php

                                echo sanitizar(
                                    mb_substr(
                                        $s['descricao'] ?? '',
                                        0,
                                        120
                                    )
                                );

                                ?>...

                            </p>


                        </div>


                        <div
                            class="p-3 border-top border-dark"
                        >

                            <small
                                class="texto-vermelho fw-semibold"
                            >

                                <?php

                                echo formatarPreco(
                                    $s['preco'] ?? 0
                                );

                                ?>

                            </small>

                        </div>


                    </div>

                </div>


            <?php endforeach; ?>


        </div>

    </div>

</section>



<!-- ============================================================
     SEÇÃO COM VÍDEO E CONTEÚDO
============================================================ -->

<section class="secao-escura py-0">

    <div class="container-fluid px-0">

        <div class="row g-0">


            <div class="col-lg-6">

                <div class="ratio ratio-16x9">

                    <video
                        class="video-preenchimento"
                        autoplay
                        muted
                        loop
                        playsinline
                    >

                        <source
                            src="Videos/VT_COMERCIAL.mp4"
                            type="video/mp4"
                        >

                    </video>

                </div>

            </div>


            <div
                class="col-lg-6 d-flex align-items-center secao-clara"
            >

                <div class="p-5 revelacao">


                    <p class="etiqueta">

                        <i
                            class="bi bi-film me-2 texto-vermelho"
                        ></i>

                        Portfólio

                    </p>


                    <h2 class="titulo-secao mb-4">

                        Cada projeto,<br>

                        <span class="texto-vermelho">

                            uma história única.

                        </span>

                    </h2>


                    <p class="texto-sobre mb-4">

                        Produzimos vídeos com qualidade
                        cinematográfica e estratégia de comunicação.

                    </p>


                    <a
                        href="contato.php"
                        class="botao-vermelho"
                    >

                        Quero um vídeo assim

                        <i
                            class="bi bi-arrow-right ms-1"
                        ></i>

                    </a>


                </div>

            </div>


        </div>

    </div>

</section>



<!-- ============================================================
     CTA FINAL
============================================================ -->

<section class="secao-cta">

    <div
        class="container text-center revelacao"
    >


        <h2 class="titulo-cta">

            Pronto para contar

            <span class="cta-italico">

                sua história?

            </span>

        </h2>


        <p class="subtitulo-cta">

            Entre em contato e vamos criar algo memorável juntos.

        </p>


        <a
            href="contato.php"
            class="btn btn-light btn-lg"
        >

            Falar com a Originie

            <i
                class="bi bi-arrow-right ms-1"
            ></i>

        </a>


    </div>

</section>



<?php

include 'footer.php';

?>