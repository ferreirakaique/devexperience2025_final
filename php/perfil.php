<?php
include('conexao.php');
session_start();

// Verifica sessão
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

// Dados atuais do usuário
$id_usuario   = $_SESSION["id_usuario"];
$nome_usuario = $_SESSION["nome_usuario"];
$email_usuario = $_SESSION["email_usuario"];
$tipo_usuario = $_SESSION["tipo_usuario"];

$sucesso = false;
$erro = false;

// Atualizar dados
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novo_nome  = trim($_POST['nome_usuario']);
    $novo_email = trim($_POST['email_usuario']);
    $senha_atual = trim($_POST['senha_atual']);
    $nova_senha = trim($_POST['nova_senha']);
    $confirmar  = trim($_POST['confirmar_senha']);

    // Buscar senha atual do banco
    $stmt = $conexao->prepare("SELECT senha_usuario FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($senha_banco);
    $stmt->fetch();
    $stmt->close();

    // Verifica senha atual
    if (password_verify($senha_atual, $senha_banco)) {
        // Atualiza dados
        if (!empty($nova_senha)) {
            if ($nova_senha === $confirmar) {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt_update = $conexao->prepare("UPDATE usuarios SET nome_usuario=?, email_usuario=?, senha_usuario=? WHERE id_usuario=?");
                $stmt_update->bind_param("sssi", $novo_nome, $novo_email, $senha_hash, $id_usuario);
            } else {
                $erro = true; // senha nova e confirmação diferentes
            }
        } else {
            $stmt_update = $conexao->prepare("UPDATE usuarios SET nome_usuario=?, email_usuario=? WHERE id_usuario=?");
            $stmt_update->bind_param("ssi", $novo_nome, $novo_email, $id_usuario);
        }

        if (isset($stmt_update)) {
            if ($stmt_update->execute()) {
                $sucesso = true;
                // Atualiza sessão
                $_SESSION["nome_usuario"] = $novo_nome;
                $_SESSION["email_usuario"] = $novo_email;
            } else {
                $erro = true;
            }
            $stmt_update->close();
        }
    } else {
        $erro = true; // senha atual incorreta
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/perfil.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Meu Perfil</title>
</head>

<body>
    <?php include_once('nav.php'); ?>
    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-user"></i> Meu Perfil</h1>
            <p>Visualize e atualize suas informações pessoais de forma segura.</p>
        </div>

        <form method="POST" id="form_perfil">
            <div class="inputbox">
                <input type="text" name="nome_usuario" value="<?php echo htmlspecialchars($nome_usuario); ?>" required>
                <span>Nome Completo</span>
            </div>

            <div class="inputbox">
                <input type="email" name="email_usuario" value="<?php echo htmlspecialchars($email_usuario); ?>" required>
                <span>Email</span>
            </div>

            <div class="inputbox">
                <input type="password" name="senha_atual" required>
                <span>Senha Atual</span>
            </div>

            <div class="inputbox">
                <input type="password" name="nova_senha">
                <span>Nova Senha (opcional)</span>
            </div>

            <div class="inputbox">
                <input type="password" name="confirmar_senha">
                <span>Confirmar Nova Senha</span>
            </div>

            <button type="submit" id="btn_salvar_perfil">Salvar Alterações</button>
        </form>
    </main>

    <!-- ALERTAS -->
    <?php if ($sucesso): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Perfil atualizado!",
                text: "Suas informações foram atualizadas com sucesso.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            });
        </script>
    <?php elseif ($erro): ?>
        <script>
            Swal.fire({
                icon: "error",
                title: "Erro ao atualizar",
                text: "Verifique a senha atual e os dados informados.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            });
        </script>
    <?php endif; ?>
</body>

</html>