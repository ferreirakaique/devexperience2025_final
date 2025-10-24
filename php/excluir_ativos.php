<?php
include('conexao.php');
session_start();

// Verifica sessão
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

// Verifica se recebeu um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ativos.php');
    exit;
}

$id_ativo = intval($_GET['id']);
$excluido_sucesso = false;
$erro_exclusao = false;

// Confere se o ativo existe
$stmt = $conexao->prepare("SELECT nome_ativo FROM ativos WHERE id_ativo = ?");
$stmt->bind_param("i", $id_ativo);
$stmt->execute();
$result = $stmt->get_result();
$ativo = $result->fetch_assoc();
$stmt->close();

// Exclui o ativo
$stmt = $conexao->prepare("DELETE FROM ativos WHERE id_ativo = ?");
$stmt->bind_param("i", $id_ativo);

if ($stmt->execute()) {
    header('location:ativos.php');
}
$stmt->close();
