<?php

/**
 * ============================================================
 * ORIGINIE AUDIOVISUAL
 * FUNCTIONS.PHP
 * ============================================================
 */

require_once "conexao.php";


/* ============================================================
   SERVIÇOS
   ============================================================ */


/**
 * Buscar todos os serviços
 */
function buscarServicos($conexao)
{
    if (!$conexao) {
        return [];
    }

    $sql = "SELECT * FROM servicos_cat ORDER BY nome";

    $resultado = $conexao->query($sql);

    $servicos = [];

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {
            $servicos[] = $linha;
        }
    }

    return $servicos;
}


/**
 * Buscar categorias
 */
function buscarCategorias($conexao)
{
    if (!$conexao) {
        return [];
    }

    $sql = "SELECT DISTINCT categoria
            FROM servicos_cat
            WHERE categoria IS NOT NULL
            AND categoria <> ''
            ORDER BY categoria";

    $resultado = $conexao->query($sql);

    $categorias = [];

    if ($resultado) {

        while ($linha = $resultado->fetch_assoc()) {

            $categorias[] = $linha['categoria'];
        }
    }

    return $categorias;
}


/**
 * Buscar serviços por categoria
 */
function buscarServicosPorCategoria($conexao, $categoria)
{
    if (!$conexao) {
        return [];
    }

    if (empty($categoria) || $categoria === "todos") {
        return buscarServicos($conexao);
    }

    $sql = "SELECT *
            FROM servicos_cat
            WHERE categoria = ?
            ORDER BY nome";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("s", $categoria);

    $stmt->execute();

    $resultado = $stmt->get_result();

    $servicos = [];

    while ($linha = $resultado->fetch_assoc()) {

        $servicos[] = $linha;
    }

    $stmt->close();

    return $servicos;
}


/**
 * Buscar serviços por preço
 */
function buscarServicosPorPreco(
    $conexao,
    $precoMin,
    $precoMax
) {

    if (
        !$conexao ||
        !is_numeric($precoMin) ||
        !is_numeric($precoMax)
    ) {
        return [];
    }

    if ($precoMin < 0 || $precoMax < 0) {
        return [];
    }

    if ($precoMin > $precoMax) {
        return [];
    }

    $sql = "SELECT *
            FROM servicos_cat
            WHERE preco BETWEEN ? AND ?
            ORDER BY preco";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param(
        "dd",
        $precoMin,
        $precoMax
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    $servicos = [];

    while ($linha = $resultado->fetch_assoc()) {

        $servicos[] = $linha;
    }

    $stmt->close();

    return $servicos;
}


/**
 * Buscar serviço pelo ID
 */
function buscarServicoPorId($conexao, $idServico)
{
    if (
        !$conexao ||
        !is_numeric($idServico)
    ) {
        return [];
    }

    $sql = "SELECT *
            FROM servicos_cat
            WHERE id = ?";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $idServico = (int)$idServico;

    $stmt->bind_param(
        "i",
        $idServico
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $servico = $resultado->fetch_assoc();

        $stmt->close();

        return $servico;
    }

    $stmt->close();

    return [];
}


/**
 * Criar serviço
 */
function criarServico($conexao, $dados)
{
    if (!$conexao) {

        return [
            'sucesso' => false,
            'mensagem' => 'Erro de conexão com o banco de dados.'
        ];
    }

    $nome = trim($dados['nome'] ?? '');
    $categoria = trim($dados['categoria'] ?? '');
    $preco = $dados['preco'] ?? '';
    $descricao = trim($dados['descricao'] ?? '');
    $video = trim($dados['video'] ?? '');

    if ($nome === '') {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe o nome do serviço.'
        ];
    }

    if ($categoria === '') {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe a categoria.'
        ];
    }

    if (
        $preco === '' ||
        !is_numeric($preco)
    ) {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe um preço válido.'
        ];
    }

    if ($descricao === '') {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe a descrição do serviço.'
        ];
    }

    $ativo = 1;

    $sql = "INSERT INTO servicos_cat
            (nome, categoria, preco, descricao, video, ativo)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao preparar cadastro: ' .
                $conexao->error
        ];
    }

    $preco = (float)$preco;

    $stmt->bind_param(
        "ssdssi",
        $nome,
        $categoria,
        $preco,
        $descricao,
        $video,
        $ativo
    );

    if (!$stmt->execute()) {

        $erro = $stmt->error;

        $stmt->close();

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao criar serviço: ' .
                $erro
        ];
    }

    $stmt->close();

    return [
        'sucesso' => true,
        'mensagem' => 'Serviço criado com sucesso!'
    ];
}


/**
 * Atualizar serviço
 */
function atualizarServico(
    $conexao,
    $idServico,
    $dados
) {

    if (
        !$conexao ||
        !is_numeric($idServico)
    ) {

        return [
            'sucesso' => false,
            'mensagem' => 'Serviço inválido.'
        ];
    }

    $nome = trim($dados['nome'] ?? '');
    $categoria = trim($dados['categoria'] ?? '');
    $preco = $dados['preco'] ?? '';
    $descricao = trim($dados['descricao'] ?? '');
    $video = trim($dados['video'] ?? '');

    $ativo = isset($dados['ativo']) ? 1 : 0;

    if ($nome === '') {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe o nome do serviço.'
        ];
    }

    if ($categoria === '') {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe a categoria.'
        ];
    }

    if (
        $preco === '' ||
        !is_numeric($preco)
    ) {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe um preço válido.'
        ];
    }

    if ($descricao === '') {

        return [
            'sucesso' => false,
            'mensagem' => 'Informe a descrição.'
        ];
    }

    $sql = "UPDATE servicos_cat
            SET nome = ?,
                categoria = ?,
                preco = ?,
                descricao = ?,
                video = ?,
                ativo = ?
            WHERE id = ?";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao preparar atualização: ' .
                $conexao->error
        ];
    }

    $idServico = (int)$idServico;
    $preco = (float)$preco;

    $stmt->bind_param(
        "ssdssii",
        $nome,
        $categoria,
        $preco,
        $descricao,
        $video,
        $ativo,
        $idServico
    );

    if (!$stmt->execute()) {

        $erro = $stmt->error;

        $stmt->close();

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao atualizar serviço: ' .
                $erro
        ];
    }

    $stmt->close();

    return [
        'sucesso' => true,
        'mensagem' => 'Serviço atualizado com sucesso!'
    ];
}


/**
 * Deletar serviço definitivamente
 */
function deletarServico(
    $conexao,
    $idServico
) {

    if (
        !$conexao ||
        !is_numeric($idServico)
    ) {

        return [
            'sucesso' => false,
            'mensagem' => 'Serviço inválido.'
        ];
    }

    $idServico = (int)$idServico;

    $sqlVerifica =
        "SELECT COUNT(*) AS total
         FROM servicos_interesse
         WHERE id_servico = ?";

    $stmtVerifica =
        $conexao->prepare($sqlVerifica);

    if ($stmtVerifica) {

        $stmtVerifica->bind_param(
            "i",
            $idServico
        );

        $stmtVerifica->execute();

        $resultado =
            $stmtVerifica->get_result();

        $linha =
            $resultado->fetch_assoc();

        $stmtVerifica->close();

        if (
            $linha &&
            (int)$linha['total'] > 0
        ) {

            $sqlInteresse =
                "DELETE FROM servicos_interesse
                 WHERE id_servico = ?";

            $stmtInteresse =
                $conexao->prepare($sqlInteresse);

            if ($stmtInteresse) {

                $stmtInteresse->bind_param(
                    "i",
                    $idServico
                );

                $stmtInteresse->execute();

                $stmtInteresse->close();
            }
        }
    }

    $sql =
        "DELETE FROM servicos_cat
         WHERE id = ?";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao preparar exclusão: ' .
                $conexao->error
        ];
    }

    $stmt->bind_param(
        "i",
        $idServico
    );

    if (!$stmt->execute()) {

        $erro = $stmt->error;

        $stmt->close();

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao excluir serviço: ' .
                $erro
        ];
    }

    $stmt->close();

    return [
        'sucesso' => true,
        'mensagem' =>
            'Serviço excluído definitivamente.'
    ];
}


/* ============================================================
   CLIENTES
   ============================================================ */


/**
 * Verificar cliente pelo email
 */
function verificarClienteExistente(
    $conexao,
    $email
) {

    if (!$conexao) {
        return false;
    }

    $sql =
        "SELECT id
         FROM clientes
         WHERE email = ?
         LIMIT 1";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $linha =
            $resultado->fetch_assoc();

        $stmt->close();

        return (int)$linha['id'];
    }

    $stmt->close();

    return false;
}


/**
 * Buscar cliente pelo ID
 */
function buscarClientePorId(
    $conexao,
    $idCliente
) {

    if (
        !$conexao ||
        !is_numeric($idCliente)
    ) {
        return [];
    }

    $sql =
        "SELECT *
         FROM clientes
         WHERE id = ?";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $idCliente = (int)$idCliente;

    $stmt->bind_param(
        "i",
        $idCliente
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $cliente =
            $resultado->fetch_assoc();

        $stmt->close();

        return $cliente;
    }

    $stmt->close();

    return [];
}


/**
 * Buscar cliente pelo email
 */
function buscarClientePorEmail(
    $conexao,
    $email
) {

    if (
        !$conexao ||
        empty($email)
    ) {
        return [];
    }

    $sql =
        "SELECT *
         FROM clientes
         WHERE email = ?
         LIMIT 1";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $cliente =
            $resultado->fetch_assoc();

        $stmt->close();

        return $cliente;
    }

    $stmt->close();

    return [];
}


/* ============================================================
   CONTATO / ORÇAMENTO
   ============================================================ */


/**
 * Salvar orçamento
 */
function salvarContato(
    $conexao,
    $nome,
    $email,
    $telefone,
    $servicosIds,
    $mensagem
) {
    if (!$conexao) {
        return [
            'sucesso' => false,
            'mensagem' => 'Erro de conexão com o banco de dados.',
            'id_contato' => 0
        ];
    }

    $validacao = validarDadosContato([
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'mensagem' => $mensagem
    ]);

    if (!$validacao['valido']) {
        return [
            'sucesso' => false,
            'mensagem' => implode(' | ', $validacao['erros']),
            'id_contato' => 0
        ];
    }

    if (!is_array($servicosIds)) {
        $servicosIds = !empty($servicosIds) ? [$servicosIds] : [];
    }

    $servicosValidos = [];
    $nomesServicos = [];

    foreach ($servicosIds as $idServico) {
        if (!is_numeric($idServico) || (int)$idServico <= 0) {
            continue;
        }

        $idServico = (int)$idServico;
        $servicoBanco = buscarServicoPorId($conexao, $idServico);

        if (!empty($servicoBanco) && isset($servicoBanco['id'], $servicoBanco['nome'])) {
            $servicosValidos[$idServico] = $idServico;
            $nomesServicos[$idServico] = $servicoBanco['nome'];
        }
    }

    $servicosValidos = array_values($servicosValidos);
    $nomesServicos = array_values($nomesServicos);

    if (count($servicosValidos) === 0) {
        return [
            'sucesso' => false,
            'mensagem' => 'Selecione pelo menos um serviço válido.',
            'id_contato' => 0
        ];
    }

    $servico = implode(', ', $nomesServicos);

    $conexao->begin_transaction();

    try {
        $idCliente = verificarClienteExistente($conexao, $email);

        if ($idCliente !== false) {
            $sqlCliente = "UPDATE clientes SET nome = ?, telefone = ? WHERE id = ?";
            $stmtCliente = $conexao->prepare($sqlCliente);

            if (!$stmtCliente) {
                throw new Exception('Erro ao preparar atualização do cliente: ' . $conexao->error);
            }

            $stmtCliente->bind_param('ssi', $nome, $telefone, $idCliente);

            if (!$stmtCliente->execute()) {
                $erro = $stmtCliente->error;
                $stmtCliente->close();
                throw new Exception('Erro ao atualizar cliente: ' . $erro);
            }

            $stmtCliente->close();
        } else {
            $sqlCliente = "INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)";
            $stmtCliente = $conexao->prepare($sqlCliente);

            if (!$stmtCliente) {
                throw new Exception('Erro ao preparar cadastro do cliente: ' . $conexao->error);
            }

            $stmtCliente->bind_param('sss', $nome, $email, $telefone);

            if (!$stmtCliente->execute()) {
                $erro = $stmtCliente->error;
                $stmtCliente->close();
                throw new Exception('Erro ao salvar cliente: ' . $erro);
            }

            $idCliente = $conexao->insert_id;
            $stmtCliente->close();
        }

        $status = 'Pendente';
        $sqlContato = "INSERT INTO contatos (id_cliente, servico, mensagem, status) VALUES (?, ?, ?, ?)";
        $stmtContato = $conexao->prepare($sqlContato);

        if (!$stmtContato) {
            throw new Exception('Erro ao preparar orçamento: ' . $conexao->error);
        }

        $stmtContato->bind_param('isss', $idCliente, $servico, $mensagem, $status);

        if (!$stmtContato->execute()) {
            $erro = $stmtContato->error;
            $stmtContato->close();
            throw new Exception('Erro ao salvar orçamento: ' . $erro);
        }

        $idContato = $conexao->insert_id;
        $stmtContato->close();

        $sqlInteresse = "INSERT INTO servicos_interesse (id_contato, id_servico) VALUES (?, ?)";
        $stmtInteresse = $conexao->prepare($sqlInteresse);

        if (!$stmtInteresse) {
            throw new Exception('Erro ao preparar relacionamento dos serviços: ' . $conexao->error);
        }

        foreach ($servicosValidos as $idServico) {
            $stmtInteresse->bind_param('ii', $idContato, $idServico);

            if (!$stmtInteresse->execute()) {
                $erro = $stmtInteresse->error;
                $stmtInteresse->close();
                throw new Exception('Erro ao relacionar serviço ao orçamento: ' . $erro);
            }
        }

        $stmtInteresse->close();
        $conexao->commit();

        return [
            'sucesso' => true,
            'mensagem' => 'Orçamento enviado com sucesso! Entraremos em contato em breve.',
            'id_contato' => $idContato
        ];
    } catch (Throwable $erro) {
        $conexao->rollback();

        return [
            'sucesso' => false,
            'mensagem' => 'Não foi possível enviar o orçamento. ' . $erro->getMessage(),
            'id_contato' => 0
        ];
    }
}


/**
 * Buscar todos os orçamentos
 */
function buscarContatos($conexao)
{
    if (!$conexao) {
        return [];
    }

    $sql =
        "SELECT
            c.id,
            c.id_cliente,
            c.servico,
            c.mensagem,
            c.criado_em,
            c.status,
            cl.nome,
            cl.email,
            cl.telefone
         FROM contatos c
         INNER JOIN clientes cl
             ON cl.id = c.id_cliente
         ORDER BY c.criado_em DESC";

    $resultado =
        $conexao->query($sql);

    $contatos = [];

    if ($resultado) {

        while (
            $linha =
            $resultado->fetch_assoc()
        ) {

            $contatos[] = $linha;
        }
    }

    return $contatos;
}


/**
 * Buscar orçamento por ID
 */
function buscarContatoPorId(
    $conexao,
    $idContato
) {

    if (
        !$conexao ||
        !is_numeric($idContato)
    ) {
        return [];
    }

    $sql =
        "SELECT
            c.id,
            c.id_cliente,
            c.servico,
            c.mensagem,
            c.criado_em,
            c.status,
            cl.nome,
            cl.email,
            cl.telefone
         FROM contatos c
         INNER JOIN clientes cl
             ON cl.id = c.id_cliente
         WHERE c.id = ?";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $idContato =
        (int)$idContato;

    $stmt->bind_param(
        "i",
        $idContato
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $contato =
            $resultado->fetch_assoc();

        $stmt->close();

        return $contato;
    }

    $stmt->close();

    return [];
}


/**
 * Buscar orçamentos do usuário pelo email
 */
function buscarOrcamentosUsuario(
    $conexao,
    $email
) {

    if (
        !$conexao ||
        empty($email)
    ) {
        return [];
    }

    $sql =
        "SELECT
            c.id,
            c.id_cliente,
            c.servico,
            c.mensagem,
            c.criado_em,
            c.status,
            cl.nome,
            cl.email,
            cl.telefone
         FROM contatos c
         INNER JOIN clientes cl
             ON cl.id = c.id_cliente
         WHERE cl.email = ?
         ORDER BY c.criado_em DESC";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    $orcamentos = [];

    while (
        $linha =
        $resultado->fetch_assoc()
    ) {

        $orcamentos[] = $linha;
    }

    $stmt->close();

    return $orcamentos;
}


/**
 * Buscar orçamento de um usuário pelo ID
 */
function buscarOrcamentoUsuarioPorId(
    $conexao,
    $idContato,
    $email
) {

    if (
        !$conexao ||
        !is_numeric($idContato) ||
        empty($email)
    ) {
        return [];
    }

    $sql =
        "SELECT
            c.id,
            c.id_cliente,
            c.servico,
            c.mensagem,
            c.criado_em,
            c.status,
            cl.nome,
            cl.email,
            cl.telefone
         FROM contatos c
         INNER JOIN clientes cl
             ON cl.id = c.id_cliente
         WHERE c.id = ?
         AND cl.email = ?
         LIMIT 1";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $idContato =
        (int)$idContato;

    $stmt->bind_param(
        "is",
        $idContato,
        $email
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $orcamento =
            $resultado->fetch_assoc();

        $stmt->close();

        return $orcamento;
    }

    $stmt->close();

    return [];
}


/**
 * Marcar orçamento como concluído
 */
function concluirOrcamento(
    $conexao,
    $idContato
) {

    if (
        !$conexao ||
        !is_numeric($idContato)
    ) {

        return [
            'sucesso' => false,
            'mensagem' => 'Orçamento inválido.'
        ];
    }

    $sql =
        "UPDATE contatos
         SET status = 'Concluído'
         WHERE id = ?";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao preparar atualização: ' .
                $conexao->error
        ];
    }

    $idContato =
        (int)$idContato;

    $stmt->bind_param(
        "i",
        $idContato
    );

    if (!$stmt->execute()) {

        $erro =
            $stmt->error;

        $stmt->close();

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao concluir orçamento: ' .
                $erro
        ];
    }

    $stmt->close();

    return [
        'sucesso' => true,
        'mensagem' =>
            'Orçamento marcado como concluído.'
    ];
}


/**
 * Excluir orçamento
 */
function deletarContato(
    $conexao,
    $idContato
) {

    if (
        !$conexao ||
        !is_numeric($idContato)
    ) {
        return [
            'sucesso' => false,
            'mensagem' => 'Orçamento inválido.'
        ];
    }

    $idContato = (int)$idContato;

    try {
        $conexao->begin_transaction();

        $sqlInteresses =
            "DELETE FROM servicos_interesse
             WHERE id_contato = ?";

        $stmtInteresses =
            $conexao->prepare($sqlInteresses);

        if (!$stmtInteresses) {
            throw new Exception(
                'Erro ao preparar exclusão dos serviços relacionados: ' .
                $conexao->error
            );
        }

        $stmtInteresses->bind_param(
            "i",
            $idContato
        );

        if (!$stmtInteresses->execute()) {
            $erro = $stmtInteresses->error;
            $stmtInteresses->close();

            throw new Exception(
                'Erro ao excluir serviços relacionados: ' .
                $erro
            );
        }

        $stmtInteresses->close();

        $sqlContato =
            "DELETE FROM contatos
             WHERE id = ?";

        $stmtContato =
            $conexao->prepare($sqlContato);

        if (!$stmtContato) {
            throw new Exception(
                'Erro ao preparar exclusão do orçamento: ' .
                $conexao->error
            );
        }

        $stmtContato->bind_param(
            "i",
            $idContato
        );

        if (!$stmtContato->execute()) {
            $erro = $stmtContato->error;
            $stmtContato->close();

            throw new Exception(
                'Erro ao excluir orçamento: ' .
                $erro
            );
        }

        if ($stmtContato->affected_rows === 0) {
            $stmtContato->close();

            throw new Exception(
                'Orçamento não encontrado.'
            );
        }

        $stmtContato->close();

        $conexao->commit();

        return [
            'sucesso' => true,
            'mensagem' => 'Orçamento excluído com sucesso.'
        ];

    } catch (Throwable $erro) {

        try {
            $conexao->rollback();
        } catch (Throwable $erroRollback) {
        }

        return [
            'sucesso' => false,
            'mensagem' => $erro->getMessage()
        ];
    }
}


/* ============================================================
   USUÁRIOS
   ============================================================ */


/**
 * Buscar usuário pelo ID
 */
function buscarUsuarioPorId(
    $conexao,
    $idUsuario
) {

    if (
        !$conexao ||
        !is_numeric($idUsuario)
    ) {
        return [];
    }

    $sql =
        "SELECT
            id,
            nome,
            email,
            tipo,
            criado_em
         FROM usuarios
         WHERE id = ?
         LIMIT 1";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $idUsuario =
        (int)$idUsuario;

    $stmt->bind_param(
        "i",
        $idUsuario
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $usuario =
            $resultado->fetch_assoc();

        $stmt->close();

        return $usuario;
    }

    $stmt->close();

    return [];
}


/**
 * Buscar usuário pelo email
 */
function buscarUsuarioPorEmail(
    $conexao,
    $email
) {

    if (
        !$conexao ||
        empty($email)
    ) {
        return [];
    }

    $sql =
        "SELECT
            id,
            nome,
            email,
            senha,
            tipo,
            criado_em
         FROM usuarios
         WHERE email = ?
         LIMIT 1";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();

    if (
        $resultado &&
        $resultado->num_rows > 0
    ) {

        $usuario =
            $resultado->fetch_assoc();

        $stmt->close();

        return $usuario;
    }

    $stmt->close();

    return [];
}


/**
 * Criar usuário
 *
 * O cadastro público sempre cria
 * usuário como tipo "usuario".
 */
function criarUsuario(
    $conexao,
    $nome,
    $email,
    $senha,
    $tipo = 'usuario'
) {

    if (!$conexao) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro de conexão com o banco de dados.'
        ];
    }

    $nome =
        trim($nome);

    $email =
        strtolower(
            trim($email)
        );

    $senha =
        trim($senha);


    /*
     * Por segurança, o cadastro público
     * nunca deve conseguir criar administrador.
     */
    $tipo = 'usuario';


    /* --------------------------------------------------------
       VALIDAR NOME
       -------------------------------------------------------- */

    if (!validarNome($nome)) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Nome inválido. Mínimo 3 caracteres.'
        ];
    }


    /* --------------------------------------------------------
       VALIDAR EMAIL
       -------------------------------------------------------- */

    if (!validarEmail($email)) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Email inválido.'
        ];
    }


    /* --------------------------------------------------------
       VALIDAR SENHA
       -------------------------------------------------------- */

    if (strlen($senha) < 6) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'A senha deve possuir pelo menos 6 caracteres.'
        ];
    }


    /* --------------------------------------------------------
       VERIFICAR EMAIL EXISTENTE
       -------------------------------------------------------- */

    $usuarioExistente =
        buscarUsuarioPorEmail(
            $conexao,
            $email
        );

    if (!empty($usuarioExistente)) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Este email já está cadastrado.'
        ];
    }


    /* --------------------------------------------------------
       CRIPTOGRAFAR SENHA
       -------------------------------------------------------- */

    $senhaHash =
        password_hash(
            $senha,
            PASSWORD_DEFAULT
        );


    /* --------------------------------------------------------
       INSERIR USUÁRIO
       -------------------------------------------------------- */

    $sql =
        "INSERT INTO usuarios
         (nome, email, senha, tipo)
         VALUES (?, ?, ?, ?)";

    $stmt =
        $conexao->prepare($sql);

    if (!$stmt) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao preparar cadastro: ' .
                $conexao->error
        ];
    }


    $stmt->bind_param(
        "ssss",
        $nome,
        $email,
        $senhaHash,
        $tipo
    );


    if (!$stmt->execute()) {

        $erro =
            $stmt->error;

        $stmt->close();

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao criar usuário: ' .
                $erro
        ];
    }


    $idUsuario =
        $conexao->insert_id;

    $stmt->close();


    return [
        'sucesso' => true,
        'mensagem' =>
            'Usuário criado com sucesso!',
        'id_usuario' =>
            $idUsuario
    ];
}


/**
 * Autenticar usuário ou administrador
 */
function autenticarUsuario(
    $conexao,
    $email,
    $senha
) {

    if (!$conexao) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro de conexão com o banco de dados.',
            'usuario' => null
        ];
    }


    $email =
        strtolower(
            trim($email)
        );

    $senha =
        trim($senha);


    if (
        empty($email) ||
        empty($senha)
    ) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Informe o email e a senha.',
            'usuario' => null
        ];
    }


    $sql =
        "SELECT
            id,
            nome,
            email,
            senha,
            tipo,
            criado_em
         FROM usuarios
         WHERE email = ?
         LIMIT 1";


    $stmt =
        $conexao->prepare($sql);


    if (!$stmt) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao preparar login: ' .
                $conexao->error,
            'usuario' => null
        ];
    }


    $stmt->bind_param(
        "s",
        $email
    );


    if (!$stmt->execute()) {

        $stmt->close();

        return [
            'sucesso' => false,
            'mensagem' =>
                'Erro ao executar login.',
            'usuario' => null
        ];
    }


    $resultado =
        $stmt->get_result();


    if (
        !$resultado ||
        $resultado->num_rows === 0
    ) {

        $stmt->close();

        return [
            'sucesso' => false,
            'mensagem' =>
                'Email ou senha incorretos.',
            'usuario' => null
        ];
    }


    $usuario =
        $resultado->fetch_assoc();


    $stmt->close();


    /* --------------------------------------------------------
       VERIFICAR SENHA
       -------------------------------------------------------- */

    $senhaCorreta = false;


    if (
        !empty($usuario['senha'])
    ) {

        if (
            password_verify(
                $senha,
                $usuario['senha']
            )
        ) {

            $senhaCorreta = true;

        } elseif (
            $senha === $usuario['senha']
        ) {

            /*
             * Compatibilidade com senhas antigas.
             */
            $senhaCorreta = true;
        }
    }


    if (!$senhaCorreta) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Email ou senha incorretos.',
            'usuario' => null
        ];
    }


    /*
     * Nunca guardar senha na sessão.
     */
    unset(
        $usuario['senha']
    );


    return [
        'sucesso' => true,
        'mensagem' =>
            'Login realizado com sucesso.',
        'usuario' =>
            $usuario
    ];
}


/**
 * Compatibilidade com autenticarAdmin()
 */
function autenticarAdmin(
    $conexao,
    $email,
    $senha
) {

    $resultado =
        autenticarUsuario(
            $conexao,
            $email,
            $senha
        );


    if (!$resultado['sucesso']) {
        return $resultado;
    }


    if (
        !isset($resultado['usuario']['tipo']) ||
        $resultado['usuario']['tipo'] !== 'admin'
    ) {

        return [
            'sucesso' => false,
            'mensagem' =>
                'Esta conta não possui acesso administrativo.',
            'usuario' => null
        ];
    }


    return $resultado;
}


/* ============================================================
   SESSÃO DO USUÁRIO
   ============================================================ */


/**
 * Iniciar sessão do usuário
 */
function iniciarSessaoUsuario($usuario)
{
    if (
        !is_array($usuario) ||
        empty($usuario['id'])
    ) {

        return false;
    }


    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    session_regenerate_id(true);


    $_SESSION['usuario'] = [
        'id' =>
            (int)$usuario['id'],

        'nome' =>
            $usuario['nome'] ?? '',

        'email' =>
            $usuario['email'] ?? '',

        'tipo' =>
            $usuario['tipo'] ?? 'usuario'
    ];


    /*
     * Se for administrador, também
     * cria sessão administrativa.
     */
    if (
        isset($usuario['tipo']) &&
        $usuario['tipo'] === 'admin'
    ) {

        $_SESSION['usuario_admin'] = [
            'id' =>
                (int)$usuario['id'],

            'nome' =>
                $usuario['nome'] ?? '',

            'email' =>
                $usuario['email'] ?? '',

            'tipo' =>
                'admin'
        ];
    }


    return true;
}


/**
 * Iniciar sessão do administrador
 */
function iniciarSessaoAdmin($usuario)
{
    if (
        !is_array($usuario) ||
        empty($usuario['id'])
    ) {

        return false;
    }


    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    session_regenerate_id(true);


    $_SESSION['usuario_admin'] = [
        'id' =>
            (int)$usuario['id'],

        'nome' =>
            $usuario['nome'] ?? '',

        'email' =>
            $usuario['email'] ?? '',

        'tipo' =>
            'admin'
    ];

    // Mantém a sessão principal sincronizada com a sessão administrativa.
    $_SESSION['usuario'] = $_SESSION['usuario_admin'];


    $_SESSION['usuario'] = [
        'id' =>
            (int)$usuario['id'],

        'nome' =>
            $usuario['nome'] ?? '',

        'email' =>
            $usuario['email'] ?? '',

        'tipo' =>
            'admin'
    ];


    return true;
}


/**
 * Verificar se existe usuário logado
 */
function usuarioLogado()
{
    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    return (
        isset($_SESSION['usuario']) &&
        !empty($_SESSION['usuario']['id'])
    );
}


/**
 * Verificar se usuário atual é administrador
 */
function usuarioEhAdmin()
{
    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    return (
        isset($_SESSION['usuario']) &&
        isset($_SESSION['usuario']['tipo']) &&
        $_SESSION['usuario']['tipo'] === 'admin'
    );
}


/**
 * Verificar se administrador está logado
 */
function administradorLogado()
{
    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    return (
        isset($_SESSION['usuario_admin']) &&
        !empty($_SESSION['usuario_admin']['id']) &&
        isset($_SESSION['usuario_admin']['tipo']) &&
        $_SESSION['usuario_admin']['tipo'] === 'admin'
    );
}


/**
 * Retornar usuário atual
 */
function obterUsuarioAtual()
{
    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    if (
        !isset($_SESSION['usuario'])
    ) {

        return null;
    }


    return $_SESSION['usuario'];
}


/**
 * Retornar administrador atual
 */
function obterAdminAtual()
{
    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    if (
        isset($_SESSION['usuario_admin'])
    ) {

        return $_SESSION['usuario_admin'];
    }


    return null;
}


/**
 * Retornar usuário atual do banco
 */
function obterUsuarioAtualBanco($conexao)
{
    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    if (
        !isset($_SESSION['usuario']['id'])
    ) {

        return null;
    }


    return buscarUsuarioPorId(
        $conexao,
        $_SESSION['usuario']['id']
    );
}


/**
 * Encerrar sessão
 */
function encerrarSessao()
{
    if (
        session_status() ===
        PHP_SESSION_NONE
    ) {

        session_start();
    }


    $_SESSION = [];


    if (
        ini_get("session.use_cookies")
    ) {

        $params =
            session_get_cookie_params();


        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }


    session_destroy();


    return true;
}


/**
 * Encerrar sessão administrativa
 */
function encerrarSessaoAdmin()
{
    return encerrarSessao();
}


/* ============================================================
   VALIDAÇÕES
   ============================================================ */


/**
 * Validar email
 */
function validarEmail($email)
{
    if (empty($email)) {
        return false;
    }

    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) !== false;
}


/**
 * Validar telefone
 */
function validarTelefone($telefone)
{
    /*
     * Telefone vazio é permitido.
     */
    if (empty($telefone)) {
        return true;
    }

    $telefoneLimpo =
        preg_replace(
            "/[^0-9]/",
            "",
            $telefone
        );

    return
        strlen($telefoneLimpo) >= 10 &&
        strlen($telefoneLimpo) <= 11;
}


/**
 * Validar nome
 */
function validarNome($nome)
{
    if (
        empty($nome) ||
        strlen($nome) < 3
    ) {
        return false;
    }

    return preg_match(
        "/^[a-záéíóúâêôãõç\s]+$/iu",
        $nome
    ) !== false;
}


/**
 * Validar mensagem
 */
function validarMensagem($mensagem)
{
    if (
        empty($mensagem) ||
        strlen($mensagem) < 10
    ) {
        return false;
    }

    return strlen($mensagem) <= 2000;
}


/**
 * Validar dados do contato
 */
function validarDadosContato($dados)
{
    $erros = [];


    if (
        !validarNome(
            $dados['nome'] ?? ''
        )
    ) {

        $erros[] =
            'Nome inválido. Mínimo 3 caracteres.';
    }


    if (
        !validarEmail(
            $dados['email'] ?? ''
        )
    ) {

        $erros[] =
            'Email inválido.';
    }


    if (
        !validarTelefone(
            $dados['telefone'] ?? ''
        )
    ) {

        $erros[] =
            'Telefone inválido.';
    }


    if (
        !validarMensagem(
            $dados['mensagem'] ?? ''
        )
    ) {

        $erros[] =
            'Mensagem deve ter entre 10 e 2000 caracteres.';
    }


    return [
        'valido' =>
            count($erros) === 0,

        'erros' =>
            $erros
    ];
}


/* ============================================================
   FORMATAÇÃO
   ============================================================ */


/**
 * Formatar preço
 */
function formatarPreco($valor)
{
    if (
        !is_numeric($valor) ||
        $valor < 0
    ) {

        return "Sob consulta";
    }

    return "A partir de R$ " .
        number_format(
            $valor,
            2,
            ",",
            "."
        );
}


/**
 * Formatar data
 */
function formatarData($data)
{
    if (empty($data)) {
        return "N/A";
    }


    $dataObj =
        DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $data
        );


    if (!$dataObj) {

        $dataObj =
            DateTime::createFromFormat(
                'Y-m-d',
                $data
            );
    }


    if (!$dataObj) {
        return "N/A";
    }


    return $dataObj->format(
        'd/m/Y H:i'
    );
}


/**
 * Sanitizar texto para HTML
 */
function sanitizar($texto)
{
    if (is_array($texto)) {

        $texto =
            implode(
                ', ',
                $texto
            );
    }


    if ($texto === null) {
        $texto = '';
    }


    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* ============================================================
   STATUS DOS ORÇAMENTOS
   ============================================================ */


/**
 * Retornar classe visual do status
 */
function classeStatusOrcamento($status)
{
    $status =
        strtolower(
            trim(
                (string)$status
            )
        );


    if (
        $status === 'concluído' ||
        $status === 'concluido'
    ) {

        return 'status-concluido';
    }


    return 'status-pendente';
}


/**
 * Retornar texto do status
 */
function textoStatusOrcamento($status)
{
    if (empty($status)) {
        return 'Pendente';
    }


    return $status;
}


/* ============================================================
   FIM
   ============================================================ */

?>