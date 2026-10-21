<?php

$titulo_pagina = "Contato";
$pagina_atual  = "contato";

require_once 'conexao.php';
require_once 'functions.php';

// Buscar serviços do banco
$todos_servicos = buscarServicos($conexao);

$mensagem_sucesso = '';
$mensagem_erro = '';
$servico_pre = $_GET['servico'] ?? '';

// Processar formulário POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $servicos_selecionados = $_POST['servicos'] ?? [];

    // Converter para array se for string
    if (!is_array($servicos_selecionados)) {
        $servicos_selecionados = !empty($servicos_selecionados) ? [$servicos_selecionados] : [];
    }

    // Salvar contato usando função com validações
    $resultado = salvarContato(
        $conexao,
        $nome,
        $email,
        $telefone,
        $servicos_selecionados,
        $mensagem
    );

    if ($resultado['sucesso']) {
        $mensagem_sucesso = $resultado['mensagem'];
        
        // Limpar formulário
        $_POST = [];
    } else {
        $mensagem_erro = $resultado['mensagem'];
    }
}

include 'header.php';

?>

<!-- HERÓI DA PÁGINA -->
<section class="heroi-pagina">
    <div class="container revelacao">
        <p class="etiqueta">
            <i class="bi bi-send-fill me-2 texto-vermelho"></i>
            Contato
        </p>

        <h1 class="titulo-secao display-title">
            Vamos contar<br>
            <span class="texto-vermelho">a sua história?</span>
        </h1>
    </div>
</section>

<!-- FORMULÁRIO -->
<section class="secao-clara py-5">
    <div class="container">
        <div class="row g-5">

            <!-- INFORMAÇÕES -->
            <div class="col-lg-4 revelacao">

                <p class="texto-sobre mb-4">
                    Conte seu projeto e retornamos em até 24h.
                </p>

                <div class="mt-5">
                    <h6 class="etiqueta mb-3">
                        <i class="bi bi-telephone me-2"></i> Contato Rápido
                    </h6>
                    <p class="texto-sobre">
                        <strong>Email:</strong><br>
                        contato@originie.com.br
                    </p>
                    <p class="texto-sobre">
                        <strong>Telefone/WhatsApp:</strong><br>
                        (44) 99999-9999
                    </p>
                </div>

            </div>

            <!-- FORMULÁRIO -->
            <div class="col-lg-8 revelacao d2">

                <?php if ($mensagem_sucesso): ?>
                    <div class="alerta-sucesso">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo sanitizar($mensagem_sucesso) ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensagem_erro): ?>
                    <div class="alerta-erro">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo sanitizar($mensagem_erro) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="formulario-contato">

                    <div class="row g-3">

                        <!-- NOME -->
                        <div class="col-sm-6">
                            <label for="campoNome" class="form-label">Nome Completo</label>
                            <input type="text" 
                                   id="campoNome"
                                   name="nome" 
                                   class="entrada-form w-100"
                                   placeholder="Seu nome completo" 
                                   value="<?php echo sanitizar($_POST['nome'] ?? '') ?>"
                                   required>
                        </div>

                        <!-- EMAIL -->
                        <div class="col-sm-6">
                            <label for="campoEmail" class="form-label">E-mail</label>
                            <input type="email" 
                                   id="campoEmail"
                                   name="email" 
                                   class="entrada-form w-100"
                                   placeholder="seu@email.com" 
                                   value="<?php echo sanitizar($_POST['email'] ?? '') ?>"
                                   required>
                        </div>

                        <!-- TELEFONE -->
                        <div class="col-sm-6">
                            <label for="campoTelefone" class="form-label">WhatsApp</label>
                            <input type="tel" 
                                   id="campoTelefone"
                                   name="telefone" 
                                   class="entrada-form w-100"
                                   placeholder="(44) 99999-9999"
                                   value="<?php echo sanitizar($_POST['telefone'] ?? '') ?>">
                        </div>

                        <!-- SERVIÇOS -->
                        <div class="col-sm-6">
                            <label for="campoServico" class="form-label">Serviços (Pode marcar múltiplos)</label>
                            <div id="checkboxServicos" class="lista-servicos-scroll">
                                <?php foreach ($todos_servicos as $s): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="servicos[]" 
                                               value="<?php echo $s['id'] ?>"
                                               id="servico_<?php echo $s['id'] ?>">
                                        <label class="form-check-label" for="servico_<?php echo $s['id'] ?>">
                                            <?php echo sanitizar($s['nome']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- MENSAGEM -->
                        <div class="col-12">
                            <label for="campoMensagem" class="form-label">Mensagem</label>
                            <textarea name="mensagem" 
                                      id="campoMensagem"
                                      rows="5"
                                      class="entrada-form w-100"
                                      placeholder="Descreva seu projeto..." 
                                      required><?php echo sanitizar($_POST['mensagem'] ?? '') ?></textarea>
                            <small class="texto-vermelho d-block mt-1">
                                Mínimo 10 caracteres
                            </small>
                        </div>

                        <!-- BOTÃO -->
                        <div class="col-12">
                            <button type="submit" class="botao-vermelho w-100 py-2">
                                <i class="bi bi-send me-2"></i>
                                Enviar Mensagem
                            </button>
                        </div>

                    </div>

                </form>

            </div>

        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
