
<?php

session_start();

if (
    !isset($_SESSION['usuario_admin']) ||
    empty($_SESSION['usuario_admin']['id'])
) {
    header('Location: login.php');
    exit;
}

require_once 'conexao.php';
require_once 'functions.php';

$id_servico = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$mensagem_erro = '';
$mensagem_sucesso = '';

if ($id_servico <= 0) {
    header('Location: admin_dashboard.php');
    exit;
}

$servico = buscarServicoPorId($conexao, $id_servico);

if (empty($servico)) {
    header('Location: admin_dashboard.php');
    exit;
}

$categorias = buscarCategorias($conexao);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $resultado = atualizarServico(
        $conexao,
        $id_servico,
        $_POST
    );

    if ($resultado['sucesso']) {

        $mensagem_sucesso = $resultado['mensagem'];

        $servico = buscarServicoPorId(
            $conexao,
            $id_servico
        );

    } else {

        $mensagem_erro = $resultado['mensagem'];
    }
}

$usuario = $_SESSION['usuario_admin'];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Editar Serviço — Originie Audiovisual
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="assets/css/admin_servico_editar.css">

</head>

<body class="secao-clara">

<div class="barra-admin">

    <div class="container">

        <div class="barra-admin-conteudo">

            <h1 class="titulo-pagina">

                <i class="bi bi-pencil-square me-2"></i>

                Editar Serviço

            </h1>

            <div class="usuario-info">

                <span>
                    <?php
                    echo sanitizar(
                        $usuario['nome'] ?? 'Administrador'
                    );
                    ?>
                </span>

                <a
                    href="logout.php"
                    class="botao-sair">

                    <i class="bi bi-box-arrow-right"></i>

                    Sair

                </a>

            </div>

        </div>

    </div>

</div>


<div class="container pagina-servico-container">

    <?php if (!empty($mensagem_erro)): ?>

        <div class="alerta-erro">

            <i class="bi bi-exclamation-circle me-2"></i>

            <?php
            echo sanitizar($mensagem_erro);
            ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($mensagem_sucesso)): ?>

        <div class="alerta-sucesso">

            <i class="bi bi-check-circle me-2"></i>

            <?php
            echo sanitizar($mensagem_sucesso);
            ?>

        </div>

    <?php endif; ?>


    <div class="formulario-servico">

        <h2 class="titulo-formulario-servico">

            <?php
            echo sanitizar($servico['nome']);
            ?>

        </h2>


        <form method="POST">


            <!-- NOME -->

            <div class="grupo-formulario">

                <label for="campoNome">

                    <i class="bi bi-type me-1"></i>

                    Nome do Serviço

                </label>

                <input
                    type="text"
                    id="campoNome"
                    name="nome"
                    class="entrada-formulario"
                    value="<?php
                    echo sanitizar($servico['nome']);
                    ?>"
                    required>

            </div>


            <div class="linha-formulario">


                <!-- CATEGORIA -->

                <div class="grupo-formulario">

                    <label for="campoCategoria">

                        <i class="bi bi-tag me-1"></i>

                        Categoria

                    </label>

                    <select
                        id="campoCategoria"
                        name="categoria"
                        class="entrada-formulario"
                        required>

                        <option value="">
                            Selecione uma categoria
                        </option>

                        <?php foreach ($categorias as $cat): ?>

                            <option
                                value="<?php echo sanitizar($cat); ?>"
                                <?php
                                echo (
                                    $servico['categoria'] === $cat
                                )
                                    ? 'selected'
                                    : '';
                                ?>>

                                <?php
                                echo sanitizar(
                                    ucfirst($cat)
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- PREÇO -->

                <div class="grupo-formulario">

                    <label for="campoPreco">

                        <i class="bi bi-cash-coin me-1"></i>

                        Preço (R$)

                    </label>

                    <input
                        type="number"
                        id="campoPreco"
                        name="preco"
                        class="entrada-formulario"
                        step="0.01"
                        min="0"
                        value="<?php
                        echo sanitizar($servico['preco']);
                        ?>"
                        required>

                </div>

            </div>


            <!-- DESCRIÇÃO -->

            <div class="grupo-formulario">

                <label for="campoDescricao">

                    <i class="bi bi-chat-left-text me-1"></i>

                    Descrição

                </label>

                <textarea
                    id="campoDescricao"
                    name="descricao"
                    class="entrada-formulario"
                    required><?php
                    echo sanitizar($servico['descricao']);
                    ?></textarea>

            </div>


            <!-- VÍDEO -->

            <div class="grupo-formulario">

                <label for="campoVideo">

                    <i class="bi bi-play-circle me-1"></i>

                    Caminho do Vídeo

                </label>

                <input
                    type="text"
                    id="campoVideo"
                    name="video"
                    class="entrada-formulario"
                    value="<?php
                    echo sanitizar(
                        $servico['video'] ?? ''
                    );
                    ?>"
                    placeholder="Videos/meu_video.mp4">

                <div class="info-campo">

                    Exemplo:
                    Videos/meu_video.mp4

                </div>

            </div>


            <!-- STATUS -->

            <div class="grupo-formulario">

                <label>

                    <i class="bi bi-check-circle me-1"></i>

                    Status

                </label>

                <div class="checkbox-grupo">

                    <input
                        type="checkbox"
                        id="checkboxAtivo"
                        name="ativo"
                        value="1"
                        <?php
                        echo !empty($servico['ativo'])
                            ? 'checked'
                            : '';
                        ?>>

                    <label for="checkboxAtivo">

                        Serviço Ativo

                    </label>

                </div>

                <div class="info-campo">

                    Desmarque para deixar o serviço
                    oculto no site.

                </div>

            </div>


            <!-- BOTÕES -->

            <div class="acoes-formulario">

                <button
                    type="submit"
                    class="botao-salvar">

                    <i class="bi bi-save"></i>

                    Salvar Alterações

                </button>

                <a
                    href="admin_dashboard.php"
                    class="botao-cancelar">

                    <i class="bi bi-x-circle"></i>

                    Cancelar

                </a>

            </div>


        </form>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>