<?php
include_once('./php/conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email_login_digitado']) && isset($_POST['senha_login_digitado'])) {
        $email_login = $_POST['email_login_digitado'];
        $senha_login = $_POST['senha_login_digitado'];

        $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email_usuario = ?");
        $stmt->bind_param('s', $email_login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario_logado = $result->fetch_assoc();
            if (password_verify($senha_login, $usuario_logado['senha_usuario'])) {
                session_start();
                $_SESSION['id_usuario'] = $usuario_logado['id_usuario'];
                $_SESSION['nome_usuario'] = $usuario_logado['nome_usuario'];
                $_SESSION['email_usuario'] = $usuario_logado['email_usuario'];
                $_SESSION['tipo_usuario'] = $usuario_logado['tipo_usuario'];
                header('Location: ./php/dashboard.php');
            } else {
                $login_errado = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Login</title>
</head>

<body>
    <main>
        <form method="POST" id="form_login">
            <h1>Entrar</h1>
            <div class="inputbox">
                <input type="email" name="email_login_digitado" required>
                <span>Email</span>
            </div>
            <div class="inputbox">
                <input type="password" name="senha_login_digitado" required>
                <span>Senha</span>
            </div>
            <p>Não tem uma conta?<a href="./php/cadastro.php">Cadastre-se</a></p>
            <button type="submit" id="criar_usuario">Entrar</button>
        </form>
    </main>
</body>
<?php if (isset($login_errado) && $login_errado): ?>
    <script>
        Swal.fire({
            icon: "error",
            title: "Credenciais inválidas",
            html: 'Verifique <strong>e-mail</strong> e <strong>senha</strong> digitados.',
            confirmButtonText: "Tentar novamente",
            confirmButtonColor: "#459EB5",
            color: "#ffffff",
            background: "#004759",
            allowOutsideClick: false,
            allowEscapeKey: true,
            showCloseButton: true,
            backdrop: "rgba(0,0,0,.6)",
            focusConfirm: true
        }).then((result) => {
            if (result.isConfirmed || result.isDismissed) {
                window.location.href = 'index.php';
            }
        });
    </script>
<?php endif; ?>


</html>