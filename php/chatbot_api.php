<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$q = strtolower(trim($payload['message'] ?? ''));

// mapa de intenções -> resposta + ações
$intents = [
    // Cadastro
    '/(cadastrar|novo).*(colaborador|usuario)/' => [
        'reply' => 'Para cadastrar um novo colaborador, preencha o formulário com nome, e-mail, tipo (Admin/Colaborador) e defina uma senha.',
        'actions' => [
            ['label' => 'Abrir Cadastro de Colaborador', 'url' => 'cadastrar_colaborador.php'],
            ['label' => 'Ver Colaboradores', 'url' => 'colaboradores.php']
        ]
    ],
    '/(cadastrar|novo).*(ativo)/' => [
        'reply' => 'Para cadastrar um ativo, informe nome, categoria, valor, data de aquisição, nº de série, status e localização.',
        'actions' => [
            ['label' => 'Cadastrar Ativo', 'url' => 'cadastrar_ativos.php'],
            ['label' => 'Ver Ativos', 'url' => 'ativos.php']
        ]
    ],

    // Manutenções
    '/(registrar|cadastrar).*(manuten)/' => [
        'reply' => 'Para registrar uma manutenção, selecione o ativo, defina tipo (Preventiva/Corretiva), data, responsável, custo e descrição.',
        'actions' => [
            ['label' => 'Registrar Manutenção', 'url' => 'cadastrar_manutencao.php'],
            ['label' => 'Histórico de Manutenções', 'url' => 'manutencoes.php']
        ]
    ],
    '/(ver|listar).*(manuten)/' => [
        'reply' => 'Você pode visualizar o histórico em formato de cards com responsável, custo e descrição.',
        'actions' => [
            ['label' => 'Ver Manutenções', 'url' => 'manutencoes.php']
        ]
    ],

    // Dashboard & Relatórios
    '/(dashboard|relat(ó|o)rio|indicador|gr(á|a)fico)/' => [
        'reply' => 'O dashboard exibe ativos por categoria, status, manutenções por tipo e custos totais. Também há exportação em PDF/Excel.',
        'actions' => [
            ['label' => 'Abrir Dashboard', 'url' => 'dashboard.php']
        ]
    ],

    // IA: Previsão e Insights
    '/(previs(ã|a)o|falha|risco|insight|an(á|a)lise inteligente|ia)/' => [
        'reply' => 'A seção de IA calcula risco de falha (30 dias) por ativo e traz insights automáticos de custos e recomendações.',
        'actions' => [
            ['label' => 'Abrir Dashboard (IA)', 'url' => 'dashboard.php#ia']
        ]
    ],

    // Perfil
    '/(perfil|minhas? conta|meu usu(á|a)rio|editar.*(meu|pr(ó|o)prio))/' => [
        'reply' => 'Para editar suas informações pessoais (nome, e-mail e senha), acesse “Meu Perfil”.',
        'actions' => [
            ['label' => 'Abrir Meu Perfil', 'url' => 'perfil.php']
        ]
    ],

    // Ajuda genérica
    '/(ajuda|help|como|onde)/' => [
        'reply' => 'Posso ajudar com: cadastro de colaboradores/ativos, registro de manutenções, dashboard, relatórios e IA.',
        'actions' => [
            ['label' => 'Ver Colaboradores', 'url' => 'colaboradores.php'],
            ['label' => 'Ver Ativos', 'url' => 'ativos.php'],
            ['label' => 'Dashboard', 'url' => 'dashboard.php']
        ]
    ],
];

$reply = 'Não entendi totalmente. Você quer ajuda com cadastro, manutenções, dashboard ou IA?';
$actions = [
    ['label' => 'Cadastrar Colaborador', 'url' => 'cadastrar_colaborador.php'],
    ['label' => 'Registrar Manutenção', 'url' => 'cadastrar_manutencao.php'],
    ['label' => 'Abrir Dashboard', 'url' => 'dashboard.php'],
];

foreach ($intents as $pattern => $resp) {
    if (preg_match($pattern, $q)) {
        $reply = $resp['reply'];
        $actions = $resp['actions'] ?? [];
        break;
    }
}

echo json_encode(['reply' => $reply, 'actions' => $actions], JSON_UNESCAPED_UNICODE);
