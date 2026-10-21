
<?php
/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL - CRIAR NOVO SERVIÇO
 * ============================================================
 * Apenas administradores logados podem acessar
 */

session_start();

/*
|--------------------------------------------------------------------------
| VERIFICAR LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usuario_admin']) ||
    empty($_SESSION['usuario_admin']['id'])
) {
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| CONEXÃO E FUNCTIONS
|--------------------------------------------------------------------------
*/

require_once 'conexao.php';
require_once 'functions.php';


/*
|--------------------------------------------------------------------------
| VARIÁVEIS
|--------------------------------------------------------------------------
*/

$mensagem_erro = '';
$mensagem_sucesso = '';


/*
|--------------------------------------------------------------------------
| BUSCAR CATEGORIAS
|--------------------------------------------------------------------------
*/

$categorias = buscarCategorias($conexao);


/*
|--------------------------------------------------------------------------
| PROCESSAR FORMULÁRIO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $resultado = criarServico(
        $conexao,
        $_POST
    );

    if ($resultado['sucesso']) {

        $mensagem_sucesso =
            $resultado['mensagem'];

        /*
        | Limpar formulário
        */

        $_POST = [];

    } else {

        $mensagem_erro =
            $resultado['mensagem'];

    }

}


/*
|--------------------------------------------------------------------------
| USUÁRIO LOGADO
|--------------------------------------------------------------------------
*/

$usuario =
    $_SESSION['usuario_admin'];

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
        Novo Serviço — Originie Audiovisual
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
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link rel="stylesheet" href="assets/css/admin_servico_novo.css">

</head>


<body class="secao-clara">


<!-- ========================================================= -->
<!-- BARRA ADMIN -->
<!-- ========================================================= -->

<div class="barra-admin">

    <div class="container">

        <div class="barra-admin-conteudo">


            <h1 class="titulo-pagina">

                <i class="bi bi-shield-check me-2"></i>

                Novo Serviço

            </h1>


            <div class="usuario-info">

                <span>

                    <?php

                    echo sanitizar(
                        $usuario['nome']
                    );

                    ?>

                </span>


                <a
                    href="logout.php"
                    class="botao-sair"
                >

                    <i class="bi bi-box-arrow-right"></i>

                    Sair

                </a>

            </div>


        </div>

    </div>

</div>


<!-- ========================================================= -->
<!-- CONTEÚDO -->
<!-- ========================================================= -->

<div
    class="container pagina-servico-container"
>


    <!-- ===================================================== -->
    <!-- ALERTA DE ERRO -->
    <!-- ===================================================== -->

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


    <!-- ===================================================== -->
    <!-- ALERTA DE SUCESSO -->
    <!-- ===================================================== -->

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

            <br>

            <small>

                <a
                    href="admin_servico_novo.php"
                    class="link-sucesso"
                >

                    Criar outro serviço

                </a>

                |

                <a
                    href="admin_dashboard.php"
                    class="link-sucesso"
                >

                    Voltar para dashboard

                </a>

            </small>

        </div>

    <?php endif; ?>


    <!-- ===================================================== -->
    <!-- FORMULÁRIO -->
    <!-- ===================================================== -->

    <div class="formulario-servico">


        <h2 class="titulo-formulario-servico">

            <i
                class="bi bi-plus-circle texto-vermelho me-2"
            ></i>

            Criar Novo Serviço

        </h2>


        <form method="POST">


            <!-- ================================================= -->
            <!-- NOME -->
            <!-- ================================================= -->

            <div class="grupo-formulario">

                <label for="campoNome">

                    <i
                        class="bi bi-type me-1"
                    ></i>

                    Nome do Serviço

                </label>


                <input
                    type="text"
                    id="campoNome"
                    name="nome"
                    class="entrada-formulario"

                    value="<?php

                    echo sanitizar(
                        $_POST['nome'] ?? ''
                    );

                    ?>"

                    placeholder="Ex: Vídeo Comercial"

                    required
                >


                <div class="info-campo">

                    Nome que será exibido no site

                </div>

            </div>


            <!-- ================================================= -->
            <!-- CATEGORIA E PREÇO -->
            <!-- ================================================= -->

            <div class="linha-formulario">


                <!-- CATEGORIA -->

                <div class="grupo-formulario">

                    <label for="campoCategoria">

                        <i
                            class="bi bi-tag me-1"
                        ></i>

                        Categoria

                    </label>


                    <select
                        id="campoCategoria"
                        name="categoria"
                        class="entrada-formulario"
                        required
                    >

                        <option value="">

                            Selecione uma categoria

                        </option>


                        <?php foreach ($categorias as $cat): ?>

                            <option
                                value="<?php echo sanitizar($cat); ?>"

                                <?php

                                echo (
                                    ($_POST['categoria'] ?? '') === $cat
                                )
                                ? 'selected'
                                : '';

                                ?>
                            >

                                <?php

                                echo ucfirst(
                                    sanitizar($cat)
                                );

                                ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>


                <!-- PREÇO -->

                <div class="grupo-formulario">

                    <label for="campoPreco">

                        <i
                            class="bi bi-cash-coin me-1"
                        ></i>

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

                        echo sanitizar(
                            $_POST['preco'] ?? ''
                        );

                        ?>"

                        placeholder="1200.00"

                        required
                    >


                    <div class="info-campo">

                        Use ponto como separador decimal

                    </div>

                </div>


            </div>


            <!-- ================================================= -->
            <!-- DESCRIÇÃO -->
            <!-- ================================================= -->

            <div class="grupo-formulario">

                <label for="campoDescricao">

                    <i
                        class="bi bi-chat-left-text me-1"
                    ></i>

                    Descrição

                </label>


                <textarea
                    id="campoDescricao"
                    name="descricao"
                    class="entrada-formulario"
                    placeholder="Descreva os detalhes deste serviço..."
                    maxlength="500"
                    required
                ><?php

                echo sanitizar(
                    $_POST['descricao'] ?? ''
                );

                ?></textarea>


                <div class="info-campo">

                    Máximo 500 caracteres

                </div>

            </div>


            <!-- ================================================= -->
            <!-- VÍDEO -->
            <!-- ================================================= -->

            <div class="grupo-formulario">

                <label for="campoVideo">

                    <i
                        class="bi bi-play-circle me-1"
                    ></i>

                    URL do Vídeo (Opcional)

                </label>


                <input
                    type="text"
                    id="campoVideo"
                    name="video"
                    class="entrada-formulario"

                    value="<?php

                    echo sanitizar(
                        $_POST['video'] ?? ''
                    );

                    ?>"

                    placeholder="Videos/SERVICO.mp4"
                >


                <div class="info-campo">

                    Caminho do vídeo na pasta Videos/

                </div>

            </div>


            <!-- ================================================= -->
            <!-- STATUS -->
            <!-- ================================================= -->

            <div class="grupo-formulario">

                <label>

                    <i
                        class="bi bi-toggle-on me-1"
                    ></i>

                    Status do Serviço

                </label>


                <div class="grupo-status">


                    <input
                        type="checkbox"
                        id="campoAtivo"
                        name="ativo"
                        value="1"

                        <?php

                        /*
                        | Novo serviço começa ativo.
                        | Se o formulário voltar após erro,
                        | mantém a escolha feita pelo usuário.
                        */

                        if (
                            !isset($_POST['ativo']) ||
                            $_POST['ativo'] == '1'
                        ) {

                            echo 'checked';

                        }

                        ?>
                    >


                    <label for="campoAtivo">

                        Serviço Ativo

                    </label>


                </div>


                <div class="info-campo">

                    Quando ativo, o serviço será exibido no site.
                    Desmarque para cadastrar o serviço como inativo.

                </div>

            </div>


            <!-- ================================================= -->
            <!-- AÇÕES -->
            <!-- ================================================= -->

            <div class="acoes-formulario">


                <button
                    type="submit"
                    class="botao-salvar"
                >

                    <i
                        class="bi bi-check-circle"
                    ></i>

                    Criar Serviço

                </button>


                <a
                    href="admin_dashboard.php"
                    class="botao-cancelar"
                >

                    <i
                        class="bi bi-x-circle"
                    ></i>

                    Cancelar

                </a>


            </div>


        </form>


    </div>


</div>


<!-- ========================================================= -->
<!-- BOOTSTRAP JS -->
<!-- ========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>