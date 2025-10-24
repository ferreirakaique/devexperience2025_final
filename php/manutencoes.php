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

// Define se é Admin
$isAdmin = ($tipo_usuario === 'Admin');

// Busca todas as manutenções com o nome do ativo e do responsável
$query = "
    SELECT 
        m.id_manutencao,
        a.nome_ativo,
        m.tipo_manutencao,
        m.data_manutencao,
        u.nome_usuario AS responsavel,
        m.custo_manutencao,
        m.descricao_manutencao
    FROM manutencoes m
    JOIN ativos a ON m.id_manutencao = a.id_ativo
    JOIN usuarios u ON m.responsavel_manutencao = u.id_usuario
    ORDER BY m.data_manutencao DESC
";

$result = $conexao->query($query);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenções</title>
    <link rel="stylesheet" href="../css/manutencoes.css">
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include_once('nav.php'); ?>

    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-wrench"></i> Histórico de Manutenções</h1>
            <p>Veja todas as manutenções registradas nos ativos, com detalhes sobre tipo, responsável e custo.</p>
        </div>

        <div class="cards_container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card_manutencao <?php echo strtolower($row['tipo_manutencao']); ?>">
                        <div class="card_header">
                            <h2><i class="fa-solid fa-screwdriver-wrench"></i> <?php echo htmlspecialchars($row['nome_ativo']); ?></h2>
                            <span class="tag_tipo"><?php echo htmlspecialchars($row['tipo_manutencao']); ?></span>
                        </div>

                        <div class="card_body">
                            <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($row['data_manutencao'])); ?></p>
                            <p><strong>Responsável:</strong> <?php echo htmlspecialchars($row['responsavel']); ?></p>
                            <p><strong>Custo:</strong> R$ <?php echo number_format($row['custo_manutencao'], 2, ',', '.'); ?></p>
                            <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($row['descricao_manutencao'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="sem_manutencoes">Nenhuma manutenção registrada até o momento.</p>
            <?php endif; ?>
        </div>

        <!-- Botão só aparece para ADMIN -->
        <?php if ($isAdmin): ?>
            <a href="cadastrar_manutencoes.php" class="botao_nova">
                <i class="fa-solid fa-plus"></i> Registrar Nova Manutenção
            </a>
        <?php endif; ?>
    </main>
</body>

</html>