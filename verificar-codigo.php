
<?php
session_start();

if (
    !isset($_SESSION["recuperar_usuario_id"]) ||
    !isset($_SESSION["codigo_recuperacao"]) ||
    !isset($_SESSION["expira_codigo"])
) {
    header("Location: esqueciminhasenha.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codigo = trim($_POST["codigo"] ?? "");

    if (time() > (int) $_SESSION["expira_codigo"]) {

        unset(
            $_SESSION["codigo_recuperacao"],
            $_SESSION["expira_codigo"],
            $_SESSION["codigo_verificado"]
        );

        $erro = "O código expirou. Solicite um novo código.";

    } elseif (!preg_match('/^\d{6}$/', $codigo)) {

        $erro = "Digite o código de 6 números.";

    } elseif (
        hash_equals(
            (string) $_SESSION["codigo_recuperacao"],
            $codigo
        )
    ) {

        $_SESSION["codigo_verificado"] = true;

        unset(
            $_SESSION["codigo_recuperacao"],
            $_SESSION["expira_codigo"]
        );

        header("Location: novasenha.php");
        exit;

    } else {

        $erro = "Código incorreto. Confira e tente novamente.";

    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verificar código - Acesso Livre</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #F5FBFF;
            color: #334E68;
        }

        .container {
            width: 100%;
            max-width: 420px;
            padding: 35px;
            background: white;
            border: 1px solid #D9EAF5;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(64, 124, 170, 0.12);
            text-align: center;
        }

        h1 {
            color: #195A7A;
            font-size: 25px;
            margin-bottom: 12px;
        }

        p {
            line-height: 1.6;
            margin-bottom: 22px;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #D9EAF5;
            border-radius: 8px;
            font-size: 22px;
            text-align: center;
            letter-spacing: 6px;
            margin-bottom: 18px;
        }

        input:focus {
            outline: 2px solid #8CCCF6;
            border-color: #73BEEB;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #8CCCF6;
            color: #195A7A;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #73BEEB;
        }

        .erro {
            padding: 10px;
            margin-bottom: 16px;
            border-radius: 8px;
            color: #A61B1B;
            background: #FFF0F0;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: #195A7A;
        }
    </style>
</head>

<body>

    <main class="container">

        <h1>Verificar código</h1>

        <p>
            Digite o código de 6 números enviado para o seu e-mail
            para continuar a recuperação da senha.
        </p>

        <?php if ($erro !== ""): ?>
            <p class="erro" role="alert">
                <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="verificar-codigo.php">

            <label for="codigo">Código de verificação</label>

            <input
                type="text"
                id="codigo"
                name="codigo"
                inputmode="numeric"
                autocomplete="one-time-code"
                pattern="[0-9]{6}"
                minlength="6"
                maxlength="6"
                placeholder="000000"
                aria-describedby="ajuda"
                required
            >

            <p id="ajuda">O código deve conter 6 números.</p>

            <button type="submit">Verificar código</button>

        </form>

        <a href="esqueciminhasenha.php">
            Solicitar outro código
        </a>

    </main>

</body>
</html>
