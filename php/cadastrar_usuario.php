<?php
include('conexao.php');
session_start();

// Verifica sessão
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

// Dados da sessão
$id_usuario   = $_SESSION["id_usuario"];
$nome_usuario = $_SESSION["nome_usuario"];
$email_usuario = $_SESSION["email_usuario"];
$tipo_usuario = $_SESSION["tipo_usuario"];

$cadastro_sucesso = false;
$erro_cadastro = false;

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_usuario_novo = trim($_POST['nome_usuario']);
    $email_usuario_novo = trim($_POST['email_usuario']);
    $senha_usuario = trim($_POST['senha_usuario']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $tipo_usuario_novo = $_POST['tipo_usuario'];

    // Validações básicas
    if ($nome_usuario_novo && $email_usuario_novo && $senha_usuario && $confirmar_senha && $tipo_usuario_novo) {
        if ($senha_usuario === $confirmar_senha) {
            // Verifica se o e-mail já existe
            $check = $conexao->prepare("SELECT id_usuario FROM usuarios WHERE email_usuario = ?");
            $check->bind_param("s", $email_usuario_novo);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $erro_cadastro = true;
            } else {
                $senha_hash = password_hash($senha_usuario, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("INSERT INTO usuarios (nome_usuario, email_usuario, senha_usuario, tipo_usuario) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nome_usuario_novo, $email_usuario_novo, $senha_hash, $tipo_usuario_novo);

                if ($stmt->execute()) {
                    $cadastro_sucesso = true;
                } else {
                    $erro_cadastro = true;
                }

                $stmt->close();
            }
            $check->close();
        } else {
            $erro_cadastro = true; // Senhas diferentes
        }
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
    <link rel="stylesheet" href="../css/cadastrar_usuario.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Cadastrar Colaborador</title>
</head>

<body>
    <?php include_once('nav.php'); ?>
    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-user-plus"></i> Cadastrar Colaborador</h1>
            <p>Preencha todas as informações abaixo para registrar um novo colaborador no sistema.</p>
        </div>

        <form method="POST" id="form_cadastrar_colaborador">
            <div class="inputbox">
                <input type="text" name="nome_usuario" required>
                <span>Nome Completo</span>
            </div>

            <div class="inputbox">
                <input type="email" name="email_usuario" required>
                <span>Email</span>
            </div>

            <div class="inputbox">
                <input type="password" name="senha_usuario" required>
                <span>Senha</span>
            </div>

            <div class="inputbox">
                <input type="password" name="confirmar_senha" required>
                <span>Confirmar Senha</span>
            </div>

            <div class="inputbox">
                <select name="tipo_usuario" required>
                    <option value="" disabled selected>Selecione o Tipo</option>
                    <option value="Admin">Admin</option>
                    <option value="Colaborador">Colaborador</option>
                </select>
                <span id="tipo_usuario">Tipo de Usuário</span>
            </div>

            <button type="submit" id="btn_criar_colaborador">Cadastrar Colaborador</button>
        </form>
    </main>

    <!-- ALERTAS -->
    <?php if ($cadastro_sucesso): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Colaborador cadastrado!",
                text: "O novo colaborador foi adicionado com sucesso.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            }).then(() => {
                window.location.href = "colaboradores.php";
            });
        </script>
    <?php elseif ($erro_cadastro): ?>
        <script>
            Swal.fire({
                icon: "error",
                title: "Erro ao cadastrar",
                text: "Verifique os dados preenchidos (e-mail já existe ou senhas diferentes).",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            });
        </script>
    <?php endif; ?>
</body>

</html>