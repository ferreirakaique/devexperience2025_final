<?php
include('conexao.php');
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

$filtro = $_GET['f'] ?? 'todas'; // 'todas' | 'so-ativos' | 'so-usuarios'
$where = "1=1";
if ($filtro === 'so-ativos')   $where = "n.fk_id_ativo IS NOT NULL";
if ($filtro === 'so-usuarios') $where = "n.fk_id_ativo IS NULL";

$sql = "SELECT n.*, a.nome_ativo, u.nome_usuario
        FROM notificacao n
        LEFT JOIN ativos a ON a.id_ativo = n.fk_id_ativo
        INNER JOIN usuarios u ON u.id_usuario = n.fk_id_usuario
        WHERE $where
        ORDER BY n.criado_em DESC";

$res = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Notificações de Ativos</title>
    <link rel="stylesheet" href="../css/colaboradores.css">
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
    <style>
        main {
            padding: 80px 20px;
        }

        .cards {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        }

        .card {
            background: rgba(0, 71, 89, .55);
            border: 1px solid rgba(69, 158, 181, .5);
            border-radius: 16px;
            padding: 16px;
            color: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        }

        .card h3 {
            margin: 0 0 6px;
            font-weight: 600
        }

        .meta {
            font-size: .9rem;
            opacity: .9;
            margin-bottom: 8px;
        }

        .pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .3);
            font-size: .8rem;
        }

        .filtros {
            display: flex;
            gap: 10px;
            margin: 0 0 18px;
        }

        .filtros a {
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid #459EB5;
            color: #fff;
            text-decoration: none;
            background: linear-gradient(180deg, #459EB5, #004759);
        }
    </style>
</head>

<body>
    <?php include_once('nav.php'); ?>
    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-bell"></i> Notificações de Ativos</h1>
            <p>Novos cadastros, edições e exclusões são mostrados aqui.</p>
        </div>

        <div class="filtros">
            <a href="?f=todas">Todas</a>
            <a href="?f=so-ativos">Relacionadas a ativos</a>
            <a href="?f=so-usuarios">Sem ativo (exclusões)</a>
        </div>

        <div class="cards">
            <?php if ($res && $res->num_rows): while ($n = $res->fetch_assoc()): ?>
                    <div class="card">
                        <h3><i class="fa-solid fa-circle-exclamation"></i> Notificação</h3>
                        <p><?php echo htmlspecialchars($n['descricao_notificacao']); ?></p>

                        <p class="meta">
                            <?php if ($n['fk_id_ativo']): ?>
                                <span class="pill">Ativo: #<?php echo $n['fk_id_ativo']; ?><?php echo $n['nome_ativo'] ? ' • ' . htmlspecialchars($n['nome_ativo']) : ''; ?></span>
                                &nbsp;•&nbsp;
                            <?php else: ?>
                                <span class="pill">Sem ativo (exclusão)</span>
                                &nbsp;•&nbsp;
                            <?php endif; ?>
                            <span class="pill">Quem: <?php echo htmlspecialchars($n['nome_usuario']); ?></span>
                            &nbsp;•&nbsp;
                            <span class="pill"><?php echo date('d/m/Y H:i', strtotime($n['criado_em'])); ?></span>
                        </p>

                        <?php if ($n['fk_id_ativo']): ?>
                            <a class="pill" href="editar_ativos.php?id=<?php echo $n['fk_id_ativo']; ?>">Ver ativo</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile;
            else: ?>
                <p>Nenhuma notificação registrada.</p>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>