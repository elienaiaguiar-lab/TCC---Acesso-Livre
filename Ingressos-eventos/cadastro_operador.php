
<?php
session_start();

require_once __DIR__ . "/banco/conexao.php";

$erro = "";
$sucesso = "";

$nome = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";

    if (
        $nome === "" ||
        $email === "" ||
        $senha === "" ||
        $confirmar === ""
    ) {

        $erro = "Preencha todos os campos.";

    } elseif (strlen($nome) > 150) {

        $erro = "O nome deve ter no máximo 150 caracteres.";

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 255
    ) {

        $erro = "Digite um e-mail válido.";

    } elseif (strlen($senha) < 8) {

        $erro = "A senha deve ter pelo menos 8 caracteres.";

    } elseif ($senha !== $confirmar) {

        $erro = "As senhas não são iguais.";

    } else {

        try {

            // Verifica se o e-mail já está cadastrado.
            $verificar = $db->prepare(
                "SELECT idOperador
                 FROM operadores
                 WHERE email = ?
                 LIMIT 1"
            );

            $verificar->execute([$email]);

            if ($verificar->fetch(PDO::FETCH_ASSOC)) {

                $erro = "Este e-mail já está cadastrado.";

            } else {

                // Criptografa a senha.
                $senhaHash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                // O operador começa inativo.
                // Precisa de autorização para entrar no painel.
                $cadastrar = $db->prepare(
                    "INSERT INTO operadores
                     (nome, email, senha, ativo)
                     VALUES (?, ?, ?, 0)"
                );

                $cadastrar->execute([
                    $nome,
                    $email,
                    $senhaHash
                ]);

                $sucesso = "Cadastro realizado com sucesso! Aguarde a autorização do administrador para acessar o painel.";

                $nome = "";
                $email = "";
            }

        } catch (PDOException $e) {

            error_log(
                "Erro ao cadastrar operador: " .
                $e->getMessage()
            );

            $erro = "Não foi possível realizar o cadastro. Tente novamente.";
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

    <title>Cadastro de Operador - Acesso Livre</title>

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
            max-width: 520px;
            background: white;
            padding: 38px;
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
            font-size: 27px;
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
            font-size: 16px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 14px;
            margin-bottom: 18px;
            border: 2px solid #D9EAF5;
            border-radius: 10px;
            background: #F8FBFD;
            color: #334E68;
            font-size: 16px;
            outline: none;
        }

        input:focus {
            border-color: #8CCCF6;
            background: white;
        }

        .botao {
            width: 100%;
            padding: 15px;
            margin-top: 10px;
            border: none;
            border-radius: 12px;
            background: #8CCCF6;
            color: #173042;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }

        .botao:hover {
            background: #73BEEB;
        }

        .erro,
        .sucesso {
            padding: 13px;
            border-radius: 10px;
            margin-bottom: 18px;
            text-align: center;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .erro {
            background: #FFE8E8;
            border: 1px solid #E29A9A;
            color: #8A2020;
        }

        .sucesso {
            background: #E7FAED;
            border: 1px solid #79B98A;
            color: #216B35;
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
                padding: 25px 20px;
            }

            .logo h1 {
                font-size: 29px;
            }

            h2 {
                font-size: 24px;
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

            <div class="icone" role="img" aria-label="Calendário de eventos">
                🗓️
            </div>

            <h1>Acesso Livre</h1>

            <p>Eventos acessíveis para todos</p>

        </div>

        <h2>Cadastro de Operador</h2>

        <p class="subtitulo">
            Cadastre-se para solicitar acesso
            ao gerenciamento de eventos.
            Após o cadastro, será necessário
            aguardar a autorização do administrador.
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

        <?php if ($sucesso !== ""): ?>

            <div class="sucesso" role="status">
                <?= htmlspecialchars(
                    $sucesso,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="cadastro_operador.php"
        >

            <label for="nome">
                Nome completo
            </label>

            <input
                type="text"
                id="nome"
                name="nome"
                placeholder="Digite seu nome completo"
                value="<?= htmlspecialchars(
                    $nome,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                autocomplete="name"
                maxlength="150"
                required
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
                autocomplete="email"
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
                placeholder="Crie uma senha"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="confirmar">
                Confirmar senha
            </label>

            <input
                type="password"
                id="confirmar"
                name="confirmar"
                placeholder="Digite a senha novamente"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <button
                type="submit"
                class="botao"
            >
                Solicitar cadastro
            </button>

        </form>

        <div class="links">

            <a href="login_operador.php">
                Já sou operador — Entrar
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
            aria-label="Iniciar ou parar leitura por voz"
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

        // AUMENTAR E DIMINUIR FONTE

        let tamanhoFonte = 16;

        function ajustarFonte() {

            document.querySelectorAll(
                "main h1, main h2, main p, main label, " +
                "main input, main button, main a, " +
                "main .erro, main .sucesso"
            ).forEach(function(elemento) {

                if (!elemento.dataset.fonteOriginal) {

                    const tamanhoAtual =
                        parseFloat(
                            window.getComputedStyle(elemento).fontSize
                        );

                    elemento.dataset.fonteOriginal = tamanhoAtual;
                }

                const original =
                    parseFloat(elemento.dataset.fonteOriginal);

                const novoTamanho =
                    Math.max(
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

        // LEITURA POR VOZ / DESCRIÇÃO DA PÁGINA

        function lerPagina() {

            if (!("speechSynthesis" in window)) {

                alert(
                    "A leitura por voz não está disponível neste navegador."
                );

                return;
            }

            // Se já estiver lendo, interrompe.
            if (window.speechSynthesis.speaking) {

                window.speechSynthesis.cancel();

                return;
            }

            const mensagem =
                document.querySelector(".erro, .sucesso");

            let texto = `
                Acesso Livre.
                Eventos acessíveis para todos.

                Você está na página de cadastro de operador.

                Esta página possui um formulário centralizado
                em um cartão branco, com fundo azul claro.

                O formulário permite solicitar acesso
                ao gerenciamento de eventos.

                Primeiro campo: Nome completo.
                Digite seu nome.

                Segundo campo: E-mail.
                Informe um endereço de e-mail válido.

                Terceiro campo: Senha.
                Crie uma senha com pelo menos oito caracteres.

                Quarto campo: Confirmar senha.
                Digite novamente a mesma senha.

                Depois, pressione o botão Solicitar cadastro.

                Após o cadastro, será necessário aguardar
                a autorização do administrador.

                Abaixo do formulário existem links
                para entrar como operador
                e voltar ao início do site.

                No canto inferior esquerdo estão
                os botões para aumentar a fonte,
                diminuir a fonte e ouvir esta descrição.

                O site também oferece o VLibras
                para tradução em Língua Brasileira de Sinais.
            `;

            if (mensagem) {

                texto += " Mensagem do sistema: " +
                    mensagem.innerText;
            }

            window.speechSynthesis.cancel();

            const fala =
                new SpeechSynthesisUtterance(texto);

            fala.lang = "pt-BR";
            fala.rate = 0.9;
            fala.pitch = 1;
            fala.volume = 1;

            window.speechSynthesis.speak(fala);
        }

        // Interrompe a leitura ao sair da página.
        window.addEventListener("pagehide", function() {

            if ("speechSynthesis" in window) {

                window.speechSynthesis.cancel();
            }
        });

    </script>

</body>
</html>
