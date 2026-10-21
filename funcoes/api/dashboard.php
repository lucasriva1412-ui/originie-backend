<?php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/config.php';

try {

    $busca = isset($_GET['busca'])
        ? trim((string) $_GET['busca'])
        : '';

    $categoria = isset($_GET['categoria'])
        ? trim((string) $_GET['categoria'])
        : '';

    $ativo = null;

    if (isset($_GET['ativo']) && $_GET['ativo'] !== '') {
        $ativo = (int) $_GET['ativo'];

        if ($ativo !== 0 && $ativo !== 1) {
            $ativo = null;
        }
    }

    $pagina = isset($_GET['pagina'])
        ? (int) $_GET['pagina']
        : 1;

    if ($pagina < 1) {
        $pagina = 1;
    }

    $limite = isset($_GET['limite'])
        ? (int) $_GET['limite']
        : 10;

    if ($limite < 1) {
        $limite = 10;
    }

    if ($limite > 100) {
        $limite = 100;
    }

    $offset = ($pagina - 1) * $limite;


    $stmt = $pdo->prepare(
        "CALL sp_indicadores_dashboard(
            :busca,
            :categoria,
            :ativo,
            :limite,
            :offset
        )"
    );

    $stmt->bindValue(
        ':busca',
        $busca,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ':categoria',
        $categoria,
        PDO::PARAM_STR
    );

    if ($ativo === null) {
        $stmt->bindValue(
            ':ativo',
            null,
            PDO::PARAM_NULL
        );
    } else {
        $stmt->bindValue(
            ':ativo',
            $ativo,
            PDO::PARAM_INT
        );
    }

    $stmt->bindValue(
        ':limite',
        $limite,
        PDO::PARAM_INT
    );

    $stmt->bindValue(
        ':offset',
        $offset,
        PDO::PARAM_INT
    );


    $stmt->execute();


    /*
    |--------------------------------------------------------------------------
    | RESULTADO 1 - INDICADORES
    |--------------------------------------------------------------------------
    */

    $indicadores = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$indicadores) {

        $indicadores = [
            'total_servicos' => 0,
            'servicos_ativos' => 0,
            'media_preco' => 0,
            'orcamentos_pendentes' => 0
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RESULTADO 2 - SERVIÇOS
    |--------------------------------------------------------------------------
    */

    $servicos = [];

    if ($stmt->nextRowset()) {

        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | RESULTADO 3 - TOTAL FILTRADO
    |--------------------------------------------------------------------------
    */

    $totalFiltrado = 0;

    if ($stmt->nextRowset()) {

        $resultadoTotal = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultadoTotal) {

            $totalFiltrado =
                (int) ($resultadoTotal['total_filtrado'] ?? 0);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LIMPAR TODOS OS RESULTADOS DA PROCEDURE
    |--------------------------------------------------------------------------
    */

    while ($stmt->nextRowset()) {
    }

    $stmt->closeCursor();


    /*
    |--------------------------------------------------------------------------
    | FORMATAR INDICADORES
    |--------------------------------------------------------------------------
    */

    $indicadoresFormatados = [

        'total_servicos' =>
            (int) ($indicadores['total_servicos'] ?? 0),

        'servicos_ativos' =>
            (int) ($indicadores['servicos_ativos'] ?? 0),

        'media_preco' =>
            (float) ($indicadores['media_preco'] ?? 0),

        'orcamentos_pendentes' =>
            (int) ($indicadores['orcamentos_pendentes'] ?? 0)
    ];


    /*
    |--------------------------------------------------------------------------
    | FORMATAR SERVIÇOS
    |--------------------------------------------------------------------------
    */

    $servicosFormatados = [];

    foreach ($servicos as $servico) {

        $servicosFormatados[] = [

            'id' =>
                (int) ($servico['id'] ?? 0),

            'nome' =>
                (string) ($servico['nome'] ?? ''),

            'categoria' =>
                (string) ($servico['categoria'] ?? ''),

            'preco' =>
                (float) ($servico['preco'] ?? 0),

            'ativo' =>
                (bool) ($servico['ativo'] ?? false),

            'total_interesses' =>
                (int) ($servico['total_interesses'] ?? 0),

            'total_orcamentos' =>
                (int) ($servico['total_orcamentos'] ?? 0),

            'total_clientes' =>
                (int) ($servico['total_clientes'] ?? 0)
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR ORÇAMENTOS
    |--------------------------------------------------------------------------
    */

    $sqlOrcamentos = "
        SELECT
            c.id,
            c.servico,
            c.status,
            c.criado_em,
            cli.nome AS cliente

        FROM contatos c

        INNER JOIN clientes cli
            ON cli.id = c.id_cliente

        ORDER BY
            c.criado_em DESC
    ";

    $stmtOrcamentos = $pdo->prepare($sqlOrcamentos);

    $stmtOrcamentos->execute();

    $orcamentos = $stmtOrcamentos->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | FORMATAR ORÇAMENTOS
    |--------------------------------------------------------------------------
    */

    $orcamentosFormatados = [];

    foreach ($orcamentos as $orcamento) {

        $orcamentosFormatados[] = [

            'id' =>
                (int) ($orcamento['id'] ?? 0),

            'servico' =>
                (string) ($orcamento['servico'] ?? ''),

            'status' =>
                (string) ($orcamento['status'] ?? ''),

            'criado_em' =>
                (string) ($orcamento['criado_em'] ?? ''),

            'cliente' =>
                (string) ($orcamento['cliente'] ?? '')
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PAGINAÇÃO
    |--------------------------------------------------------------------------
    */

    $totalPaginas = 0;

    if ($totalFiltrado > 0) {

        $totalPaginas = (int) ceil(
            $totalFiltrado / $limite
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESPOSTA JSON
    |--------------------------------------------------------------------------
    */

    echo json_encode(
        [

            'sucesso' => true,

            'indicadores' =>
                $indicadoresFormatados,

            'servicos' =>
                $servicosFormatados,

            'orcamentos' =>
                $orcamentosFormatados,

            'paginacao' => [

                'pagina_atual' =>
                    $pagina,

                'limite' =>
                    $limite,

                'total_registros' =>
                    $totalFiltrado,

                'total_paginas' =>
                    $totalPaginas
            ],

            'filtros' => [

                'busca' =>
                    $busca,

                'categoria' =>
                    $categoria,

                'ativo' =>
                    $ativo
            ]
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_PRETTY_PRINT
    );

} catch (Throwable $erro) {

    http_response_code(500);

    echo json_encode(
        [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao carregar os dados da dashboard.'
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_PRETTY_PRINT
    );
}