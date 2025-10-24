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

$edicao_sucesso = false;
$erro_edicao = false;

// ID do ativo via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ativos.php');
    exit;
}

$id_ativo = intval($_GET['id']);

// Busca dados do ativo
$stmt = $conexao->prepare("SELECT * FROM ativos WHERE id_ativo = ?");
$stmt->bind_param("i", $id_ativo);
$stmt->execute();
$result = $stmt->get_result();
$ativo = $result->fetch_assoc();
$stmt->close();

// Se não encontrou
if (!$ativo) {
    echo "<script>
            alert('Ativo não encontrado!');
            window.location.href='ativos.php';
          </script>";
    exit;
}

// Atualização (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_ativo          = trim($_POST['nome_ativo']);
    $categoria_ativo     = trim($_POST['categoria_ativo']);
    $valor_ativo         = trim($_POST['valor_ativo']);
    $data_aquisicao_ativo = $_POST['data_aquisicao_ativo'];
    $numero_serie_ativo  = trim($_POST['numero_serie_ativo']);
    $status_ativo        = $_POST['status_ativo'];
    $localizacao_ativo   = trim($_POST['localizacao_ativo']);

    if ($nome_ativo && $categoria_ativo && $valor_ativo && $data_aquisicao_ativo && $status_ativo) {
        $stmt = $conexao->prepare("UPDATE ativos SET 
            nome_ativo = ?, 
            categoria_ativo = ?, 
            valor_ativo = ?, 
            data_aquisicao_ativo = ?, 
            numero_serie_ativo = ?, 
            status_ativo = ?, 
            localizacao_ativo = ?
            WHERE id_ativo = ?");
        $stmt->bind_param(
            "ssdssssi",
            $nome_ativo,
            $categoria_ativo,
            $valor_ativo,
            $data_aquisicao_ativo,
            $numero_serie_ativo,
            $status_ativo,
            $localizacao_ativo,
            $id_ativo
        );

        if ($stmt->execute()) {
            $edicao_sucesso = true;
        } else {
            $erro_edicao = true;
        }

        $stmt->close();
    } else {
        $erro_edicao = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/editar_ativos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Editar Ativo</title>
</head>

<body>
    <?php include_once('nav.php') ?>
    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-pen-to-square"></i> Editar Ativo #<?php echo htmlspecialchars($id_ativo); ?></h1>
            <p>Atualize as informações do ativo abaixo e salve para registrar as alterações.</p>
        </div>

        <form method="POST" id="form_cadastrar_ativos">
            <div class="inputbox">
                <input type="text" name="nome_ativo" value="<?php echo htmlspecialchars($ativo['nome_ativo']); ?>" required>
                <span>Nome do Ativo</span>
            </div>

            <div class="inputbox">
                <input type="text" name="categoria_ativo" value="<?php echo htmlspecialchars($ativo['categoria_ativo']); ?>" required>
                <span>Categoria</span>
            </div>

            <div class="inputbox">
                <input type="number" step="0.01" name="valor_ativo" value="<?php echo htmlspecialchars($ativo['valor_ativo']); ?>" required>
                <span>Valor (R$)</span>
            </div>

            <div class="inputbox">
                <input type="date" name="data_aquisicao_ativo" value="<?php echo htmlspecialchars($ativo['data_aquisicao_ativo']); ?>" required>
                <span>Data de Aquisição</span>
            </div>

            <div class="inputbox">
                <input type="text" name="numero_serie_ativo" value="<?php echo htmlspecialchars($ativo['numero_serie_ativo']); ?>">
                <span>Número de Série</span>
            </div>

            <div class="inputbox">
                <select name="status_ativo" required>
                    <option value="">Selecione o Status</option>
                    <option value="ativo" <?php if ($ativo['status_ativo'] === 'ativo') echo 'selected'; ?>>Ativo</option>
                    <option value="inativo" <?php if ($ativo['status_ativo'] === 'inativo') echo 'selected'; ?>>Inativo</option>
                </select>
            </div>

            <div class="inputbox">
                <input type="text" name="localizacao_ativo" value="<?php echo htmlspecialchars($ativo['localizacao_ativo']); ?>" required>
                <span>Localização</span>
            </div>

            <button type="submit" id="criar_ativo">Salvar Alterações</button>
        </form>
    </main>

    <!-- ALERTAS -->
    <?php if ($edicao_sucesso): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Alterações salvas!",
                text: "As informações do ativo foram atualizadas com sucesso.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            }).then(() => {
                window.location.href = "ativos.php";
            });
        </script>
    <?php elseif ($erro_edicao): ?>
        <script>
            Swal.fire({
                icon: "error",
                title: "Erro ao atualizar",
                text: "Verifique os dados e tente novamente.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            });
        </script>
    <?php endif; ?>
</body>

</html>