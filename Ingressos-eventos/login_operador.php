
<?php
session_start();

require_once __DIR__ . "/banco/conexao.php";

$erro = "";
$email = "";

// Impede que o navegador guarde a página de login em cache.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Se o operador já estiver autenticado,
// encaminha para o painel.
if (isset($_SESSION["operador_id"])) {

    $verificarSessao = $db->prepare(
        "SELECT idOperador
         FROM operadores
         WHERE idOperador = ?
         AND ativo = 1
         LIMIT 1"
    );

    $verificarSessao->execute([
        $_SESSION["operador_id"]
    ]);

    if ($verificarSessao->fetch(PDO::FETCH_ASSOC)) {

        header("Location: painel_operador.php");
        exit;

    } else {

        unset(
            $_SESSION["operador_id"],
            $_SESSION["operador_nome"],
            $_SESSION["operador_email"]
        );
    }
}

// Processa o formulário.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {

        $erro = "Preencha o e-mail e a senha.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = "Digite um e-mail válido.";

    } else {

        try {

            $sql = $db->prepare(
                "SELECT idOperador, nome, email, senha, ativo
                 FROM operadores
                 WHERE email = ?
                 LIMIT 1"
            );

            $sql->execute([$email]);

            $operador = $sql->fetch(PDO::FETCH_ASSOC);

            if (
                !$operador ||
                !password_verify($senha, $operador["senha"])
            ) {

                $erro = "E-mail ou senha incorretos.";

            } elseif ((int)$operador["ativo"] !== 1) {

                $erro = "Seu cadastro ainda não foi autorizado pelo administrador.";

            } else {

                // Protege a sessão após o login.
                session_regenerate_id(true);

                $_SESSION["operador_id"] =
                    (int)$operador["idOperador"];

                $_SESSION["operador_nome"] =
                    $operador["nome"];

                $_SESSION["operador_email"] =
                    $operador["email"];

                header("Location: painel_operador.php");
                exit;
            }

        } catch (PDOException $e) {

            error_log(
                "Erro no login do operador: " .
                $e->getMessage()
            );

            $erro = "Não foi possível entrar agora. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login do Operador - Acesso Livre</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(
                135deg,
                #F9FCFF,
                #EAF6FF,
                #F5FBFF
            );
            color: #334E68;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 40px;
            border-radius: 22px;
            box-shadow: 0 10px 35px rgba(64, 124, 170, 0.15);
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .icone {
            width: 75px;
            height: 75px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #EAF7FF;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 36px;
        }

        .logo h1 {
            margin: 0;
            font-size: 34px;
            color: #195A7A;
        }

        .logo p {
            margin-top: 8px;
            font-size: 17px;
            color: #7A92A5;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitulo {
            text-align: center;
            font-size: 16px;
            line-height: 1.5;
            color: #7A92A5;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 17px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #D9EAF5;
            border-radius: 10px;
            background: #F8FBFD;
            color: #334E68;
            font-size: 17px;
            outline: none;
        }

        input:focus {
            border-color: #8CCCF6;
            background: white;
        }

        .botao {
            width: 100%;
            padding: 16px;
            margin-top: 8px;
            border: none;
            border-radius: 12px;
            background: #8CCCF6;
            color: #173042;
            font-size: 19px;
            font-weight: bold;
            cursor: pointer;
        }

        .botao:hover {
            background: #73BEEB;
        }

        .erro {
            background: #FFE8E8;
            border: 1px solid #E29A9A;
            color: #8A2020;
            padding: 13px;
            border-radius: 10px;
            margin-bottom: 18px;
            text-align: center;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .links {
            margin-top: 25px;
            text-align: center;
            line-height: 2;
        }

        .links a {
            color: #195A7A;
            font-weight: bold;
            text-decoration: none;
        }

        .acessibilidade {
            position: fixed;
            bottom: 15px;
            left: 15px;
            display: flex;
            gap: 8px;
            z-index: 1000;
        }

        .acessibilidade button {
            min-width: 48px;
            height: 48px;
            border: 2px solid #195A7A;
            border-radius: 10px;
            background: white;
            color: #195A7A;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
        }

        .acessibilidade button:hover {
            background: #EAF6FF;
        }

        .acessibilidade button:focus-visible,
        .botao:focus-visible,
        input:focus-visible,
        a:focus-visible {
            outline: 3px solid #195A7A;
            outline-offset: 3px;
        }

        @media (max-width: 550px) {

            .container {
                padding: 28px 22px;
            }

            .logo h1 {
                font-size: 30px;
            }

            h2 {
                font-size: 25px;
            }

            .acessibilidade {
                bottom: 8px;
                left: 8px;
            }
        }

    </style>

</head>

<body>

    <main class="container">

        <div class="logo">

            <div
                class="icone"
                role="img"
                aria-label="Cadeado representando acesso seguro"
            >
                🔐
            </div>

            <h1>Acesso Livre</h1>

            <p>Eventos acessíveis para todos</p>

        </div>

        <h2>Login do Operador</h2>

        <p class="subtitulo">
            Entre com seu e-mail e senha
            para acessar o gerenciamento de eventos.
            O acesso é exclusivo para operadores autorizados.
        </p>

        <?php if ($erro !== ""): ?>

            <div class="erro" role="alert">
                <?= htmlspecialchars(
                    $erro,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="login_operador.php"
        >

            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Digite seu e-mail"
                value="<?= htmlspecialchars(
                    $email,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                autocomplete="username"
                maxlength="255"
                required
            >

            <label for="senha">
                Senha
            </label>

            <input
                type="password"
                id="senha"
                name="senha"
                placeholder="Digite sua senha"
                autocomplete="current-password"
                required
            >

            <button
                type="submit"
                class="botao"
            >
                Entrar no painel
            </button>

        </form>

        <div class="links">

            <a href="cadastro_operador.php">
                Ainda não sou operador — Cadastrar
            </a>

            <div>
                <a href="inicio.php">
                    ← Voltar para o início
                </a>
            </div>

        </div>

    </main>

    <!-- BOTÕES DE ACESSIBILIDADE -->

    <div
        class="acessibilidade"
        role="group"
        aria-label="Ferramentas de acessibilidade"
    >

        <button
            type="button"
            onclick="aumentarFonte()"
            aria-label="Aumentar tamanho da fonte"
            title="Aumentar fonte"
        >
            A+
        </button>

        <button
            type="button"
            onclick="diminuirFonte()"
            aria-label="Diminuir tamanho da fonte"
            title="Diminuir fonte"
        >
            A−
        </button>

        <button
            type="button"
            onclick="lerPagina()"
            aria-label="Iniciar ou parar audiodescrição"
            title="Ouvir descrição da página"
        >
            🔊
        </button>

    </div>

    <!-- VLibras -->

    <div vw class="enabled">

        <div vw-access-button class="active"></div>

        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>

    </div>

    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>

    <script>

        // INICIALIZA O VLIBRAS

        if (window.VLibras) {

            new window.VLibras.Widget(
                "https://vlibras.gov.br/app"
            );
        }

        // CONTROLE DO TAMANHO DA FONTE

        let tamanhoFonte = 16;

        function ajustarFonte() {

            document.querySelectorAll(
                "main h1, main h2, main p, main label, " +
                "main input, main button, main a, main .erro"
            ).forEach(function(elemento) {

                if (!elemento.dataset.fonteOriginal) {

                    const tamanhoAtual = parseFloat(
                        window.getComputedStyle(elemento).fontSize
                    );

                    elemento.dataset.fonteOriginal = tamanhoAtual;
                }

                const original = parseFloat(
                    elemento.dataset.fonteOriginal
                );

                const novoTamanho = Math.max(
                    12,
                    original + (tamanhoFonte - 16)
                );

                elemento.style.fontSize = novoTamanho + "px";
            });
        }

        function aumentarFonte() {

            tamanhoFonte = Math.min(
                26,
                tamanhoFonte + 2
            );

            ajustarFonte();
        }

        function diminuirFonte() {

            tamanhoFonte = Math.max(
                14,
                tamanhoFonte - 2
            );

            ajustarFonte();
        }

        // AUDIODESCRIÇÃO / LEITURA POR VOZ

        function lerPagina() {

            if (!("speechSynthesis" in window)) {

                alert(
                    "A leitura por voz não está disponível neste navegador."
                );

                return;
            }

            if (window.speechSynthesis.speaking) {

                window.speechSynthesis.cancel();
                return;
            }

            const mensagem = document.querySelector(".erro");

            let texto = `
                Acesso Livre.
                Eventos acessíveis para todos.

                Você está na página de login do operador.

                A página apresenta um formulário centralizado
                em um cartão branco, com fundo azul claro.

                No topo existe um ícone de cadeado,
                representando acesso seguro.

                Para entrar, preencha dois campos.

                Primeiro campo: E-mail.
                Digite o endereço de e-mail
                utilizado no cadastro de operador.

                Segundo campo: Senha.
                Digite sua senha cadastrada.

                Depois, pressione o botão Entrar no painel.

                Apenas operadores autorizados
                podem acessar o gerenciamento de eventos.

                Abaixo do formulário existe um link
                para solicitar cadastro como operador
                e outro para voltar ao início do site.

                No canto inferior esquerdo estão
                os botões de acessibilidade:
                aumentar fonte, diminuir fonte
                e ouvir esta descrição.

                O site também oferece o VLibras
                para tradução em Língua Brasileira de Sinais.
            `;

            if (mensagem) {

                texto += " Mensagem do sistema: " +
                    mensagem.innerText;
            }

            window.speechSynthesis.cancel();

            const fala = new SpeechSynthesisUtterance(texto);

            fala.lang = "pt-BR";
            fala.rate = 0.9;
            fala.pitch = 1;
            fala.volume = 1;

            window.speechSynthesis.speak(fala);
        }

        // INTERROMPE A LEITURA AO SAIR DA PÁGINA

        window.addEventListener("pagehide", function() {

            if ("speechSynthesis" in window) {

                window.speechSynthesis.cancel();
            }
        });

    </script>

</body>
</html>
