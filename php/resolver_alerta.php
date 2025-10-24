<?php
// /php/resolver_alerta.php
include('conexao.php');
session_start();
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$p = json_decode($raw, true);
$id = isset($p['id']) ? (int)$p['id'] : 0;
$acao = $p['acao'] ?? '';

if (!$id || !in_array($acao, ['lido', 'resolvido'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Parâmetros inválidos']);
    exit;
}

$uid = $_SESSION['id_usuario'] ?? null;

if ($acao === 'lido') {
    $stmt = $conexao->prepare("UPDATE notificacoes SET status='lido', lido_em=NOW() WHERE id_notificacao=? AND status <> 'resolvido'");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conexao->prepare("UPDATE notificacoes SET status='resolvido', resolvido_em=NOW() WHERE id_notificacao=?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
}

$stmt = $conexao->prepare("INSERT INTO notificacao_logs (id_notificacao, acao, id_usuario) VALUES (?,?,?)");
$stmt->bind_param("isi", $id, $acao, $uid);
$stmt->execute();
$stmt->close();

echo json_encode(['status' => $ok ? 'ok' : 'erro']);
