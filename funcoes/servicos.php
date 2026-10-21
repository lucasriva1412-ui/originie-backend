<?php

$titulo_pagina = "Serviços";
$pagina_atual  = "servicos";

require_once 'conexao.php';
require_once 'functions.php';

include 'header.php';

// Categoria selecionada via GET
$categoria_ativa = $_GET['cat'] ?? 'todos';

// Busca serviços direto do banco
$servicos_exibidos = buscarServicosPorCategoria($conexao, $categoria_ativa);

// Busca categorias do banco
$categorias_bd = buscarCategorias($conexao);

// Busca por texto (LÓGICA DE FILTRO - Tech Forge)
$termo_busca = trim($_GET['busca'] ?? '');
if (!empty($termo_busca)) {
    $termo_busca_limpo = sanitizar($termo_busca);
    $servicos_exibidos = array_filter($servicos_exibidos, function($servico) use ($termo_busca) {
        $termo_lower = strtolower($termo_busca);
        return (
            stripos($servico['nome'], $termo_busca) !== false ||
            stripos($servico['descricao'], $termo_busca) !== false
        );
    });
}

// Busca por faixa de preço (LÓGICA DE FILTRO AVANÇADO - Tech Forge)
$preco_min = isset($_GET['preco_min']) && is_numeric($_GET['preco_min']) ? (float)$_GET['preco_min'] : 0;
$preco_max = isset($_GET['preco_max']) && is_numeric($_GET['preco_max']) ? (float)$_GET['preco_max'] : 10000;

if ($preco_min > 0 || $preco_max < 10000) {
    $servicos_exibidos = array_filter($servicos_exibidos, function($servico) use ($preco_min, $preco_max) {
        $preco = (float)$servico['preco'];
        return $preco >= $preco_min && $preco <= $preco_max;
    });
}

?>

<!-- HERÓI DA PÁGINA -->
<section class="heroi-pagina">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 revelacao">
                <p class="etiqueta">
                    <i class="bi bi-camera-video-fill me-2 texto-vermelho"></i>
                    O Que Fazemos
                </p>

                <h1 class="titulo-secao display-title">
                    Soluções para cada<br>
                    <span class="texto-vermelho">momento da sua marca.</span>
                </h1>

                <p class="texto-sobre">
                    Cada projeto é tratado do zero, com estratégia e qualidade profissional.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FILTROS -->
<section class="secao-clara pt-4 pb-2">
    <div class="container">

        <!-- FILTRO POR CATEGORIA -->
        <div class="mb-4">
            <h6 class="etiqueta mb-3">Filtrar por Categoria</h6>
            <div class="btn-group flex-wrap gap-2">

                <!-- TODOS -->
                <a href="servicos.php"
                   class="btn <?php echo $categoria_ativa === 'todos' || $categoria_ativa === '' ? 'botao-vermelho' : 'btn-outline-secondary' ?>">
                    Todos os Serviços
                </a>

                <!-- CATEGORIAS -->
                <?php foreach ($categorias_bd as $cat): ?>
                    <a href="servicos.php?cat=<?php echo urlencode($cat) ?>"
                       class="btn <?php echo $categoria_ativa === $cat ? 'botao-vermelho' : 'btn-outline-secondary' ?>">
                        <?php echo ucfirst(sanitizar($cat)) ?>
                    </a>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- FILTRO POR BUSCA E PREÇO -->
        <form method="GET" class="row g-3 mb-4" id="formularioBusca">
            
            <!-- BUSCA POR TEXTO -->
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-dark texto-vermelho">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="busca" 
                           class="entrada-form w-100" 
                           placeholder="Procurar serviço..."
                           value="<?php echo sanitizar($termo_busca) ?>">
                </div>
            </div>

            <!-- FILTRO DE PREÇO -->
            <div class="col-md-6">
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" 
                               name="preco_min" 
                               class="entrada-form w-100" 
                               placeholder="Preço mínimo"
                               value="<?php echo $preco_min > 0 ? $preco_min : '' ?>"
                               min="0"
                               step="100">
                    </div>
                    <div class="col-6">
                        <input type="number" 
                               name="preco_max" 
                               class="entrada-form w-100" 
                               placeholder="Preço máximo"
                               value="<?php echo $preco_max < 10000 ? $preco_max : '' ?>"
                               min="0"
                               step="100">
                    </div>
                </div>
            </div>

            <!-- INPUTS OCULTOS PARA MANTER FILTROS -->
            <input type="hidden" name="cat" value="<?php echo urlencode($categoria_ativa) ?>">

            <!-- BOTÃO DE FILTRO -->
            <div class="col-12">
                <button type="submit" class="botao-vermelho me-2">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a href="servicos.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Limpar Filtros
                </a>
            </div>

        </form>

    </div>
</section>

<!-- GRID DE SERVIÇOS -->
<section class="secao-clara pb-5">
    <div class="container">

        <?php if (empty($servicos_exibidos)): ?>

            <div class="alert alerta-erro text-center">
                <i class="bi bi-info-circle me-2"></i>
                Nenhum serviço encontrado com os filtros selecionados.
            </div>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($servicos_exibidos as $i => $s): ?>

                    <div class="col-md-6 col-lg-4 revelacao delay-<?php echo (int)($i % 3); ?>">

                        <div class="cartao-servico h-100 d-flex flex-column">

                            <!-- VÍDEO -->
                            <?php if (!empty($s['video'])): ?>
                                <div class="ratio ratio-16x9">
                                    <video class="video-cartao" 
                                           muted 
                                           loop 
                                           playsinline
                                           onmouseenter="this.play()" 
                                           onmouseleave="this.pause()">
                                        <source src="<?php echo sanitizar($s['video']) ?>" type="video/mp4">
                                    </video>
                                </div>
                            <?php else: ?>
                                <div class="marcador-video d-flex align-items-center justify-content-center">
                                    <i class="bi bi-camera-video texto-vermelho icone-video-vazio"></i>
                                </div>
                            <?php endif; ?>

                            <!-- CORPO -->
                            <div class="corpo-cartao flex-grow-1 d-flex flex-column">

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge fundo-vermelho">
                                        <?php echo sanitizar($s['categoria']) ?>
                                    </span>
                                    <small class="texto-vermelho fw-semibold">
                                        <?php echo formatarPreco($s['preco'] ?? 0) ?>
                                    </small>
                                </div>

                                <h5 class="titulo-cartao">
                                    <?php echo sanitizar($s['nome']) ?>
                                </h5>

                                <p class="descricao-cartao flex-grow-1">
                                    <?php echo sanitizar($s['descricao'] ?? '') ?>
                                </p>

                                <!-- BOTÃO -->
                                <a href="contato.php?servico=<?php echo $s['id'] ?>"
                                   class="botao-vermelho w-100 mt-3 text-decoration-none text-center">
                                    Solicitar Orçamento
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</section>

<!-- CTA -->
<section class="secao-cta">
    <div class="container text-center revelacao">
        <h2 class="titulo-cta">Não encontrou o que procura?</h2>
        <p class="subtitulo-cta">Fale com a gente e montamos um projeto sob medida.</p>
        <a href="contato.php" class="btn btn-light btn-lg">Entrar em contato</a>
    </div>
</section>

<?php include 'footer.php'; ?>
