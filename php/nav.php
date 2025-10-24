<?php
include_once('conexao.php');

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}

$nome_usuario = $_SESSION['nome_usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$isAdmin = ($tipo_usuario === 'Admin');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/nav.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
</head>

<header>
    <nav>
        <ul>
            <li><img src="../img/youtan.png" alt=""></li>

            <?php if ($isAdmin): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="colaboradores.php">Colaboradores</a></li>
            <?php endif; ?>

            <li><a href="ativos.php">Ativos</a></li>
            <li><a href="manutencoes.php">Manutenções</a></li>

            <li><a href="perfil.php">Olá, <?php echo htmlspecialchars($nome_usuario) ?> | <?php echo htmlspecialchars($tipo_usuario) ?></a></li>

            <li class="logout">
                <a href="#" id="btnLogout" title="Sair" aria-label="Sair">
                    <i class="fa-solid fa-right-from-bracket"></i> <span>Sair</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<a href="chatbot.php" class="fab_chat" title="Youtan Helper">
    <i class="fa-solid fa-robot"></i>
</a>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnLogout');
        if (!btn) return;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            Swal.fire({
                icon: 'question',
                title: 'Deseja sair?',
                text: 'Você será desconectado da sua conta.',
                showCancelButton: true,
                confirmButtonText: 'Sair',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e84a4a',
                cancelButtonColor: '#459EB5',
                color: '#ffffff',
                background: '#004759',
            }).then((r) => {
                if (r.isConfirmed) window.location.href = 'logout.php';
            });
        });
    });
</script>

</html>