<?php
include('conexao.php');
session_start();

// Verifica sessão
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

// Dados do usuário logado
$id_usuario   = $_SESSION["id_usuario"];
$nome_usuario = $_SESSION["nome_usuario"];
$email_usuario = $_SESSION["email_usuario"];
$tipo_usuario = $_SESSION["tipo_usuario"];

$edicao_sucesso = false;
$erro_edicao = false;

// ID do usuário a editar
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: colaboradores.php');
    exit;
}

$id_editar = intval($_GET['id']);

// Busca os dados do usuário
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_editar);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: colaboradores.php');
    exit;
}

$usuario = $result->fetch_assoc();

// Atualiza se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome_usuario']);
    $email = trim($_POST['email_usuario']);
    $tipo = $_POST['tipo_usuario'];
    $senha = trim($_POST['senha_usuario']);
    $confirmar = trim($_POST['confirmar_senha']);

    if ($senha !== "" && $senha !== $confirmar) {
        $erro_edicao = true;
    } else {
        if ($senha !== "") {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt_update = $conexao->prepare("UPDATE usuarios SET nome_usuario=?, email_usuario=?, senha_usuario=?, tipo_usuario=? WHERE id_usuario=?");
            $stmt_update->bind_param("ssssi", $nome, $email, $senha_hash, $tipo, $id_editar);
        } else {
            $stmt_update = $conexao->prepare("UPDATE usuarios SET nome_usuario=?, email_usuario=?, tipo_usuario=? WHERE id_usuario=?");
            $stmt_update->bind_param("sssi", $nome, $email, $tipo, $id_editar);
        }

        if ($stmt_update->execute()) {
            $edicao_sucesso = true;

            // 🔄 Atualiza sessão se o usuário editar o próprio perfil
            if ($id_editar == $_SESSION['id_usuario']) {
                $_SESSION['nome_usuario']  = $nome;
                $_SESSION['email_usuario'] = $email;
                $_SESSION['tipo_usuario']  = $tipo;
            }
        } else {
            $erro_edicao = true;
        }

        $stmt_update->close();
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../css/editar_usuarios.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include_once('nav.php'); ?>

    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-user-pen"></i> Editar Usuário #<?php echo htmlspecialchars($id_editar); ?></h1>
            <p>Atualize as informações do colaborador e salve as alterações.</p>
        </div>

        <form method="POST" id="form_editar_usuario">
            <div class="inputbox">
                <input type="text" name="nome_usuario" value="<?php echo htmlspecialchars($usuario['nome_usuario']); ?>" required>
                <span>Nome</span>
            </div>

            <div class="inputbox">
                <input type="email" name="email_usuario" value="<?php echo htmlspecialchars($usuario['email_usuario']); ?>" required>
                <span>Email</span>
            </div>

            <div class="inputbox">
                <input type="password" name="senha_usuario" placeholder="•••••••••">
                <span>Nova Senha (opcional)</span>
            </div>

            <div class="inputbox">
                <input type="password" name="confirmar_senha" placeholder="•••••••••">
                <span>Confirmar Nova Senha</span>
            </div>

            <div class="inputbox">
                <select name="tipo_usuario" required>
                    <option value="" disabled selected>Selecione o Tipo</option>
                    <option value="Admin" <?php echo ($usuario['tipo_usuario'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="Colaborador" <?php echo ($usuario['tipo_usuario'] === 'Colaborador') ? 'selected' : ''; ?>>Colaborador</option>
                </select>
                <span id="tipo_usuario">Tipo de Usuário</span>
            </div>

            <button type="submit" id="btn_salvar_usuario">Salvar Alterações</button>
        </form>
    </main>

    <!-- ALERTAS -->
    <?php if ($edicao_sucesso): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Usuário atualizado!",
                text: "As informações foram salvas com sucesso.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            }).then(() => {
                window.location.href = "colaboradores.php";
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