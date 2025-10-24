<?php
include('conexao.php');
session_start();

// Verifica sessão
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

// Dados do usuário
$id_usuario   = $_SESSION["id_usuario"];
$nome_usuario = $_SESSION["nome_usuario"];
$email_usuario = $_SESSION["email_usuario"];
$tipo_usuario = $_SESSION["tipo_usuario"];

$cadastro_sucesso = false;
$erro_cadastro = false;

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_ativo          = trim($_POST['nome_ativo']);
    $categoria_ativo     = trim($_POST['categoria_ativo']);
    $valor_ativo         = trim($_POST['valor_ativo']);
    $data_aquisicao_ativo = $_POST['data_aquisicao_ativo'];
    $numero_serie_ativo  = trim($_POST['numero_serie_ativo']);
    $status_ativo        = $_POST['status_ativo'];
    $localizacao_ativo   = trim($_POST['localizacao_ativo']);

    // Validação básica
    if ($nome_ativo && $categoria_ativo && $valor_ativo && $data_aquisicao_ativo && $status_ativo) {
        $stmt = $conexao->prepare("INSERT INTO ativos 
            (nome_ativo, categoria_ativo, valor_ativo, data_aquisicao_ativo, numero_serie_ativo, status_ativo, localizacao_ativo)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssdssss",
            $nome_ativo,
            $categoria_ativo,
            $valor_ativo,
            $data_aquisicao_ativo,
            $numero_serie_ativo,
            $status_ativo,
            $localizacao_ativo
        );

        if ($stmt->execute()) {
            $cadastro_sucesso = true;
        } else {
            $erro_cadastro = true;
        }

        $stmt->close();
    } else {
        $erro_cadastro = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/cadastrar_ativos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Cadastrar Ativos</title>
</head>

<body>
    <?php include_once('nav.php') ?>
    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-boxes-stacked"></i> Cadastrar Ativo</h1>
            <p>Preencha todas as informações abaixo para registrar um novo ativo no sistema.</p>
        </div>

        <form method="POST" id="form_cadastrar_ativos">
            <div class="inputbox">
                <input type="text" name="nome_ativo" required>
                <span>Nome do Ativo</span>
            </div>

            <div class="inputbox">
                <input type="text" name="categoria_ativo" required>
                <span>Categoria</span>
            </div>

            <div class="inputbox">
                <input type="number" step="0.01" name="valor_ativo" required>
                <span>Valor (R$)</span>
            </div>

            <div class="inputbox">
                <input type="date" name="data_aquisicao_ativo" required>
                <span id="data_aquisicao">Data de Aquisição</span>
            </div>

            <div class="inputbox">
                <input type="text" name="numero_serie_ativo" required>
                <span>Número de Série</span>
            </div>

            <div class="inputbox">
                <select name="status_ativo" required>
                    <option value="">Selecione o Status</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>

            <div class="inputbox">
                <input type="text" name="localizacao_ativo" required>
                <span>Localização</span>
            </div>

            <button type="submit" id="criar_ativo">Cadastrar Ativo</button>
        </form>
    </main>

    <!-- ALERTAS -->
    <?php if ($cadastro_sucesso): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Ativo cadastrado!",
                text: "O ativo foi adicionado com sucesso ao sistema.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            }).then(() => {
                window.location.href = "ativos.php";
            });
        </script>
    <?php elseif ($erro_cadastro): ?>
        <script>
            Swal.fire({
                icon: "error",
                title: "Erro ao cadastrar",
                text: "Verifique os dados preenchidos e tente novamente.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            });
        </script>
    <?php endif; ?>
</body>

</html>