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

$cadastro_sucesso = false;
$erro_cadastro = false;

// Busca os ativos cadastrados
$stmt_ativos = $conexao->prepare("SELECT id_ativo, nome_ativo FROM ativos ORDER BY nome_ativo");
$stmt_ativos->execute();
$result_ativos = $stmt_ativos->get_result();

// Busca os usuários (responsáveis técnicos)
$stmt_usuarios = $conexao->prepare("SELECT id_usuario, nome_usuario FROM usuarios ORDER BY nome_usuario");
$stmt_usuarios->execute();
$result_usuarios = $stmt_usuarios->get_result();

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_ativo = $_POST['id_ativo'];
    $tipo_manutencao = $_POST['tipo_manutencao'];
    $data_manutencao = $_POST['data_manutencao'];
    $responsavel_manutencao = $_POST['responsavel_manutencao'];
    $custo_manutencao = $_POST['custo_manutencao'];
    $descricao_manutencao = trim($_POST['descricao_manutencao']);

    if ($id_ativo && $tipo_manutencao && $data_manutencao && $responsavel_manutencao && $custo_manutencao) {
        $stmt = $conexao->prepare("INSERT INTO manutencoes 
            (id_manutencao, tipo_manutencao, data_manutencao, responsavel_manutencao, custo_manutencao, descricao_manutencao)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issids", $id_ativo, $tipo_manutencao, $data_manutencao, $responsavel_manutencao, $custo_manutencao, $descricao_manutencao);

        if ($stmt->execute()) {
            $cadastro_sucesso = true;
        } else {
            $erro_cadastro = true;
        }
        $stmt->close();
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
    <title>Cadastrar Manutenção</title>
    <link rel="stylesheet" href="../css/cadastrar_manutencoes.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include_once('nav.php'); ?>

    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-screwdriver-wrench"></i> Registrar Manutenção</h1>
            <p>Preencha as informações abaixo para registrar uma manutenção de um ativo.</p>
        </div>

        <form method="POST" id="form_cadastrar_manutencao">
            <div class="inputbox">
                <select name="id_ativo" required>
                    <option value="" disabled selected>Selecione o Ativo</option>
                    <?php while ($ativo = $result_ativos->fetch_assoc()): ?>
                        <option value="<?php echo $ativo['id_ativo']; ?>">
                            <?php echo htmlspecialchars($ativo['nome_ativo']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <span id="span_pracima">Ativo</span>
            </div>

            <div class="inputbox">
                <select name="tipo_manutencao" required>
                    <option value="" disabled selected>Selecione o Tipo</option>
                    <option value="Preventiva">Preventiva</option>
                    <option value="Corretiva">Corretiva</option>
                </select>
                <span id="span_pracima">Tipo de Manutenção</span>
            </div>

            <div class="inputbox">
                <input type="date" name="data_manutencao" required>
                <span id="span_pracima">Data da Manutenção</span>
            </div>

            <div class="inputbox">
                <select name="responsavel_manutencao" required>
                    <option value="" disabled selected>Selecione o Responsável Técnico</option>
                    <?php while ($user = $result_usuarios->fetch_assoc()): ?>
                        <option value="<?php echo $user['id_usuario']; ?>">
                            <?php echo htmlspecialchars($user['nome_usuario']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <span id="span_pracima">Responsável Técnico</span>
            </div>

            <div class="inputbox">
                <input type="number" step="0.01" name="custo_manutencao" required>
                <span>Custo (R$)</span>
            </div>

            <div class="inputbox">
                <input name="descricao_manutencao" rows="4" style="resize: none; width: 100%; border-radius: 14px; padding: 12px 14px; background: rgba(0,71,89,0.8); border: 1px solid rgba(69,158,181,0.5); color: white;" required></input>
                <span>Descrição do Serviço</span>
            </div>

            <button type="submit" id="btn_salvar_manutencao">Salvar Manutenção</button>
        </form>
    </main>

    <!-- ALERTAS -->
    <?php if ($cadastro_sucesso): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Manutenção registrada!",
                text: "A manutenção foi adicionada com sucesso ao histórico do ativo.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            }).then(() => {
                window.location.href = "manutencoes.php";
            });
        </script>
    <?php elseif ($erro_cadastro): ?>
        <script>
            Swal.fire({
                icon: "error",
                title: "Erro ao registrar",
                text: "Verifique os campos preenchidos e tente novamente.",
                confirmButtonColor: "#459EB5",
                color: "#ffffff",
                background: "#004759",
            });
        </script>
    <?php endif; ?>
</body>

</html>