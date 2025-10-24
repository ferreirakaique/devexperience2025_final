<?php
include_once('conexao.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_usuario = $_POST['nome_cadastro_digitado'];
    $email_usuario = $_POST['email_cadastro_digitado'];
    $senha_usuario = password_hash($_POST['senha_cadastro_digitado'], PASSWORD_DEFAULT);
    $tipo_usuario = $_POST['tipo_usuario_digitado'];

    $stmt = $conexao->prepare('INSERT INTO usuarios (nome_usuario,email_usuario,senha_usuario,tipo_usuario) VALUES(?,?,?,?)');
    $stmt->bind_param('ssss', $nome_usuario, $email_usuario, $senha_usuario, $tipo_usuario);
    $stmt->execute();
    $cadastro_sucesso = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/cadastro.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Cadastro</title>
</head>

<body>
    <main>
        <form method="POST" id="form_login">
            <button id="voltar_login" type="button" class="btn-icone" aria-label="Voltar" title="Voltar">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="sr-only">Voltar</span>
            </button>
            <h1>Cadastrar Administrador</h1>
            <div class="inputbox">
                <input type="nome" name="nome_cadastro_digitado" required>
                <span>Nome</span>
            </div>
            <div class="inputbox">
                <input type="email" name="email_cadastro_digitado" required>
                <span>Email</span>
            </div>
            <div class="inputbox">
                <input type="password" name="senha_cadastro_digitado" required>
                <span>Senha</span>
            </div>
            <input type="hidden" name="tipo_usuario_digitado" value="admin">
            <button type="submit" id="criar_usuario">Criar conta</button>
        </form>
    </main>

    <script>
        document.getElementById('voltar_login')?.addEventListener('click', () => {
            if (window.history.length > 1) {
                history.back();
            } else {
                window.location.href = '../index.php';
            }
        });
    </script>

    <?php if (isset($login_errado) && $login_errado): ?>
        <script>
            Swal.fire({
                icon: "error",
                title: "Credenciais inválidas",
                text: "Verifique email e senha digitados",
                confirmButtonText: "OK",
                confirmButtonColor: "#459EB5", // cor Youtan
                color: "#ffffff",
                background: "#004759"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
        </script>

    <?php elseif (isset($cadastro_sucesso) && $cadastro_sucesso): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Cadastro realizado com sucesso",
                text: "Você será redirecionado para a página de login",
                confirmButtonText: "Ir para o login",
                confirmButtonColor: "#459EB5", // cor Youtan
                color: "#ffffff",
                background: "#004759"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../index.php';
                }
            });
        </script>
    <?php endif; ?>



</body>


</html>