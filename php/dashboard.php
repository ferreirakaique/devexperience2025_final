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

// ==========================
// CONSULTAS AO BANCO
// ==========================

// Ativos por categoria
$query_cat = $conexao->query("SELECT categoria_ativo, COUNT(*) as total FROM ativos GROUP BY categoria_ativo");

// Ativos por status
$query_status = $conexao->query("SELECT status_ativo, COUNT(*) as total FROM ativos GROUP BY status_ativo");

// Custo total de manutenção
$query_custo = $conexao->query("SELECT SUM(custo_manutencao) AS total_custo FROM manutencoes");
$total_custo = $query_custo->fetch_assoc()['total_custo'] ?? 0;

// Manutenções por tipo
$query_tipo = $conexao->query("SELECT tipo_manutencao, COUNT(*) as total FROM manutencoes GROUP BY tipo_manutencao");


// ... seu código anterior (sessão, consultas dos gráficos) ...

// ==========================
// IA — PREVISÃO DE FALHAS (30 DIAS) + INSIGHTS
// ==========================
$since180 = date('Y-m-d', strtotime('-180 days'));
$since365 = date('Y-m-d', strtotime('-365 days'));

$sql_ai = $conexao->prepare("SELECT a.id_ativo,
    a.nome_ativo,
    a.categoria_ativo,
    a.data_aquisicao_ativo,
    -- manutencoes últimos 180 dias
    SUM(CASE WHEN m.data_manutencao >= ? THEN 1 ELSE 0 END)               AS manut_180,
    SUM(CASE WHEN m.data_manutencao >= ? AND m.tipo_manutencao='Corretiva' THEN 1 ELSE 0 END) AS corretivas_180,
    -- custo últimos 12 meses
    COALESCE(SUM(CASE WHEN m.data_manutencao >= ? THEN m.custo_manutencao END), 0) AS custo_12m,
    -- última manutenção (qualquer período)
    MAX(m.data_manutencao) AS ultima_manutencao
  FROM ativos a
  LEFT JOIN manutencoes m ON m.id_manutencao = a.id_ativo
  GROUP BY a.id_ativo, a.nome_ativo, a.categoria_ativo, a.data_aquisicao_ativo
");
$sql_ai->bind_param("sss", $since180, $since180, $since365);
$sql_ai->execute();
$res_ai = $sql_ai->get_result();

$ai_riscos = [];           // para gráfico Top Risco
$ai_lista = [];            // para tabela/lista
$insights_categorias = []; // custo por categoria 12m
$alertas_preventivos = []; // IDs de alto risco

while ($r = $res_ai->fetch_assoc()) {
    $id    = (int)$r['id_ativo'];
    $nome  = $r['nome_ativo'] ?? 'Ativo #' . $id;
    $cat   = $r['categoria_ativo'] ?? 'N/D';
    $man180 = (int)$r['manut_180'];
    $cor180 = (int)$r['corretivas_180'];
    $custo12 = (float)$r['custo_12m'];
    $ultima = $r['ultima_manutencao'];

    // idade do ativo e atraso da última manutenção
    $idade_dias = null;
    if (!empty($r['data_aquisicao_ativo'])) {
        $idade_dias = (int)((time() - strtotime($r['data_aquisicao_ativo'])) / 86400);
    }
    $gap_dias = $ultima ? (int)((time() - strtotime($ultima)) / 86400) : 999;

    // normalizações simples
    $freq_norm   = min($man180 / 4, 1);        // 4+ manutenções/180d = alto
    $cor_norm    = min($cor180 / 3, 1);        // 3+ corretivas/180d = alto
    $custo_norm  = min($custo12 / 5000, 1);    // R$ 5k/12m = alto (ajuste se quiser)
    $overdue_norm = min($gap_dias / 180, 1);    // >180d sem manutenção = alto

    // score final (0 a 1) — pesos
    $score = 0.40 * $cor_norm + 0.30 * $freq_norm + 0.20 * $custo_norm + 0.10 * $overdue_norm;

    // motivo resumido
    $motivos = [];
    if ($cor180 >= 2) $motivos[] = "$cor180 corretivas/180d";
    if ($man180 >= 3) $motivos[] = "$man180 manutenções/180d";
    if ($custo12 >= 3000) $motivos[] = "R$ " . number_format($custo12, 2, ',', '.') . "/12m";
    if ($gap_dias >= 120) $motivos[] = "$gap_dias dias sem manutenção";
    $motivo = count($motivos) ? implode(' · ', $motivos) : 'Operação estável';

    $ai_lista[] = [
        'id' => $id,
        'nome' => $nome,
        'categoria' => $cat,
        'score' => $score,
        'motivo' => $motivo,
        'custo12' => $custo12
    ];

    // custo por categoria (só somar para gráficos/insights)
    if (!isset($insights_categorias[$cat])) $insights_categorias[$cat] = 0;
    $insights_categorias[$cat] += $custo12;

    // marcar alertas (score >= 0.70)
    if ($score >= 0.70) $alertas_preventivos[] = ['id' => $id, 'nome' => $nome, 'score' => $score];
}

// ordenar por score desc e pegar Top 5 para gráfico
usort($ai_lista, fn($a, $b) => $b['score'] <=> $a['score']);
$top5 = array_slice($ai_lista, 0, 5);

// preparar strings para JS (labels e dados)
$top_labels = array_map(fn($x) => $x['nome'], $top5);
$top_scores = array_map(fn($x) => round($x['score'] * 100, 1), $top5); // em %

$cat_labels = array_keys($insights_categorias);
$cat_custos = array_map(fn($v) => round($v, 2), array_values($insights_categorias));
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistema de Ativos</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include_once('nav.php'); ?>

    <main>
        <div class="titulo">
            <h1><i class="fa-solid fa-chart-line"></i> Dashboard e Relatórios</h1>
            <p>Visualize os principais indicadores e métricas sobre os ativos e manutenções.</p>
        </div>

        <section class="resumo_cards">
            <div class="card_resumo">
                <i class="fa-solid fa-box"></i>
                <h2>Ativos Cadastrados</h2>
                <p>
                    <?php
                    $count = $conexao->query("SELECT COUNT(*) AS total FROM ativos")->fetch_assoc()['total'];
                    echo $count;
                    ?>
                </p>
            </div>

            <div class="card_resumo">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <h2>Manutenções Registradas</h2>
                <p>
                    <?php
                    $count = $conexao->query("SELECT COUNT(*) AS total FROM manutencoes")->fetch_assoc()['total'];
                    echo $count;
                    ?>
                </p>
            </div>

            <div class="card_resumo">
                <i class="fa-solid fa-money-bill-wave"></i>
                <h2>Custo Total</h2>
                <p>R$ <?php echo number_format($total_custo, 2, ',', '.'); ?></p>
            </div>
        </section>

        <div class="container_categoria">
            <div class="categoria">
                <h3><i class="fa-solid fa-layer-group"></i> Ativos por Categoria</h3>
                <canvas id="graficoCategorias"></canvas>
            </div>
        </div>

        <section class="graficos_container">
            <div class="grafico_box">
                <h3><i class="fa-solid fa-toggle-on"></i> Ativos por Status</h3>
                <canvas id="graficoStatus"></canvas>
            </div>

            <div class="grafico_box">
                <h3><i class="fa-solid fa-wrench"></i> Manutenções por Tipo</h3>
                <canvas id="graficoManutencao"></canvas>
            </div>
        </section>

        <section class="ia_container">
            <div class="ia_header">
                <h2><i class="fa-solid fa-robot"></i> Análise Inteligente (IA)</h2>
                <p>Previsão de falhas nos próximos 30 dias e insights automáticos gerados a partir do histórico.</p>
            </div>

            <div class="ia_grid">
                <!-- Previsão de Falhas (gráfico) -->
                <div class="ia_card">
                    <div class="ia_card_header">
                        <h3><i class="fa-solid fa-triangle-exclamation"></i> Previsão de Falhas (30 dias)</h3>
                    </div>
                    <canvas id="graficoRisco"></canvas>
                    <p class="ia_hint">Quanto maior a barra, maior a probabilidade de falha (score em %).</p>
                </div>

                <!-- Insights de Custo por Categoria (12m) -->
                <div class="ia_card">
                    <div class="ia_card_header">
                        <h3><i class="fa-solid fa-sack-dollar"></i> Custo de Manutenção por Categoria (12 meses)</h3>
                    </div>
                    <canvas id="graficoCusto12m"></canvas>
                </div>

                <!-- Recomendações/Insights em texto -->
                <div class="ia_card ia_text">
                    <div class="ia_card_header">
                        <h3><i class="fa-solid fa-lightbulb"></i> Insights & Recomendações</h3>
                    </div>
                    <ul class="ia_list">
                        <?php if (!empty($ai_lista)):
                            $alto_custo = $ai_lista;
                            usort($alto_custo, fn($a, $b) => $b['custo12'] <=> $a['custo12']);
                            $mais_caro = $alto_custo[0];
                        ?>
                            <li><strong>Ativo de maior custo (12m):</strong> <?php echo htmlspecialchars($mais_caro['nome']); ?> — R$ <?php echo number_format($mais_caro['custo12'], 2, ',', '.'); ?></li>
                            <li><strong>Maior risco (30d):</strong> <?php echo htmlspecialchars($ai_lista[0]['nome']); ?> — <?php echo round($ai_lista[0]['score'] * 100, 1); ?>%</li>
                            <li><strong>Categorias com mais custo:</strong>
                                <?php
                                arsort($insights_categorias);
                                echo htmlspecialchars(implode(', ', array_slice(array_keys($insights_categorias), 0, 3)));
                                ?>
                            </li>
                            <li><strong>Recomendação:</strong> priorize inspeção nos ativos com risco ≥ 70% e nos 3 maiores centros de custo.</li>
                        <?php else: ?>
                            <li>Sem dados suficientes para gerar insights.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>

        <section class="exportar">
            <button id="btnPDF"><i class="fa-solid fa-file-pdf"></i> Exportar PDF</button>
            <button id="btnExcel"><i class="fa-solid fa-file-excel"></i> Exportar Excel</button>
        </section>

    </main>

    <script>
        // ====== IA: TOP RISCO (BARRAS) ======
        const ctxRisco = document.getElementById('graficoRisco');
        new Chart(ctxRisco, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($top_labels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    label: 'Risco (30 dias) %',
                    data: <?php echo json_encode($top_scores); ?>,
                    backgroundColor: ['#FF6B6B', '#FFD166', '#62C2D9', '#5EE47F', '#A78BFA']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Top 5 Ativos por Risco (30 dias)',
                        color: '#FFFFFF'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            color: '#FFFFFF',
                            callback: v => v + '%'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.15)'
                        },
                        beginAtZero: true,
                        max: 100
                    },
                    x: {
                        ticks: {
                            color: '#FFFFFF'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // ====== IA: CUSTO POR CATEGORIA (12M) ======
        const ctxCusto = document.getElementById('graficoCusto12m');
        new Chart(ctxCusto, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels, JSON_UNESCAPED_UNICODE); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_custos); ?>,
                    backgroundColor: ['#459EB5', '#62C2D9', '#00A8C0', '#5EE47F', '#FFD166', '#A78BFA', '#FF6B6B']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribuição de Custos por Categoria (12m)',
                        color: '#FFFFFF'
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#FFFFFF'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const v = ctx.parsed || 0;
                                return ` R$ ${v.toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
                            }
                        }
                    }
                }
            }
        });

        // ====== ALERTAS PREVENTIVOS (toast) ======
        <?php if (!empty($alertas_preventivos)): ?>
            setTimeout(() => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alerta preventivo',
                    html: `<?php
                            $names = array_map(fn($x) => htmlspecialchars($x['nome']) . ' (' . round($x['score'] * 100, 0) . '%)', $alertas_preventivos);
                            echo 'Risco elevado em: <br><strong>' . implode('</strong><br><strong>', $names) . '</strong>';
                            ?>`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 6000,
                    background: '#004759',
                    color: '#fff'
                });
            }, 600);
        <?php endif; ?>
    </script>


    <script>
        // ------------------ GRAFICO: ATIVOS POR CATEGORIA ------------------
        const ctxCat = document.getElementById('graficoCategorias');
        new Chart(ctxCat, {
            type: 'bar',
            data: {
                labels: [<?php while ($row = $query_cat->fetch_assoc()) echo "'" . $row['categoria_ativo'] . "',"; ?>],
                datasets: [{
                    label: 'Quantidade',
                    data: [<?php
                            $query_cat2 = $conexao->query("SELECT categoria_ativo, COUNT(*) as total FROM ativos GROUP BY categoria_ativo");
                            while ($row = $query_cat2->fetch_assoc()) echo $row['total'] . ",";
                            ?>],
                    backgroundColor: ['#459EB5', '#62C2D9', '#007B8F', '#00A8C0']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Ativos por Categoria',
                        color: '#FFFFFF' // <- título branco
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#FFFFFF'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.15)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#FFFFFF'
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.15)'
                        }
                    }
                }
            }
        });

        // ------------------ GRAFICO: ATIVOS POR STATUS ------------------
        const ctxStatus = document.getElementById('graficoStatus');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: [<?php while ($row = $query_status->fetch_assoc()) echo "'" . ucfirst($row['status_ativo']) . "',"; ?>],
                datasets: [{
                    data: [<?php
                            $query_status2 = $conexao->query("SELECT status_ativo, COUNT(*) as total FROM ativos GROUP BY status_ativo");
                            while ($row = $query_status2->fetch_assoc()) echo $row['total'] . ",";
                            ?>],
                    backgroundColor: ['#5EE47F', '#FF6B6B']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Ativos por Status',
                        color: '#FFFFFF' // <- título branco
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#FFFFFF'
                        }
                    },
                    tooltip: {
                        bodyColor: '#FFFFFF',
                        titleColor: '#FFFFFF'
                    }
                }
            }
        });

        // ------------------ GRAFICO: MANUTENÇÕES POR TIPO ------------------
        const ctxTipo = document.getElementById('graficoManutencao');
        new Chart(ctxTipo, {
            type: 'pie',
            data: {
                labels: [<?php while ($row = $query_tipo->fetch_assoc()) echo "'" . $row['tipo_manutencao'] . "',"; ?>],
                datasets: [{
                    data: [<?php
                            $query_tipo2 = $conexao->query("SELECT tipo_manutencao, COUNT(*) as total FROM manutencoes GROUP BY tipo_manutencao");
                            while ($row = $query_tipo2->fetch_assoc()) echo $row['total'] . ",";
                            ?>],
                    backgroundColor: ['#FFD166', '#06D6A0']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Manutenções por Tipo',
                        color: '#FFFFFF' // <- título branco
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#FFFFFF'
                        }
                    },
                    tooltip: {
                        bodyColor: '#FFFFFF',
                        titleColor: '#FFFFFF'
                    }
                }
            }
        });

        // ------------------ EXPORTAÇÃO SIMPLIFICADA ------------------
        document.getElementById("btnPDF").addEventListener("click", () => window.print());
        document.getElementById("btnExcel").addEventListener("click", () => {
            alert("Exportação para Excel em desenvolvimento...");
        });
    </script>

</body>

</html>