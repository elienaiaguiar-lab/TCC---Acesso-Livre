
<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once __DIR__ . "/banco/conexao.php";

// Somente operadores autenticados podem entrar.
if (!isset($_SESSION["operador_id"])) {
    header("Location: login_operador.php");
    exit;
}

try {

    // Confirma no banco se o operador continua autorizado.
    $verificar = $db->prepare(
        "SELECT idOperador, nome, email, ativo
         FROM operadores
         WHERE idOperador = ?
         LIMIT 1"
    );

    $verificar->execute([
        $_SESSION["operador_id"]
    ]);

    $operador = $verificar->fetch(PDO::FETCH_ASSOC);

    if (!$operador || (int)$operador["ativo"] !== 1) {

        unset(
            $_SESSION["operador_id"],
            $_SESSION["operador_nome"],
            $_SESSION["operador_email"]
        );

        header("Location: login_operador.php");
        exit;
    }

} catch (PDOException $e) {

    error_log(
        "Erro ao verificar operador: " .
        $e->getMessage()
    );

    http_response_code(503);
    exit("Não foi possível carregar o painel no momento.");
}

$nomeOperador = $operador["nome"];
$emailOperador = $operador["email"];

// Função para mostrar textos com segurança.
function escapar($texto) {
    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        "UTF-8"
    );
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

    <title>Painel do Operador - Acesso Livre</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #F4FAFF;
            color: #334E68;
        }

        .cabecalho {
            background: #8CCCF6;
            padding: 22px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 3px 12px rgba(64, 124, 170, 0.12);
        }

        .marca h1 {
            margin: 0;
            color: #195A7A;
            font-size: 30px;
        }

        .marca p {
            margin: 6px 0 0;
            color: #334E68;
            font-size: 15px;
        }

        .navegacao {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .navegacao a {
            text-decoration: none;
            background: white;
            color: #195A7A;
            padding: 12px 17px;
            border-radius: 10px;
            font-weight: bold;
        }

        .navegacao a:hover {
            background: #EAF6FF;
        }

        .conteudo {
            width: 90%;
            max-width: 1150px;
            margin: 35px auto 100px;
        }

        .boas-vindas {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 5px 20px rgba(64, 124, 170, 0.08);
            margin-bottom: 25px;
        }

        .boas-vindas h2 {
            margin-top: 0;
            font-size: 28px;
            color: #195A7A;
        }

        .boas-vindas p {
            line-height: 1.7;
            font-size: 17px;
        }

        .identificacao {
            display: inline-block;
            background: #EAF6FF;
            color: #195A7A;
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 10px;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .secao-titulo {
            font-size: 25px;
            color: #195A7A;
            margin: 30px 0 18px;
        }

        .grade {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(250px, 1fr)
            );
            gap: 20px;
        }

        .cartao {
            background: white;
            padding: 28px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(64, 124, 170, 0.08);
        }

        .cartao .icone {
            font-size: 38px;
            margin-bottom: 15px;
        }

        .cartao h3 {
            color: #195A7A;
            margin: 0 0 12px;
            font-size: 21px;
        }

        .cartao p {
            color: #607D92;
            font-size: 16px;
            line-height: 1.6;
            min-height: 75px;
        }

        .botao {
            display: inline-block;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: none;
            background: #8CCCF6;
            color: #173042;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
        }

        .botao.desativado {
            background: #E3EAF0;
            color: #657B8B;
            cursor: not-allowed;
        }

        .aviso {
            background: #EAF6FF;
            border-left: 5px solid #8CCCF6;
            padding: 20px;
            border-radius: 10px;
            margin-top: 25px;
            line-height: 1.7;
        }

        .rodape {
            text-align: center;
            background: #D9EFFD;
            padding: 25px;
            color: #195A7A;
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

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #195A7A;
            outline-offset: 3px;
        }

        @media (max-width: 600px) {

            .cabecalho {
                padding: 20px;
            }

            .conteudo {
                width: 94%;
            }

            .boas-vindas {
                padding: 22px;
            }

            .boas-vindas h2 {
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

    <header class="cabecalho">

        <div class="marca">
            <h1>Acesso Livre</h1>
            <p>Painel de gerenciamento de eventos</p>
        </div>

        <nav class="navegacao" aria-label="Navegação principal">

            <a href="inicio.php">
                Início
            </a>

            <a href="home.php">
                Ver site
            </a>

        </nav>

    </header>

    <main class="conteudo">

        <section class="boas-vindas">

            <h2>
                Bem-vindo(a),
                <?= escapar($nomeOperador) ?>!
            </h2>

            <p>
                Você está no painel de operadores do
                <strong>Acesso Livre</strong>.
                Aqui será possível gerenciar os eventos
                da plataforma.
            </p>

            <div class="identificacao">

                <strong>Operador autorizado</strong>

                <div>
                    Nome:
                    <?= escapar($nomeOperador) ?>
                </div>

                <div>
                    E-mail:
                    <?= escapar($emailOperador) ?>
                </div>

            </div>

        </section>

        <section aria-labelledby="titulo-gerenciamento">

            <h2
                class="secao-titulo"
                id="titulo-gerenciamento"
            >
                Gerenciamento de Eventos
            </h2>

            <div class="grade">

                <!-- CADASTRAR EVENTOS -->

                <article class="cartao">

                    <div
                        class="icone"
                        role="img"
                        aria-label="Calendário"
                    >
                        📅
                    </div>

                    <h3>Cadastrar Evento</h3>

                    <p>
                        Adicione novos eventos à plataforma,
                        incluindo nome, data, local e descrição.
                    </p>

                    <button
                        type="button"
                        class="botao desativado"
                        disabled
                    >
                        Em desenvolvimento
                    </button>

                </article>

                <!-- VISUALIZAR EVENTOS -->

                <article class="cartao">

                    <div
                        class="icone"
                        role="img"
                        aria-label="Lista"
                    >
                        📋
                    </div>

                    <h3>Visualizar Eventos</h3>

                    <p>
                        Consulte os eventos cadastrados
                        e confira as informações disponíveis.
                    </p>

                    <button
                        type="button"
                        class="botao desativado"
                        disabled
                    >
                        Em desenvolvimento
                    </button>

                </article>

                <!-- EDITAR EVENTOS -->

                <article class="cartao">

                    <div
                        class="icone"
                        role="img"
                        aria-label="Lápis"
                    >
                        ✏️
                    </div>

                    <h3>Editar Evento</h3>

                    <p>
                        Atualize as informações dos eventos,
                        como data, horário, local e descrição.
                    </p>

                    <button
                        type="button"
                        class="botao desativado"
                        disabled
                    >
                        Em desenvolvimento
                    </button>

                </article>

                <!-- EXCLUIR EVENTOS -->

                <article class="cartao">

                    <div
                        class="icone"
                        role="img"
                        aria-label="Lixeira"
                    >
                        🗑️
                    </div>

                    <h3>Excluir Evento</h3>

                    <p>
                        Remova eventos quando necessário,
                        respeitando as permissões de acesso.
                    </p>

                    <button
                        type="button"
                        class="botao desativado"
                        disabled
                    >
                        Em desenvolvimento
                    </button>

                </article>

            </div>

        </section>

        <div class="aviso" role="status">

            <strong>Informação:</strong>

            Este painel já verifica se o operador
            está autorizado no banco de dados.
            As funções de cadastro, edição e exclusão
            serão conectadas nas próximas etapas.

        </div>

    </main>

    <footer class="rodape">

        Acesso Livre — Plataforma de eventos acessíveis

    </footer>

    <!-- FERRAMENTAS DE ACESSIBILIDADE -->

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
                "main h2, main h3, main p, " +
                "main strong, main .identificacao, " +
                "main button, main .aviso, " +
                "header a, footer"
            ).forEach(function(elemento) {

                if (!elemento.dataset.fonteOriginal) {

                    elemento.dataset.fonteOriginal =
                        parseFloat(
                            window.getComputedStyle(elemento).fontSize
                        );
                }

                const original = parseFloat(
                    elemento.dataset.fonteOriginal
                );

                elemento.style.fontSize =
                    Math.max(
                        12,
                        original + (tamanhoFonte - 16)
                    ) + "px";
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

            const nomeOperador = <?=
                json_encode(
                    $nomeOperador,
                    JSON_HEX_TAG |
                    JSON_HEX_AMP |
                    JSON_HEX_APOS |
                    JSON_HEX_QUOT
                )
            ?>;

            const texto = `
                Acesso Livre.
                Painel de gerenciamento de eventos.

                Bem-vindo ao painel do operador
                ${nomeOperador}.

                A página apresenta um cabeçalho azul claro
                com o nome Acesso Livre
                e links para o início e para o site.

                Logo abaixo existe uma área de boas-vindas
                com a identificação do operador autorizado.

                A seção principal apresenta quatro cartões.

                Primeiro cartão: Cadastrar Evento.
                Essa função permitirá adicionar
                novos eventos à plataforma.

                Segundo cartão: Visualizar Eventos.
                Essa função permitirá consultar
                os eventos cadastrados.

                Terceiro cartão: Editar Evento.
                Essa função permitirá atualizar
                informações dos eventos.

                Quarto cartão: Excluir Evento.
                Essa função permitirá remover eventos.

                Neste momento, essas quatro funções
                ainda estão em desenvolvimento.

                No canto inferior esquerdo estão
                os botões para aumentar a fonte,
                diminuir a fonte e ouvir esta descrição.

                O site também oferece o VLibras
                para tradução em Língua Brasileira de Sinais.
            `;

            window.speechSynthesis.cancel();

            const fala =
                new SpeechSynthesisUtterance(texto);

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
