<?php
// /php/verificar_alertas.php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/config/email_helper.php'; // PHPMailer wrapper

date_default_timezone_set('America/Sao_Paulo');

function cria_alerta($conexao, $data)
{
    // Evita duplicata (índice UNIQUE garante, mas tratamos aqui também)
    $stmt = $conexao->prepare("SELECT id_notificacao FROM notificacoes
    WHERE tipo=? AND referencia_tipo=? AND referencia_id=? AND data_evento=?");
    $stmt->bind_param("ssis", $data['tipo'], $data['referencia_tipo'], $data['referencia_id'], $data['data_evento']);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existe) return $existe['id_notificacao'];

    $stmt = $conexao->prepare("INSERT INTO notificacoes
    (tipo, titulo, mensagem, referencia_tipo, referencia_id, data_evento, prioridade, canal_painel, canal_email, status)
    VALUES (?,?,?,?,?,?,?,?,?, 'pendente')");
    $stmt->bind_param(
        "ssssissii",
        $data['tipo'],
        $data['titulo'],
        $data['mensagem'],
        $data['referencia_tipo'],
        $data['referencia_id'],
        $data['data_evento'],
        $data['prioridade'],
        $data['canal_painel'],
        $data['canal_email']
    );
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    log_alerta($conexao, $id, 'criado', json_encode($data));
    return $id;
}

function log_alerta($conexao, $id_notificacao, $acao, $detalhes = '', $id_usuario = null)
{
    $stmt = $conexao->prepare("INSERT INTO notificacao_logs (id_notificacao, acao, detalhes, id_usuario) VALUES (?,?,?,?)");
    $stmt->bind_param("issi", $id_notificacao, $acao, $detalhes, $id_usuario);
    $stmt->execute();
    $stmt->close();
}

$hoje = (new DateTime())->format('Y-m-d');
$em7  = (new DateTime('+7 days'))->format('Y-m-d');
$em15 = (new DateTime('+15 days'))->format('Y-m-d');
$em30 = (new DateTime('+30 days'))->format('Y-m-d');

$novos = [];

// ============ LICENÇAS A VENCER ============
$q = $conexao->query("SELECT id_licenca, id_ativo, nome_licenca, data_validade FROM licencas
                      WHERE data_validade BETWEEN '$hoje' AND '$em30'");
while ($r = $q->fetch_assoc()) {
    $dias = (new DateTime())->diff(new DateTime($r['data_validade']))->days;
    $prior = $dias <= 7 ? 'alta' : ($dias <= 15 ? 'media' : 'baixa');

    $novos[] = [
        'tipo'            => 'licenca',
        'titulo'          => "Licença vence em $dias dia(s)",
        'mensagem'        => "A licença '{$r['nome_licenca']}' do ativo #{$r['id_ativo']} vence em {$r['data_validade']}.",
        'referencia_tipo' => 'licenca',
        'referencia_id'   => (int)$r['id_licenca'],
        'data_evento'     => $r['data_validade'],
        'prioridade'      => $prior,
        'canal_painel'    => 1,
        'canal_email'     => 1
    ];
}

// ============ GARANTIAS A VENCER ============
$q = $conexao->query("SELECT id_garantia, id_ativo, fornecedor, data_fim FROM garantias
                      WHERE data_fim BETWEEN '$hoje' AND '$em30'");
while ($r = $q->fetch_assoc()) {
    $dias = (new DateTime())->diff(new DateTime($r['data_fim']))->days;
    $prior = $dias <= 7 ? 'alta' : ($dias <= 15 ? 'media' : 'baixa');

    $novos[] = [
        'tipo'            => 'garantia',
        'titulo'          => "Garantia termina em $dias dia(s)",
        'mensagem'        => "Garantia do ativo #{$r['id_ativo']} com '{$r['fornecedor']}' encerra em {$r['data_fim']}.",
        'referencia_tipo' => 'garantia',
        'referencia_id'   => (int)$r['id_garantia'],
        'data_evento'     => $r['data_fim'],
        'prioridade'      => $prior,
        'canal_painel'    => 1,
        'canal_email'     => 1
    ];
}

// ============ DEVOLUÇÕES PENDENTES ============
$q = $conexao->query("SELECT id_devolucao, id_ativo, responsavel, data_prevista, status
                      FROM devolucoes WHERE status='pendente' AND data_prevista <= '$em15'");
while ($r = $q->fetch_assoc()) {
    $dias = (new DateTime())->diff(new DateTime($r['data_prevista']))->days;
    $prior = (new DateTime($r['data_prevista']) < new DateTime()) ? 'critica' : 'alta';

    $novos[] = [
        'tipo'            => 'devolucao',
        'titulo'          => "Devolução prevista em {$r['data_prevista']}",
        'mensagem'        => "Ativo #{$r['id_ativo']} com {$r['responsavel']} deve ser devolvido até {$r['data_prevista']}.",
        'referencia_tipo' => 'devolucao',
        'referencia_id'   => (int)$r['id_devolucao'],
        'data_evento'     => $r['data_prevista'],
        'prioridade'      => $prior,
        'canal_painel'    => 1,
        'canal_email'     => 1
    ];
}

// ============ MANUTENÇÕES PROGRAMADAS ============
$q = $conexao->query("SELECT id_mp, id_ativo, tipo, proxima_data FROM manutencoes_programadas
                      WHERE proxima_data BETWEEN '$hoje' AND '$em30'");
while ($r = $q->fetch_assoc()) {
    $dias = (new DateTime())->diff(new DateTime($r['proxima_data']))->days;
    $prior = $dias <= 7 ? 'alta' : 'media';

    $novos[] = [
        'tipo'            => 'manutencao',
        'titulo'          => "Manutenção {$r['tipo']} em $dias dia(s)",
        'mensagem'        => "Ativo #{$r['id_ativo']} tem manutenção {$r['tipo']} em {$r['proxima_data']}.",
        'referencia_tipo' => 'manutencao_prog',
        'referencia_id'   => (int)$r['id_mp'],
        'data_evento'     => $r['proxima_data'],
        'prioridade'      => $prior,
        'canal_painel'    => 1,
        'canal_email'     => 1
    ];
}

// ============ CRIA E ENVIA ============
foreach ($novos as $n) {
    $id = cria_alerta($conexao, $n);

    // Envia e-mail (para exemplo, manda para todos admins).
    if ($n['canal_email']) {
        // pegue destinatários do seu sistema
        $destinatarios = $conexao->query("SELECT email_usuario FROM usuarios WHERE tipo_usuario='Admin'");
        $emails = [];
        while ($u = $destinatarios->fetch_assoc()) $emails[] = $u['email_usuario'];

        if ($emails) {
            $ok = enviar_email($emails, $n['titulo'], nl2br($n['mensagem']));
            if ($ok) {
                $conexao->query("UPDATE notificacoes SET status='enviado', enviado_em=NOW() WHERE id_notificacao=$id");
                log_alerta($conexao, $id, 'email_enviado', implode(',', $emails));
            }
        }
    }
}

echo json_encode(['status' => 'ok', 'gerados' => count($novos)]);
