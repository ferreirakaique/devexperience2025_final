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

// Busca todos os ativos
$stmt_ativos = $conexao->prepare('SELECT * FROM ativos ORDER BY id_ativo ASC');
$stmt_ativos->execute();
$result_ativos = $stmt_ativos->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/ativos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <title>Ativos</title>
</head>

<body>
    <?php include_once('nav.php') ?>
    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-boxes-stacked"></i> Ativos Cadastrados</h1>
            <p>Visualize e gerencie todos os ativos registrados no sistema de forma simples e organizada.</p>
        </div>


        <div class="ativos_container">
            <?php if ($result_ativos->num_rows > 0): ?>
                <?php while ($ativo = $result_ativos->fetch_assoc()): ?>
                    <div class="ativo_card">
                        <?php if ($isAdmin): ?>
                            <div class="acoes">
                                <a href="editar_ativos.php?id=<?php echo urlencode($ativo['id_ativo']); ?>" class="editar" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="#"
                                    class="excluir"
                                    title="Excluir"
                                    data-id="<?php echo htmlspecialchars($ativo['id_ativo']); ?>"
                                    data-nome="<?php echo htmlspecialchars($ativo['nome_ativo']); ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                        <h2><?php echo htmlspecialchars($ativo['nome_ativo']); ?></h2>
                        <p><strong>Categoria:</strong> <?php echo htmlspecialchars($ativo['categoria_ativo']); ?></p>
                        <p><strong>Valor:</strong> R$ <?php echo number_format($ativo['valor_ativo'], 2, ',', '.'); ?></p>
                        <p><strong>Data de aquisição:</strong> <?php echo date('d/m/Y', strtotime($ativo['data_aquisicao_ativo'])); ?></p>
                        <p><strong>Nº de Série:</strong> <?php echo htmlspecialchars($ativo['numero_serie_ativo']); ?></p>
                        <p><strong>Localização:</strong> <?php echo htmlspecialchars($ativo['localizacao_ativo']); ?></p>
                        <p><strong>Status:</strong>
                            <span class="status <?php echo $ativo['status_ativo'] === 'ativo' ? 'ativo' : 'inativo'; ?>">
                                <?php echo ucfirst($ativo['status_ativo']); ?>
                            </span>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum ativo cadastrado.</p>
            <?php endif; ?>
        </div>

        <a href="cadastrar_ativos.php">+ Cadastrar novo ativo</a>
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
                        title: "Excluir ativo?",
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
                            window.location.href = `excluir_ativos.php?id=${id}`;
                        }
                    });
                });
            });
        });
    </script>

</body>

</html>