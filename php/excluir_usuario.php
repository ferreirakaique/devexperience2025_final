<?php
include('conexao.php');
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: colaboradores.php?msg=excluido");
    } else {
        echo "Erro ao excluir colaborador.";
    }
} else {
    header("Location: colaboradores.php");
}
