<?php
include('conexao.php');
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: /index.php');
    exit;
}
$nome = $_SESSION['nome_usuario'];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Youtan Helper (IA)</title>
    <link rel="stylesheet" href="../css/chatbot.css">
    <script src="https://kit.fontawesome.com/8417e3dabe.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include_once('nav.php'); ?>

    <main>
        <div class="chat_wrapper">
            <header class="chat_header">
                <div class="chat_title">
                    <i class="fa-solid fa-robot"></i>
                    <div>
                        <h1>Youtan Helper (IA)</h1>
                        <p>Olá, <?php echo htmlspecialchars($nome); ?>! Como posso ajudar?</p>
                    </div>
                </div>
                <div class="chat_actions">
                    <button id="btnLimpar" title="Limpar conversa"><i class="fa-solid fa-broom"></i></button>
                </div>
            </header>

            <section id="chatBody" class="chat_body">
                <div class="msg bot">
                    <div class="bubble">
                        <p>Sou o assistente virtual do sistema. Você pode perguntar sobre:</p>
                        <ul>
                            <li>Cadastro de colaboradores e ativos</li>
                            <li>Relatórios e Dashboard</li>
                            <li>Manutenções, custos e histórico</li>
                            <li>Previsão de falhas (IA) e insights</li>
                        </ul>
                    </div>
                </div>

                <div class="chips">
                    <button class="chip">Como cadastrar colaborador?</button>
                    <button class="chip">Ver manutenções</button>
                    <button class="chip">Abrir dashboard</button>
                    <button class="chip">Previsão de falhas</button>
                    <button class="chip">Editar meu perfil</button>
                </div>
            </section>

            <footer class="chat_input">
                <form id="formChat">
                    <input type="text" id="txtMsg" placeholder="Digite sua pergunta..." autocomplete="off" required />
                    <button type="submit" id="btnSend" aria-label="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            </footer>
        </div>
    </main>

    <script>
        const bodyEl = document.getElementById('chatBody');
        const form = document.getElementById('formChat');
        const input = document.getElementById('txtMsg');
        const btnLimpar = document.getElementById('btnLimpar');

        // chips (sugestões rápidas)
        document.querySelectorAll('.chip').forEach(c =>
            c.addEventListener('click', () => {
                input.value = c.textContent.trim();
                form.dispatchEvent(new Event('submit', {
                    cancelable: true
                }));
            })
        );

        btnLimpar.addEventListener('click', () => {
            bodyEl.innerHTML = `
        <div class="msg bot">
          <div class="bubble"><p>Conversa limpa. Em que posso ajudar?</p></div>
        </div>
      `;
        });

        function appendMsg(text, from = 'user', actions = []) {
            const wrap = document.createElement('div');
            wrap.className = `msg ${from}`;
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            bubble.innerHTML = `<p>${text}</p>`;
            if (actions && actions.length) {
                const actionsEl = document.createElement('div');
                actionsEl.className = 'actions';
                actions.forEach(a => {
                    const link = document.createElement('a');
                    link.href = a.url;
                    link.textContent = a.label;
                    link.className = 'action_link';
                    actionsEl.appendChild(link);
                });
                bubble.appendChild(actionsEl);
            }
            wrap.appendChild(bubble);
            bodyEl.appendChild(wrap);
            bodyEl.scrollTop = bodyEl.scrollHeight;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = input.value.trim();
            if (!msg) return;

            appendMsg(msg, 'user');
            input.value = '';
            bodyEl.scrollTop = bodyEl.scrollHeight;

            try {
                const res = await fetch('chatbot_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message: msg
                    })
                });
                const data = await res.json();
                appendMsg(data.reply, 'bot', data.actions || []);
            } catch (err) {
                appendMsg('Desculpe, houve um erro ao processar sua mensagem. Tente novamente.', 'bot');
            }
        });
    </script>
</body>

</html>