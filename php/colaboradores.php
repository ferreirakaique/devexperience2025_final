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

// Busca todos os usuários
$stmt_colaborador = $conexao->prepare('SELECT * FROM usuarios');
$stmt_colaborador->execute();
$result_colaborador = $stmt_colaborador->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/colaboradores.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Gerenciar Colaboradores</title>
</head>

<body>
    <?php include_once('nav.php'); ?>

    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-users"></i> Gerenciar Colaboradores</h1>
            <p>Visualize, edite ou exclua os colaboradores cadastrados no sistema.</p>
        </div>

        <div class="tabela_container">
            <?php if ($result_colaborador->num_rows > 0): ?>
                <table class="tabela_usuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($colaborador = $result_colaborador->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($colaborador['id_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($colaborador['nome_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($colaborador['email_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($colaborador['tipo_usuario']); ?></td>
                                <td class="acoes">
                                    <a href="editar_usuarios.php?id=<?php echo urlencode($colaborador['id_usuario']); ?>"
                                        class="editar"
                                        data-id="<?php echo (int)$colaborador['id_usuario']; ?>"
                                        title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <a href="#"
                                        class="excluir"
                                        title="Excluir"
                                        data-id="<?php echo htmlspecialchars($colaborador['id_usuario']); ?>"
                                        data-nome="<?php echo htmlspecialchars($colaborador['nome_usuario']); ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum colaborador cadastrado.</p>
            <?php endif; ?>
        </div>

        <a href="cadastrar_usuario.php" class="botao_adicionar">+ Cadastrar novo colaborador</a>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const botoesExcluir = document.querySelectorAll(".excluir");

            botoesExcluir.forEach(botao => {
                botao.addEventListener("click", (e) => {
                    e.preventDefault();
                    const id = botao.dataset.id;
                    const nome = botao.dataset.nome;

                    Swal.fire({
                        icon: "warning",
                        title: "Excluir colaborador?",
                        html: `<strong>${nome}</strong><br><br>Esta ação não poderá ser desfeita.`,
                        showCancelButton: true,
                        confirmButtonText: "Sim, excluir",
                        cancelButtonText: "Cancelar",
                        confirmButtonColor: "#e84a4a",
                        cancelButtonColor: "#459EB5",
                        color: "#ffffff",
                        background: "#004759",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `excluir_usuario.php?id=${id}`;
                        }
                    });
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // ID do usuário logado, vindo da sessão PHP
            const myId = <?php echo (int)$id_usuario; ?>;

            // Bloquear edição do próprio usuário na lista
            document.querySelectorAll(".editar").forEach(link => {
                link.addEventListener("click", (e) => {
                    const targetId = parseInt(link.dataset.id, 10);

                    if (targetId === myId) {
                        e.preventDefault();
                        Swal.fire({
                            icon: "info",
                            title: "Edite pelo seu perfil",
                            html: "Para editar suas próprias informações, acesse <strong>Meu Perfil</strong>.",
                            showCancelButton: true,
                            confirmButtonText: "Ir para Meu Perfil",
                            cancelButtonText: "Cancelar",
                            confirmButtonColor: "#459EB5",
                            cancelButtonColor: "#6c757d",
                            color: "#ffffff",
                            background: "#004759",
                        }).then((r) => {
                            if (r.isConfirmed) {
                                window.location.href = "perfil.php";
                            }
                        });
                    }
                    // se não for o próprio usuário, segue o fluxo normal (abre editar_usuarios.php)
                });
            });

            // -------- seu código de exclusão permanece igual --------
            const botoesExcluir = document.querySelectorAll(".excluir");
            botoesExcluir.forEach(botao => {
                botao.addEventListener("click", (e) => {
                    e.preventDefault();
                    const id = botao.dataset.id;
                    const nome = botao.dataset.nome;

                    Swal.fire({
                        icon: "warning",
                        title: "Excluir colaborador?",
                        html: `<strong>${nome}</strong><br><br>Esta ação não poderá ser desfeita.`,
                        showCancelButton: true,
                        confirmButtonText: "Sim, excluir",
                        cancelButtonText: "Cancelar",
                        confirmButtonColor: "#e84a4a",
                        cancelButtonColor: "#459EB5",
                        color: "#ffffff",
                        background: "#004759",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `excluir_usuario.php?id=${id}`;
                        }
                    });
                });
            });
        });
    </script>

</body>

</html>