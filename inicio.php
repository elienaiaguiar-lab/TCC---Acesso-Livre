<!DOCTYPE html>
<html lang="pt-BR">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Acesso Livre</title>


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            min-height: 100vh;
            min-height: 100svh;
            font-family: Arial, sans-serif;
            background: #140051;


            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }


        /* CONTEÚDO PRINCIPAL */


        .container {
            width: 100%;
            min-height: 100vh;
            min-height: 100svh;


            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;


            padding: 20px;
        }


        /* LOGO */


        .logo {
            display: block;


            width: 320px;
            max-width: 75vw;
            height: auto;


            object-fit: contain;


            opacity: 0;


            animation: logoEntrada 1.2s ease forwards;
        }


        @keyframes logoEntrada {
            0% {
                opacity: 0;
                transform: scale(.65) translateY(25px);
            }


            70% {
                opacity: 1;
                transform: scale(1.06) translateY(-5px);
            }


            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }


        /* SÍMBOLOS DE ACESSIBILIDADE */


        .acessibilidade {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 28px;


            margin: 35px 0 35px;
        }


        .simbolo {
            width: 65px;
            height: 65px;


            border-radius: 50%;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.35);


            display: flex;
            justify-content: center;
            align-items: center;


            opacity: 0;
            transform: translateY(25px) scale(.7);


            animation:
                simboloEntrada .8s ease forwards,
                simboloFlutuar 3s ease-in-out infinite;
        }


        .simbolo:nth-child(1) {
            animation-delay: .7s, 1.5s;
        }


        .simbolo:nth-child(2) {
            animation-delay: 1s, 1.8s;
        }


        .simbolo:nth-child(3) {
            animation-delay: 1.3s, 2.1s;
        }


        .simbolo svg {
            width: 32px;
            height: 32px;


            fill: none;
            stroke: #FFFFFF;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }


        @keyframes simboloEntrada {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }


        @keyframes simboloFlutuar {
            0%, 100% {
                translate: 0 0;
            }


            50% {
                translate: 0 -7px;
            }
        }


        /* RESPONSIVIDADE */


        @media (max-width: 600px) {
            .logo {
                width: 260px;
            }


            .acessibilidade {
                gap: 17px;
                margin: 30px 0;
            }


            .simbolo {
                width: 58px;
                height: 58px;
            }


            .simbolo svg {
                width: 28px;
                height: 28px;
            }
        }


        @media (max-width: 360px) {
            .logo {
                width: 230px;
            }
        }


        /* ACESSIBILIDADE */


        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }


            .logo,
            .simbolo {
                opacity: 1;
                transform: none;
            }
        }
    </style>
</head>


<body>


    <main class="container">


        <!-- LOGO -->
        <img
            src="LOGO.png"
            alt="Acesso Livre"
            class="logo"
        >


        <!-- SÍMBOLOS DE ACESSIBILIDADE -->
        <div class="acessibilidade">


            <!-- ACESSIBILIDADE FÍSICA -->
            <div class="simbolo" title="Acessibilidade física">
                <svg viewBox="0 0 24 24">
                    <circle cx="10" cy="4" r="2"/>
                    <circle cx="9" cy="16" r="6"/>
                    <path d="M10 7l2 5h5l2 5"/>
                    <path d="M10 7l-2 6"/>
                    <path d="M12 12h5"/>
                </svg>
            </div>


            <!-- ACESSIBILIDADE VISUAL -->
            <div class="simbolo" title="Acessibilidade visual">
                <svg viewBox="0 0 24 24">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>


            <!-- ACESSIBILIDADE AUDITIVA -->
            <div class="simbolo" title="Acessibilidade auditiva">
                <svg viewBox="0 0 24 24">
                    <path d="M5 11a7 7 0 0 1 14 0c0 4-3 5-4 7"/>
                    <path d="M9 11a3 3 0 0 1 6 0c0 2-2 3-3 4"/>
                    <path d="M11 19c0 2 3 2 4 0"/>
                </svg>
            </div>


        </div>


    </main>


    <!-- REDIRECIONAMENTO INVISÍVEL APÓS 5 SEGUNDOS -->


    <script>
        setTimeout(function () {
            window.location.href = "login.php";
        }, 5000);
    </script>


</body>
</html>