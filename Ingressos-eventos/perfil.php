<?php

session_start();

require_once __DIR__ . "/banco/conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuarioId = $_SESSION["usuario_id"];

// Buscar dados do usuário
$sql = $db->prepare("
    SELECT *
    FROM usuarios
    WHERE idUsuario = ?
    LIMIT 1
");

$sql->execute([$usuarioId]);

$usuario = $sql->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Buscar compras do usuário
$sqlCompras = $db->prepare("
    SELECT
        ingressos.codigo,
        ingressos.compradoEm AS comprado_em,
        eventos.nome,
        eventos.dataEvento AS data_evento,
        eventos.horario,
        eventos.local,
        eventos.preco
    FROM ingressos
    INNER JOIN eventos
        ON ingressos.idEvento = eventos.idUsuario
    WHERE ingressos.idUsuario = ?
    ORDER BY ingressos.compradoEm DESC
");

$sqlCompras->execute([$usuarioId]);

$compras = $sqlCompras->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Meu Perfil - Acesso Livre</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: linear-gradient(
        135deg,
        #F9FCFF,
        #EAF6FF,
        #F5FBFF
    );

    color: #334E68;

    min-height: 100vh;

}

header {

    background: white;

    border-bottom: 2px solid #D9EAF5;

    padding: 18px 30px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    box-shadow:
        0 3px 15px
        rgba(64, 124, 170, 0.08);

}

.logo {

    color: #195A7A;

    font-size: 28px;

    font-weight: bold;

}

.menu {

    display: flex;

    gap: 15px;

    align-items: center;

}

.menu a {

    text-decoration: none;

    color: #195A7A;

    font-weight: bold;

    font-size: 17px;

    padding: 10px 15px;

    border-radius: 10px;

}

.menu a:hover {

    background: #EAF6FF;

}

.sair {

    background: #8CCCF6;

    color: #173042 !important;

}

.container {

    max-width: 850px;

    margin: 50px auto;

    padding: 0 20px;

}

.perfil {

    background: white;

    border: 2px solid #D9EAF5;

    border-radius: 25px;

    padding: 40px;

    box-shadow:
        0 10px 35px
        rgba(64, 124, 170, 0.15);

}

.titulo {

    text-align: center;

    color: #195A7A;

    font-size: 32px;

    margin-bottom: 10px;

}

.subtitulo {

    text-align: center;

    color: #7A92A5;

    font-size: 18px;

    margin-bottom: 35px;

}

.avatar {

    width: 110px;

    height: 110px;

    margin: 0 auto 25px auto;

    border-radius: 50%;

    background: #8CCCF6;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 50px;

    color: #173042;

}

.informacao {

    background: #F5FBFF;

    border: 2px solid #D9EAF5;

    border-radius: 15px;

    padding: 18px;

    margin-bottom: 15px;

}

.informacao strong {

    display: block;

    color: #195A7A;

    font-size: 17px;

    margin-bottom: 7px;

}

.informacao span {

    font-size: 19px;

    color: #334E68;

    word-break: break-word;

}

.compras-titulo {

    color: #195A7A;

    font-size: 27px;

    margin-top: 40px;

    margin-bottom: 20px;

    border-top: 2px solid #D9EAF5;

    padding-top: 30px;

}

.compra {

    background: #F5FBFF;

    border: 2px solid #D9EAF5;

    border-radius: 18px;

    padding: 22px;

    margin-bottom: 18px;

}

.compra h3 {

    color: #195A7A;

    font-size: 23px;

    margin-top: 0;

    margin-bottom: 18px;

}

.dado-compra {

    margin: 9px 0;

    font-size: 17px;

}

.dado-compra strong {

    color: #195A7A;

}

.codigo {

    margin-top: 15px;

    background: white;

    border: 2px dashed #8CCCF6;

    border-radius: 12px;

    padding: 12px;

    color: #195A7A;

    font-weight: bold;

}

.sem-compras {

    background: #F5FBFF;

    border: 2px solid #D9EAF5;

    border-radius: 15px;

    padding: 25px;

    text-align: center;

    font-size: 18px;

    color: #7A92A5;

}

.botoes {

    display: flex;

    flex-direction: column;

    gap: 12px;

    margin-top: 30px;

}

.botao {

    display: block;

    text-align: center;

    text-decoration: none;

    width: 100%;

    padding: 16px;

    border-radius: 14px;

    background: #8CCCF6;

    color: #173042;

    font-size: 19px;

    font-weight: bold;

}

.botao:hover {

    background: #73BEEB;

}

.acessibilidade {

    margin-top: 30px;

    padding-top: 25px;

    border-top: 2px solid #D9EAF5;

    text-align: center;

}

.acessibilidade h2 {

    color: #195A7A;

    font-size: 23px;

}

.acessibilidade button {

    padding: 12px 15px;

    margin: 5px;

    border: 2px solid #8CCCF6;

    background: white;

    border-radius: 10px;

    color: #195A7A;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

}

.acessibilidade button:hover {

    background: #EAF6FF;

}

@media (max-width: 600px) {

    header {

        padding: 15px;

        flex-direction: column;

        gap: 12px;

    }

    .menu {

        flex-wrap: wrap;

        justify-content: center;

    }

    .perfil {

        padding: 25px 20px;

    }

    .titulo {

        font-size: 27px;

    }

    .compras-titulo {

        font-size: 23px;

    }

}

</style>

</head>

<body>

<header>

<div class="logo">
    Acesso Livre
</div>

<nav class="menu">

<a href="home.php">
    Início
</a>

<a href="perfil.php">
    Meu Perfil
</a>

<a href="logout.php" class="sair">
    Sair
</a>

</nav>

</header>

<main class="container">

<section class="perfil">

<div class="titulo">
    Meu Perfil
</div>

<div class="subtitulo">
    Confira seus dados e suas compras
</div>

<div class="avatar">
    👤
</div>

<div class="informacao">

<strong>Nome</strong>

<span>
    <?= htmlspecialchars($usuario["nome"]) ?>
</span>

</div>

<div class="informacao">

<strong>E-mail</strong>

<span>
    <?= htmlspecialchars($usuario["e-mail"] ?? '') ?>
</span>

</div>

<h2 class="compras-titulo">
    🎫 Minhas compras
</h2>

<?php if (count($compras) > 0): ?>

<?php foreach ($compras as $compra): ?>

<div class="compra">

<h3>
    <?= htmlspecialchars($compra["nome"]) ?>
</h3>

<div class="dado-compra">

📅

<strong>Data do evento:</strong>

<?= date(
    "d/m/Y",
    strtotime($compra["data_evento"])
) ?>

</div>

<div class="dado-compra">

🕐

<strong>Horário:</strong>

<?= htmlspecialchars($compra["horario"]) ?>

</div>

<div class="dado-compra">

📍

<strong>Local:</strong>

<?= htmlspecialchars($compra["local"]) ?>

</div>

<div class="dado-compra">

💰

<strong>Valor:</strong>

R$

<?= number_format(
    (float) $compra["preco"],
    2,
    ",",
    "."
) ?>

</div>

<div class="dado-compra">

🛒

<strong>Comprado em:</strong>

<?= date(
    "d/m/Y H:i",
    strtotime($compra["comprado_em"])
) ?>

</div>

<div class="codigo">

🎟️ Código do ingresso:

<br>

<?= htmlspecialchars($compra["codigo"]) ?>

</div>

</div>

<?php endforeach; ?>

<?php else: ?>

<div class="sem-compras">

Você ainda não realizou nenhuma compra.

</div>

<?php endif; ?>

<div class="botoes">

<a href="home.php" class="botao">
    🏠 Voltar para o início
</a>

</div>

<section class="acessibilidade">

<h2>
    ♿ Acessibilidade
</h2>

<button
    type="button"
    onclick="aumentarFonte()"
>
    A+ Aumentar fonte
</button>

<button
    type="button"
    onclick="diminuirFonte()"
>
    A− Diminuir fonte
</button>

<button
    type="button"
    onclick="lerPagina()"
>
    🔊 Ouvir página
</button>

</section>

</section>

</main>

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

        alert(
            "A leitura por voz não está disponível neste navegador."
        );

        return;
    }

    speechSynthesis.cancel();

    const texto =
        document.querySelector("main").innerText;

    const fala =
        new SpeechSynthesisUtterance(texto);

    fala.lang = "pt-BR";

    fala.rate = 0.9;

    speechSynthesis.speak(fala);

}

</script>

</body>

</html>