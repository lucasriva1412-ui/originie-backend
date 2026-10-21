<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['usuario']) ||
    !is_array($_SESSION['usuario']) ||
    empty($_SESSION['usuario']['id'])
) {
    header('Location: login.php');
    exit;
}

if (
    ($_SESSION['usuario']['tipo'] ?? '') !== 'admin'
) {
    header('Location: index.php');
    exit;
}

require_once 'conexao.php';
require_once 'functions.php';

$usuario = $_SESSION['usuario'];

$categoriaFiltro = $_GET['categoria'] ?? '';
$precoMin = $_GET['preco_min'] ?? '';
$precoMax = $_GET['preco_max'] ?? '';
$ordenacao = $_GET['ordenacao'] ?? 'nome';

$categorias = buscarCategorias($conexao);

$sql = "
    SELECT *
    FROM servicos_cat
    WHERE 1 = 1
";

$parametros = [];
$tipos = '';

if (
    !empty($categoriaFiltro) &&
    $categoriaFiltro !== 'todos'
) {
    $sql .= " AND categoria = ? ";
    $parametros[] = $categoriaFiltro;
    $tipos .= 's';
}

if (
    $precoMin !== '' &&
    is_numeric($precoMin)
) {
    $sql .= " AND preco >= ? ";
    $parametros[] = (float) $precoMin;
    $tipos .= 'd';
}

if (
    $precoMax !== '' &&
    is_numeric($precoMax)
) {
    $sql .= " AND preco <= ? ";
    $parametros[] = (float) $precoMax;
    $tipos .= 'd';
}

switch ($ordenacao) {

    case 'preco_menor':
        $sql .= " ORDER BY preco ASC ";
        break;

    case 'preco_maior':
        $sql .= " ORDER BY preco DESC ";
        break;

    case 'nome_desc':
        $sql .= " ORDER BY nome DESC ";
        break;

    case 'categoria':
        $sql .= " ORDER BY categoria ASC, nome ASC ";
        break;

    case 'ativo':
        $sql .= " ORDER BY ativo DESC, nome ASC ";
        break;

    case 'nome':
    default:
        $sql .= " ORDER BY nome ASC ";
        break;
}

$stmt = $conexao->prepare($sql);

$servicos = [];

if ($stmt) {

    if (!empty($parametros)) {
        $stmt->bind_param(
            $tipos,
            ...$parametros
        );
    }

    $stmt->execute();

    $resultado = $stmt->get_result();

    while ($linha = $resultado->fetch_assoc()) {
        $servicos[] = $linha;
    }

    $stmt->close();
}

$totalCategorias = count($categorias);
$totalFiltrados = count($servicos);

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
        Dashboard — Originie Audiovisual
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/admin_dashboard.css"
    >

</head>

<body>

<nav class="navbar-admin">

    <div class="container">

        <div class="navbar-admin-conteudo">

            <a
                href="index.php"
                class="logo-admin"
            >

                ORIGINIE

                <span>
                    ADMIN
                </span>

            </a>

            <div class="navbar-admin-acoes">

                <span class="usuario-admin">

                    <i class="bi bi-person-circle"></i>

                    <?php

                    echo htmlspecialchars(
                        $usuario['nome']
                        ?? 'Administrador',
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>

                </span>

                <a
                    href="index.php"
                    class="btn-admin-secundario"
                >

                    <i class="bi bi-house"></i>

                    Site

                </a>

                <a
                    href="logout.php"
                    class="btn-admin-sair"
                >

                    <i class="bi bi-box-arrow-right"></i>

                    Sair

                </a>

            </div>

        </div>

    </div>

</nav>


<main class="dashboard-admin">

    <div class="container">

        <div class="cabecalho-dashboard">

            <div>

                <span class="etiqueta-admin">
                    PAINEL ADMINISTRATIVO
                </span>

                <h1>
                    Dashboard
                </h1>

                <p>
                    Gerencie os serviços,
                    orçamentos e projetos da
                    Originie Audiovisual.
                </p>

            </div>

            <div class="acoes-dashboard">

                <a
                    href="admin_financeiro.php"
                    class="btn-orcamentos"
                >

                    <i class="bi bi-cash-stack"></i>

                    Financeiro

                </a>

                <a
                    href="admin_orcamentos.php"
                    class="btn-orcamentos"
                >

                    <i class="bi bi-clipboard-check"></i>

                    Orçamentos

                </a>

                <a
                    href="admin_servico_novo.php"
                    class="btn-novo-servico"
                >

                    <i class="bi bi-plus-lg"></i>

                    Novo Serviço

                </a>

            </div>

        </div>


        <div class="stats-admin">

            <div class="card-stat">

                <div class="icone-stat">

                    <i class="bi bi-camera-video"></i>

                </div>

                <div>

                    <div
                        class="card-stat-numero"
                        id="card-total-servicos"
                    >
                        Carregando...
                    </div>

                    <div class="card-stat-label">
                        Serviços ativos
                    </div>

                </div>

            </div>


            <div class="card-stat">

                <div class="icone-stat">

                    <i class="bi bi-tags"></i>

                </div>

                <div>

                    <div class="card-stat-numero">

                        <?php
                        echo $totalCategorias;
                        ?>

                    </div>

                    <div class="card-stat-label">
                        Categorias
                    </div>

                </div>

            </div>


            <div class="card-stat">

                <div class="icone-stat">

                    <i class="bi bi-collection"></i>

                </div>

                <div>

                    <div
                        class="card-stat-numero"
                        id="card-total-banco"
                    >
                        Carregando...
                    </div>

                    <div class="card-stat-label">
                        Total de serviços
                    </div>

                </div>

            </div>


            <div class="card-stat">

                <div class="icone-stat">

                    <i class="bi bi-check-circle"></i>

                </div>

                <div>

                    <div
                        class="card-stat-numero"
                        id="card-ativos-banco"
                    >
                        Carregando...
                    </div>

                    <div class="card-stat-label">
                        Ativos no banco
                    </div>

                </div>

            </div>

        </div>


        <section class="painel-analitico">

            <div class="cabecalho-analitico">

                <div>

                    <span class="etiqueta-admin">
                        ANÁLISE DE DADOS
                    </span>

                    <h2>
                        Indicadores da Dashboard
                    </h2>

                    <p>
                        Informações processadas pela API
                        e pelo TypeScript.
                    </p>

                </div>

            </div>


            <div class="row g-4">

                <div class="col-lg-4">

                    <div class="card-ranking">

                        <div class="titulo-ranking">

                            <i class="bi bi-trophy"></i>

                            <h3>
                                Serviços mais procurados
                            </h3>

                        </div>

                        <div id="ranking-servicos">

                            <p>
                                Carregando ranking...
                            </p>

                        </div>

                    </div>

                </div>


                <div class="col-lg-8">

                    <div class="card-dados-api">

                        <div class="titulo-ranking">

                            <i class="bi bi-bar-chart"></i>

                            <h3>
                                Dados processados
                            </h3>

                        </div>

                        <div class="table-responsive">

                            <table class="table tabela-admin">

                                <thead>

                                    <tr>

                                        <th>
                                            Serviço
                                        </th>

                                        <th>
                                            Categoria
                                        </th>

                                        <th>
                                            Preço
                                        </th>

                                        <th>
                                            Interesses
                                        </th>

                                        <th>
                                            Clientes
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                    </tr>

                                </thead>

                                <tbody id="tabela-servicos-body">

                                    <tr>

                                        <td colspan="6">
                                            Carregando dados...
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <?php if (!empty($_GET['mensagem'])): ?>

            <div
                class="alert alert-success alert-dismissible fade show"
            >

                <?php

                echo sanitizar(
                    $_GET['mensagem']
                );

                ?>

                <button
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <?php if (!empty($_GET['erro'])): ?>

            <div
                class="alert alert-danger alert-dismissible fade show"
            >

                <?php

                echo sanitizar(
                    $_GET['erro']
                );

                ?>

                <button
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <section class="painel-filtros">

            <div class="titulo-filtros">

                <i class="bi bi-funnel"></i>

                Filtros

            </div>

            <form
                method="GET"
                action="admin_dashboard.php"
            >

                <div class="row g-3">

                    <div class="col-lg-3 col-md-6">

                        <label
                            for="categoria"
                            class="form-label"
                        >
                            Categoria
                        </label>

                        <select
                            name="categoria"
                            id="categoria"
                            class="form-select"
                        >

                            <option value="todos">
                                Todas
                            </option>

                            <?php foreach ($categorias as $categoria): ?>

                                <?php

                                $valorCategoria =
                                    is_array($categoria)
                                        ? (
                                            $categoria['categoria']
                                            ?? ''
                                        )
                                        : $categoria;

                                ?>

                                <option
                                    value="<?php
                                    echo htmlspecialchars(
                                        $valorCategoria,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>"
                                    <?php

                                    echo (
                                        $categoriaFiltro
                                        ===
                                        $valorCategoria
                                    )
                                        ? 'selected'
                                        : '';

                                    ?>
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        ucfirst(
                                            $valorCategoria
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-lg-2 col-md-6">

                        <label
                            for="preco_min"
                            class="form-label"
                        >
                            Preço mínimo
                        </label>

                        <input
                            type="number"
                            name="preco_min"
                            id="preco_min"
                            class="form-control"
                            step="0.01"
                            min="0"
                            value="<?php
                            echo htmlspecialchars(
                                $precoMin,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>"
                        >

                    </div>


                    <div class="col-lg-2 col-md-6">

                        <label
                            for="preco_max"
                            class="form-label"
                        >
                            Preço máximo
                        </label>

                        <input
                            type="number"
                            name="preco_max"
                            id="preco_max"
                            class="form-control"
                            step="0.01"
                            min="0"
                            value="<?php
                            echo htmlspecialchars(
                                $precoMax,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>"
                        >

                    </div>


                    <div class="col-lg-3 col-md-6">

                        <label
                            for="ordenacao"
                            class="form-label"
                        >
                            Ordenar por
                        </label>

                        <select
                            name="ordenacao"
                            id="ordenacao"
                            class="form-select"
                        >

                            <option
                                value="nome"
                                <?php
                                echo $ordenacao === 'nome'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Nome A-Z
                            </option>

                            <option
                                value="nome_desc"
                                <?php
                                echo $ordenacao === 'nome_desc'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Nome Z-A
                            </option>

                            <option
                                value="preco_menor"
                                <?php
                                echo $ordenacao === 'preco_menor'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Menor preço
                            </option>

                            <option
                                value="preco_maior"
                                <?php
                                echo $ordenacao === 'preco_maior'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Maior preço
                            </option>

                            <option
                                value="categoria"
                                <?php
                                echo $ordenacao === 'categoria'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Categoria
                            </option>

                            <option
                                value="ativo"
                                <?php
                                echo $ordenacao === 'ativo'
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                Status
                            </option>

                        </select>

                    </div>


                    <div
                        class="col-lg-2 col-md-12 d-flex align-items-end"
                    >

                        <button
                            type="submit"
                            class="btn-filtrar"
                        >

                            <i class="bi bi-search"></i>

                            Filtrar

                        </button>

                    </div>

                </div>


                <div class="limpar-filtros">

                    <a href="admin_dashboard.php">

                        <i class="bi bi-x-circle"></i>

                        Limpar filtros

                    </a>

                </div>

            </form>

        </section>


        <section class="painel-tabela">

            <div class="cabecalho-tabela">

                <div>

                    <h2>
                        Serviços
                    </h2>

                    <span>

                        <?php
                        echo $totalFiltrados;
                        ?>

                        resultado(s)

                    </span>

                </div>

            </div>


            <div class="table-responsive">

                <table class="table tabela-admin">

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Serviço
                            </th>

                            <th>
                                Categoria
                            </th>

                            <th>
                                Preço
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Ações
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (empty($servicos)): ?>

                        <tr>

                            <td
                                colspan="6"
                                class="sem-resultados"
                            >
                                Nenhum serviço encontrado.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($servicos as $servico): ?>

                            <tr>

                                <td>

                                    <?php
                                    echo (int) $servico['id'];
                                    ?>

                                </td>

                                <td>

                                    <strong>

                                        <?php

                                        echo sanitizar(
                                            $servico['nome']
                                        );

                                        ?>

                                    </strong>

                                </td>

                                <td>

                                    <span class="badge-categoria">

                                        <?php

                                        echo sanitizar(
                                            ucfirst(
                                                $servico['categoria']
                                                ?? 'Sem categoria'
                                            )
                                        );

                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <?php

                                    echo formatarPreco(
                                        $servico['preco']
                                        ?? 0
                                    );

                                    ?>

                                </td>

                                <td>

                                    <?php if (
                                        isset(
                                            $servico['ativo']
                                        ) &&
                                        (int) $servico['ativo'] === 1
                                    ): ?>

                                        <span class="status-ativo">

                                            <i
                                                class="bi bi-check-circle-fill"
                                            ></i>

                                            Ativo

                                        </span>

                                    <?php else: ?>

                                        <span class="status-inativo">

                                            <i
                                                class="bi bi-x-circle-fill"
                                            ></i>

                                            Inativo

                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="acoes-tabela">

                                        <a
                                            href="admin_servico_editar.php?id=<?php
                                            echo (int) $servico['id'];
                                            ?>"
                                            class="btn-editar"
                                        >

                                            <i
                                                class="bi bi-pencil-square"
                                            ></i>

                                            Editar

                                        </a>


                                        <button
                                            type="button"
                                            class="btn-deletar"
                                            data-id="<?php
                                            echo (int) $servico['id'];
                                            ?>"
                                            data-nome="<?php
                                            echo sanitizar(
                                                $servico['nome']
                                            );
                                            ?>"
                                            onclick="confirmarExclusao(this)"
                                        >

                                            <i class="bi bi-trash"></i>

                                            Deletar

                                        </button>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</main>


<script src="assets/js/admin_dashboard.js"></script>

<script
    type="module"
    src="dist/dashboard.js"
></script>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>