<?php

$titulo_pagina = "Sobre";
$pagina_atual  = "sobre";

require_once 'functions.php';

include 'header.php';

// Array da equipe
$equipe = [
    [
        'nome'  => 'Danillo',
        'cargo' => 'Storytelling & Construção Narrativa',
        'foto'  => 'Danillo_jpg.jpeg',
        'bio'   => 'Atua desde o planejamento criativo até a edição final, desenvolvendo vídeos que criam conexão genuína com o público.',
        'icone'  => 'bi-pen',
    ],
    [
        'nome'  => 'Davi',
        'cargo' => 'Filmmaker & Pós-Produção (FX)',
        'foto'  => 'Davi_jpg.jpeg',
        'bio'   => 'Especializado em FX e pós-produção criativa, trazendo acabamento profissional e inovação visual.',
        'icone'  => 'bi-camera-video',
    ],
];

// Valores da empresa
$valores = [
    [
        'icone' => 'bi-eye',
        'titulo' => 'Visão Cinematográfica',
        'descricao' => 'Cada frame pensado com intenção estética e narrativa.'
    ],
    [
        'icone' => 'bi-lightning',
        'titulo' => 'Agilidade',
        'descricao' => 'Entregas rápidas sem abrir mão da qualidade.'
    ],
    [
        'icone' => 'bi-heart',
        'titulo' => 'Conexão',
        'descricao' => 'Vídeos que geram emoção e identificação.'
    ],
    [
        'icone' => 'bi-graph-up',
        'titulo' => 'Resultado',
        'descricao' => 'Conteúdo estratégico que gera impacto real.'
    ],
];

?>

<!-- HERÓI -->
<section class="heroi-pagina">
    <div class="container revelacao">
        <p class="etiqueta">
            <i class="bi bi-people-fill me-2 texto-vermelho"></i>
            Quem Somos
        </p>

        <h1 class="titulo-secao display-title">
            Mais do que vídeos —<br>
            <span class="texto-vermelho">histórias que ficam.</span>
        </h1>
    </div>
</section>

<!-- HISTÓRIA -->
<section class="secao-clara py-5">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6 revelacao">

                <p class="etiqueta mb-3">
                    <i class="bi bi-camera-reels me-2 texto-vermelho"></i>
                    Nossa História
                </p>

                <p class="texto-sobre">
                    Somos uma produtora audiovisual que transforma histórias e marcas em vídeos com conexão e impacto.
                </p>

                <p class="texto-sobre">
                    Criamos conteúdos estratégicos para empresas, eventos e redes sociais com qualidade cinematográfica.
                </p>

                <p class="texto-sobre">
                    Acreditamos que todo vídeo começa com uma pergunta:
                    <strong>qual história precisa ser contada?</strong>
                </p>

            </div>

            <!-- NÚMEROS -->
            <div class="col-lg-5 offset-lg-1 revelacao">

                <ul class="list-group list-group-flush">

                    <li class="list-group-item bg-transparent border-bottom borda-vermelha px-0 py-4">
                        <span class="numero-grande">8<span class="texto-vermelho">+</span></span>
                        <span class="etiqueta-numero">Serviços audiovisuais</span>
                    </li>

                    <li class="list-group-item bg-transparent border-bottom borda-vermelha px-0 py-4">
                        <span class="numero-grande">Same<span class="texto-vermelho"> Day</span></span>
                        <span class="etiqueta-numero">Edição e entrega no mesmo dia</span>
                    </li>

                    <li class="list-group-item bg-transparent border-bottom borda-vermelha px-0 py-4">
                        <span class="numero-grande">100<span class="texto-vermelho">%</span></span>
                        <span class="etiqueta-numero">Dedicação em cada projeto</span>
                    </li>

                    <li class="list-group-item bg-transparent px-0 py-4">
                        <span class="numero-grande">2<span class="texto-vermelho">+</span></span>
                        <span class="etiqueta-numero">Anos de experiência</span>
                    </li>

                </ul>

            </div>

        </div>
    </div>
</section>

<!-- VALORES -->
<section class="secao-escura py-5">
    <div class="container">

        <div class="row mb-4 revelacao">
            <div class="col">
                <p class="etiqueta">
                    <i class="bi bi-stars me-2 texto-vermelho"></i>
                    Nossos Valores
                </p>
            </div>
        </div>

        <div class="row g-3">

            <?php foreach ($valores as $i => $v): ?>

            <div class="col-sm-6 col-lg-3 revelacao delay-<?php echo (int)$i; ?>">

                <div class="cartao-valor h-100">

                    <i class="bi <?php echo sanitizar($v['icone']) ?> icone-valor"></i>

                    <h6 class="titulo-valor">
                        <?php echo sanitizar($v['titulo']) ?>
                    </h6>

                    <p class="descricao-valor">
                        <?php echo sanitizar($v['descricao']) ?>
                    </p>

                </div>

            </div>

            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- EQUIPE -->
<section class="secao-clara py-5">
    <div class="container">

        <div class="row mb-5 revelacao">
            <div class="col-lg-6">
                <p class="etiqueta">
                    <i class="bi bi-camera2 me-2 texto-vermelho"></i>
                    A Dupla por Trás das Câmeras
                </p>

                <h2 class="titulo-secao">
                    Conheça a <span class="texto-vermelho">equipe.</span>
                </h2>
            </div>
        </div>

        <div class="row g-4">

            <?php foreach ($equipe as $i => $m): ?>

            <div class="col-md-6 revelacao equipe-delay-<?php echo (int)$i; ?>">

                <div class="cartao-membro">

                    <div class="foto-membro-container">

                        <img src="<?php echo sanitizar($m['foto']) ?>"
                             alt="<?php echo sanitizar($m['nome']) ?>"
                             class="foto-membro">

                        <div class="sobreposicao-membro">
                            <i class="bi <?php echo sanitizar($m['icone']) ?> icone-sobreposicao"></i>
                        </div>

                    </div>

                    <div class="corpo-membro">

                        <p class="cargo-membro">
                            <?php echo sanitizar($m['cargo']) ?>
                        </p>

                        <h3 class="nome-membro">
                            <?php echo sanitizar($m['nome']) ?>
                        </h3>

                        <p class="bio-membro">
                            <?php echo sanitizar($m['bio']) ?>
                        </p>

                    </div>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    </div>
</section>

<!-- CTA -->
<section class="secao-cta">
    <div class="container text-center revelacao">

        <h2 class="titulo-cta">
            Quer trabalhar <span class="cta-italico">com a gente?</span>
        </h2>

        <p class="subtitulo-cta">
            Conta pra gente sobre o seu projeto.
        </p>

        <a href="contato.php" class="btn btn-light btn-lg">
            Entrar em contato
        </a>

    </div>
</section>

<?php include 'footer.php'; ?>
