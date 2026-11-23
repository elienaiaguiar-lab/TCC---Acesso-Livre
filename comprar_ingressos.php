
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Conexão com o banco
if (file_exists(__DIR__ . "/banco/conexao.php")) {
    require_once __DIR__ . "/banco/conexao.php";
} else {
    require_once __DIR__ . "/conexao.php";
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Receber ID do evento
$id = $_GET["id"] ?? null;

if (!$id || !is_numeric($id)) {
    header("Location: home.php");
    exit;
}

// Buscar evento no banco
$sql = $db->prepare("
    SELECT
        idUsuario AS id,
        nome,
        descricao,
        local,
        dataEvento AS data_evento,
        horario,
        preco,
        acessibilidade,
        audioDescricao AS audiodescricao,
        mapaSensorial AS mapa_sensorial
    FROM eventos
    WHERE idUsuario = ?
    LIMIT 1
");

$sql->execute([(int)$id]);
$evento = $sql->fetch(PDO::FETCH_ASSOC);

if (!$evento) {
    header("Location: home.php");
    exit;
}

$idEvento = (int)$evento["id"];

// Imagens dos eventos
$imagens = [
    1 => "luan1.png",
    2 => "jonathan.png",
    3 => "maiara.png"
];

$nomeArquivo = $imagens[$idEvento] ?? "LOGO.png";
$imagemEvento = "/Ingressos-eventos/" . $nomeArquivo;

if (!file_exists(__DIR__ . "/" . $nomeArquivo)) {
    $imagemEvento = "/Ingressos-eventos/LOGO.png";
}

// Buscar cadeiras já ocupadas
$cadeirasOcupadas = [];

try {
    $consultaCadeiras = $db->prepare("
        SELECT cadeiraEscolhida
        FROM ingressos
        WHERE idEvento = ?
        AND cadeiraEscolhida IS NOT NULL
    ");

    $consultaCadeiras->execute([$idEvento]);
    $cadeirasOcupadas = $consultaCadeiras->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $cadeirasOcupadas = [];
}

$mensagem = "";
$compraRealizada = false;
$areaEscolhida = "";
$cadeiraEscolhida = "";

// Processar compra
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $areaEscolhida = $_POST["areaEscolhida"] ?? "";
    $cadeiraEscolhida = $_POST["cadeiraEscolhida"] ?? "";

    $areasPermitidas = [
        "Espaço tranquilo",
        "Área comum",
        "Espaço reservado"
    ];

    if (!in_array($areaEscolhida, $areasPermitidas, true)) {
        $mensagem = "Selecione uma área válida.";
    } elseif (
        $areaEscolhida === "Espaço reservado" &&
        !preg_match('/^[A-E][1-6]$/', $cadeiraEscolhida)
    ) {
        $mensagem = "Selecione uma cadeira no mapa.";
    } else {

        try {

            // Conferir se a cadeira ainda está disponível
            if ($areaEscolhida === "Espaço reservado") {

                $verificarCadeira = $db->prepare("
                    SELECT COUNT(*)
                    FROM ingressos
                    WHERE idEvento = ?
                    AND cadeiraEscolhida = ?
                ");

                $verificarCadeira->execute([
                    $idEvento,
                    $cadeiraEscolhida
                ]);

                if ($verificarCadeira->fetchColumn() > 0) {
                    throw new Exception(
                        "Essa cadeira já foi reservada. Escolha outra."
                    );
                }

            } else {
                $cadeiraEscolhida = null;
            }

            // Gerar código do ingresso
            $codigo = strtoupper(bin2hex(random_bytes(4)));

            // Registrar ingresso
            $inserir = $db->prepare("
                INSERT INTO ingressos (
                    idUsuario,
                    idEvento,
                    codigo,
                    compradoEm,
                    areaEscolhida,
                    cadeiraEscolhida
                ) VALUES (?, ?, ?, NOW(), ?, ?)
            ");

            $inserir->execute([
                $_SESSION["usuario_id"],
                $idEvento,
                $codigo,
                $areaEscolhida,
                $cadeiraEscolhida
            ]);

            $compraRealizada = true;

        } catch (Exception $e) {
            $mensagem = $e->getMessage();

        } catch (PDOException $e) {
            $mensagem = "Não foi possível concluir a reserva. Verifique se a cadeira continua disponível.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($evento["nome"]) ?> - Acesso Livre</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #F9FCFF, #EAF6FF, #F5FBFF);
    color: #334E68;
    line-height: 1.5;
}

header {
    background: white;
    border-bottom: 2px solid #D9EAF5;
    padding: 18px 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo {
    color: #195A7A;
    font-size: 30px;
    font-weight: bold;
    text-decoration: none;
}

nav {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

nav a {
    color: #334E68;
    text-decoration: none;
    font-size: 17px;
    font-weight: bold;
    padding: 10px 14px;
    border-radius: 10px;
}

nav a:hover {
    background: #EAF6FF;
    color: #195A7A;
}

.sair {
    background: #8CCCF6;
    color: #173042 !important;
}

.sair:hover {
    background: #73BEEB !important;
}

main {
    max-width: 1100px;
    margin: auto;
    padding: 45px 5%;
}

.voltar {
    display: inline-block;
    margin-bottom: 25px;
    color: #195A7A;
    text-decoration: none;
    font-size: 19px;
    font-weight: bold;
}

.voltar:hover {
    text-decoration: underline;
}

.evento-container {
    background: white;
    border: 2px solid #D9EAF5;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 10px 35px rgba(64, 124, 170, 0.12);
}

.imagem-evento {
    width: 100%;
    height: 380px;
    object-fit: cover;
    display: block;
    background: #EAF6FF;
}

.informacoes {
    padding: 40px;
}

.informacoes h1 {
    color: #195A7A;
    font-size: 40px;
    margin-top: 0;
    margin-bottom: 20px;
}

.descricao {
    font-size: 20px;
    color: #526B7A;
    margin-bottom: 30px;
}

.dados {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
    margin-bottom: 30px;
}

.dado {
    background: #F5FBFF;
    border: 2px solid #D9EAF5;
    border-radius: 15px;
    padding: 20px;
}

.dado strong {
    display: block;
    color: #195A7A;
    font-size: 18px;
    margin-bottom: 5px;
}

.dado span {
    font-size: 19px;
}

.acessibilidade-evento {
    background: #EAF7FF;
    border: 2px solid #B9E1F7;
    border-radius: 18px;
    padding: 25px;
    margin-top: 25px;
}

.acessibilidade-evento h2 {
    color: #195A7A;
    font-size: 28px;
    margin-top: 0;
}

.acessibilidade-evento p {
    font-size: 18px;
    margin: 12px 0;
}

.form-compra {
    margin-top: 30px;
}

.botao {
    display: block;
    width: 100%;
    padding: 18px;
    background: #8CCCF6;
    color: #173042;
    text-align: center;
    border: none;
    border-radius: 15px;
    font-size: 21px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}

.botao:hover {
    background: #73BEEB;
}

.selecao-area {
    margin-top: 30px;
    padding: 25px;
    background: #F5FBFF;
    border: 2px solid #D9EAF5;
    border-radius: 18px;
}

.selecao-area h2 {
    color: #195A7A;
    margin-top: 0;
}

.selecao-area label {
    display: block;
    font-weight: bold;
    margin-bottom: 10px;
}

.selecao-area select {
    width: 100%;
    padding: 15px;
    border: 2px solid #8CCCF6;
    border-radius: 12px;
    font-size: 17px;
    color: #334E68;
    background: white;
}

.mapa-cadeiras {
    display: none;
    margin-top: 25px;
    padding: 25px;
    background: white;
    border: 2px solid #D9EAF5;
    border-radius: 18px;
}

.mapa-cadeiras h3 {
    text-align: center;
    color: #195A7A;
}

.tela {
    background: #195A7A;
    color: white;
    text-align: center;
    padding: 12px;
    border-radius: 10px;
    margin: 20px auto 30px;
    max-width: 400px;
    font-weight: bold;
}

.fileira {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 12px 0;
    flex-wrap: wrap;
}

.cadeira {
    width: 48px;
    height: 45px;
    border: none;
    border-radius: 9px;
    font-weight: bold;
    cursor: pointer;
    background: #D9EAF5;
    color: #195A7A;
}

.cadeira:hover:not(:disabled) {
    background: #B9E1F7;
}

.cadeira.selecionada {
    background: #73BEEB;
    color: #173042;
    outline: 3px solid #195A7A;
}

.cadeira.ocupada {
    background: #BDBDBD;
    color: #555;
    cursor: not-allowed;
}

.legenda {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 18px;
    margin-top: 25px;
}

.legenda span {
    display: flex;
    align-items: center;
    gap: 7px;
}

.cor {
    width: 18px;
    height: 18px;
    display: inline-block;
    border-radius: 5px;
}

.disponivel {
    background: #D9EAF5;
}

.escolhida {
    background: #73BEEB;
}

.indisponivel {
    background: #BDBDBD;
}

#cadeiraSelecionadaTexto {
    text-align: center;
    font-weight: bold;
    color: #195A7A;
    margin-top: 20px;
}

.mensagem {
    padding: 15px;
    background: #FFF3F3;
    border: 1px solid #E6A5A5;
    color: #8A2525;
    border-radius: 10px;
    margin: 20px 0;
}

.sucesso {
    padding: 25px;
    background: #EAF7FF;
    border: 2px solid #8CCCF6;
    border-radius: 18px;
    margin-top: 25px;
}

.sucesso h2 {
    color: #195A7A;
}

.pix {
    text-align: center;
    margin-top: 25px;
    padding: 20px;
    background: #F5FBFF;
    border-radius: 15px;
}

.pix img {
    width: 220px;
    max-width: 100%;
    margin: 15px auto;
    display: block;
}

.ferramentas {
    margin-top: 40px;
    background: white;
    border: 2px solid #D9EAF5;
    border-radius: 20px;
    padding: 30px;
}

.ferramentas h2 {
    color: #195A7A;
    font-size: 28px;
    margin-top: 0;
}

.botoes {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
}

.botoes button {
    padding: 14px 18px;
    background: white;
    border: 2px solid #8CCCF6;
    border-radius: 12px;
    color: #195A7A;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
}

.botoes button:hover {
    background: #EAF7FF;
}

footer {
    margin-top: 30px;
    padding: 35px;
    background: #195A7A;
    color: white;
    text-align: center;
}

footer p {
    margin: 6px;
    font-size: 17px;
}

@media (max-width: 700px) {

    header {
        flex-direction: column;
    }

    main {
        padding: 30px 20px;
    }

    .imagem-evento {
        height: 250px;
    }

    .informacoes {
        padding: 25px;
    }

    .informacoes h1 {
        font-size: 32px;
    }

    .dados {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<header>

<a href="home.php" class="logo">
    Acesso Livre
</a>

<nav>
    <a href="home.php">Início</a>
    <a href="home.php">Eventos</a>
    <a href="meus_ingressos.php">Meus ingressos</a>
    <a href="perfil.php">Meu perfil</a>
    <a href="logout.php" class="sair">Sair</a>
</nav>

</header>

<main>

<a href="home.php" class="voltar">
    ← Voltar para eventos
</a>

<article class="evento-container">

<img
    src="<?= htmlspecialchars($imagemEvento) ?>?v=<?= time() ?>"
    alt="Imagem do evento <?= htmlspecialchars($evento["nome"]) ?>"
    class="imagem-evento"
>

<div class="informacoes">

<h1>
    <?= htmlspecialchars($evento["nome"]) ?>
</h1>

<p class="descricao">
    <?= htmlspecialchars($evento["descricao"]) ?>
</p>

<div class="dados">

<div class="dado">
<strong>📅 Data</strong>
<span>
    <?= date("d/m/Y", strtotime($evento["data_evento"])) ?>
</span>
</div>

<div class="dado">
<strong>🕐 Horário</strong>
<span>
    <?= htmlspecialchars($evento["horario"]) ?>
</span>
</div>

<div class="dado">
<strong>📍 Local</strong>
<span>
    <?= htmlspecialchars($evento["local"]) ?>
</span>
</div>

<div class="dado">
<strong>🎫 Valor</strong>
<span>
    R$ <?= number_format($evento["preco"], 2, ",", ".") ?>
</span>
</div>

</div>

<section class="acessibilidade-evento">

<h2>♿ Acessibilidade</h2>

<p>
<strong>Recursos disponíveis:</strong>
<?= htmlspecialchars($evento["acessibilidade"] ?? "Não informado") ?>
</p>

<p>
<strong>🔊 Audiodescrição:</strong>
<?= htmlspecialchars($evento["audiodescricao"] ?? "Não informado") ?>
</p>

<p>
<strong>🗺️ Mapa sensorial:</strong>
<?= htmlspecialchars($evento["mapa_sensorial"] ?? "Não informado") ?>
</p>

</section>

<?php if ($mensagem): ?>
    <div class="mensagem">
        <?= htmlspecialchars($mensagem) ?>
    </div>
<?php endif; ?>

<?php if ($compraRealizada): ?>

<section class="sucesso">

    <h2>Ingresso reservado com sucesso!</h2>

    <p>
        <strong>Evento:</strong>
        <?= htmlspecialchars($evento["nome"]) ?>
    </p>

    <p>
        <strong>Área escolhida:</strong>
        <?= htmlspecialchars($areaEscolhida) ?>
    </p>

    <?php if ($cadeiraEscolhida): ?>
        <p>
            <strong>Cadeira:</strong>
            <?= htmlspecialchars($cadeiraEscolhida) ?>
        </p>
    <?php endif; ?>

    <p>
        <strong>Código do ingresso:</strong>
        <?= htmlspecialchars($codigo) ?>
    </p>

    <p>
        <strong>Valor:</strong>
        R$ <?= number_format($evento["preco"], 2, ",", ".") ?>
    </p>

    <div class="pix">

        <h3>Pagamento via Pix</h3>

        <p>
            Este QR Code contém os dados informativos do pedido.
            Ele não é uma cobrança Pix válida.
        </p>

        <?php
        $textoPix = "Acesso Livre - " . $evento["nome"] .
            " - Pedido " . $codigo .
            " - Valor R$ " . number_format($evento["preco"], 2, ",", ".");
        ?>

        <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($textoPix) ?>"
            alt="QR Code informativo do pedido"
        >

        <p>
            Para receber pagamentos reais, será necessário integrar
            uma chave Pix ou um serviço de pagamento.
        </p>

    </div>

    <a href="meus_ingressos.php" class="botao">
        Ver meus ingressos
    </a>

</section>

<?php else: ?>

<form method="POST" class="form-compra" id="formCompra">

    <section class="selecao-area">

        <h2>🗺️ Escolha sua área</h2>

        <p>
            Selecione o espaço que melhor atende às suas necessidades.
        </p>

        <label for="areaEscolhida">
            Área do evento:
        </label>

        <select
            name="areaEscolhida"
            id="areaEscolhida"
            required
        >
            <option value="">Selecione uma área</option>
            <option value="Espaço tranquilo">Espaço tranquilo</option>
            <option value="Área comum">Área comum</option>
            <option value="Espaço reservado">Espaço reservado</option>
        </select>

        <div class="mapa-cadeiras" id="mapaCadeiras">

            <h3>🎟️ Mapa de cadeiras</h3>

            <p style="text-align:center;">
                Selecione uma cadeira disponível.
            </p>

            <div class="tela">
                TELA / PALCO
            </div>

            <?php
            $fileiras = ["A", "B", "C", "D", "E"];

            foreach ($fileiras as $fileira):
            ?>

                <div class="fileira">

                    <?php for ($numero = 1; $numero <= 6; $numero++): ?>

                        <?php
                        $codigoCadeira = $fileira . $numero;
                        $ocupada = in_array(
                            $codigoCadeira,
                            $cadeirasOcupadas,
                            true
                        );
                        ?>

                        <button
                            type="button"
                            class="cadeira <?= $ocupada ? 'ocupada' : '' ?>"
                            data-cadeira="<?= htmlspecialchars($codigoCadeira) ?>"
                            <?= $ocupada ? 'disabled' : '' ?>
                            aria-label="<?= $ocupada ? 'Cadeira ocupada' : 'Cadeira disponível' ?> <?= htmlspecialchars($codigoCadeira) ?>"
                        >
                            <?= htmlspecialchars($codigoCadeira) ?>
                        </button>

                    <?php endfor; ?>

                </div>

            <?php endforeach; ?>

            <div class="legenda">

                <span>
                    <i class="cor disponivel"></i>
                    Disponível
                </span>

                <span>
                    <i class="cor escolhida"></i>
                    Selecionada
                </span>

                <span>
                    <i class="cor indisponivel"></i>
                    Ocupada
                </span>

            </div>

            <p id="cadeiraSelecionadaTexto">
                Nenhuma cadeira selecionada.
            </p>

            <input
                type="hidden"
                name="cadeiraEscolhida"
                id="cadeiraEscolhida"
                value=""
            >

        </div>

    </section>

    <button type="submit" class="botao" style="margin-top:20px;">
        🎫 Confirmar reserva
    </button>

</form>

<?php endif; ?>

</div>

</article>

<section class="ferramentas">

<h2>♿ Recursos de acessibilidade</h2>

<p>
    Utilize os recursos abaixo para facilitar sua navegação.
</p>

<div class="botoes">

<button type="button" onclick="aumentarFonte()">
    A+ Aumentar fonte
</button>

<button type="button" onclick="diminuirFonte()">
    A− Diminuir fonte
</button>

<button type="button" onclick="lerPagina()">
    🔊 Ouvir página
</button>

</div>

</section>

</main>

<footer>

<p><strong>Acesso Livre</strong></p>
<p>Eventos acessíveis para todos.</p>
<p>© 2026 Acesso Livre</p>

</footer>

<div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>

<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>

<script>

new window.VLibras.Widget(
    "https://vlibras.gov.br/app"
);

let tamanhoFonte = 18;

function aumentarFonte() {

    tamanhoFonte += 2;

    if (tamanhoFonte > 30) {
        tamanhoFonte = 30;
    }

    document.documentElement.style.fontSize =
        tamanhoFonte + "px";
}

function diminuirFonte() {

    tamanhoFonte -= 2;

    if (tamanhoFonte < 14) {
        tamanhoFonte = 14;
    }

    document.documentElement.style.fontSize =
        tamanhoFonte + "px";
}

function lerPagina() {

    if (!("speechSynthesis" in window)) {
        alert("A leitura por voz não está disponível neste navegador.");
        return;
    }

    speechSynthesis.cancel();

    const texto = document.querySelector("main").innerText;
    const fala = new SpeechSynthesisUtterance(texto);

    fala.lang = "pt-BR";
    fala.rate = 0.9;

    speechSynthesis.speak(fala);
}

// Mostrar o mapa somente quando Espaço reservado for selecionado
const areaEscolhida = document.getElementById("areaEscolhida");
const mapaCadeiras = document.getElementById("mapaCadeiras");
const campoCadeira = document.getElementById("cadeiraEscolhida");
const textoCadeira = document.getElementById("cadeiraSelecionadaTexto");

if (areaEscolhida) {

    areaEscolhida.addEventListener("change", function() {

        if (this.value === "Espaço reservado") {
            mapaCadeiras.style.display = "block";
        } else {
            mapaCadeiras.style.display = "none";
            campoCadeira.value = "";

            document.querySelectorAll(".cadeira.selecionada")
                .forEach(function(cadeira) {
                    cadeira.classList.remove("selecionada");
                });

            textoCadeira.textContent =
                "Nenhuma cadeira selecionada.";
        }

    });

}

// Selecionar cadeira
document.querySelectorAll(".cadeira:not(.ocupada)")
    .forEach(function(cadeira) {

        cadeira.addEventListener("click", function() {

            document.querySelectorAll(".cadeira.selecionada")
                .forEach(function(item) {
                    item.classList.remove("selecionada");
                });

            this.classList.add("selecionada");

            const codigo = this.dataset.cadeira;

            campoCadeira.value = codigo;

            textoCadeira.textContent =
                "Cadeira selecionada: " + codigo;

        });

    });

// Impedir envio sem escolher cadeira na área reservada
const formCompra = document.getElementById("formCompra");

if (formCompra) {

    formCompra.addEventListener("submit", function(event) {

        if (
            areaEscolhida.value === "Espaço reservado" &&
            campoCadeira.value === ""
        ) {
            event.preventDefault();

            alert("Selecione uma cadeira disponível no mapa.");
        }

    });

}

</script>

</body>
</html>
