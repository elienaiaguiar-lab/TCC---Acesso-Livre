<?php

require_once __DIR__ . "/conexao.php";


/*
========================================
TABELA DE USUÁRIOS
========================================
*/

$db->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        senha TEXT NOT NULL,
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");


/*
========================================
TABELA DE EVENTOS
========================================
*/

$db->exec("
    CREATE TABLE IF NOT EXISTS eventos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        descricao TEXT NOT NULL,
        local TEXT NOT NULL,
        data_evento TEXT NOT NULL,
        horario TEXT NOT NULL,
        preco REAL NOT NULL DEFAULT 0,
        acessibilidade TEXT DEFAULT '',
        audiodescricao TEXT DEFAULT '',
        mapa_sensorial TEXT DEFAULT ''
    )
");


/*
========================================
TABELA DE INGRESSOS
========================================
*/

$db->exec("
    CREATE TABLE IF NOT EXISTS ingressos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        evento_id INTEGER NOT NULL,
        codigo TEXT NOT NULL UNIQUE,
        comprado_em DATETIME DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (usuario_id)
            REFERENCES usuarios(id)
            ON DELETE CASCADE,

        FOREIGN KEY (evento_id)
            REFERENCES eventos(id)
            ON DELETE CASCADE
    )
");


/*
========================================
COMANDO PARA INSERIR EVENTOS
========================================
*/

$sql = $db->prepare("
    INSERT INTO eventos (
        nome,
        descricao,
        local,
        data_evento,
        horario,
        preco,
        acessibilidade,
        audiodescricao,
        mapa_sensorial
    )

    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");


/*
========================================
VERIFICA SE O EVENTO 1 EXISTE
========================================
*/

$verificar = $db->prepare("
    SELECT id
    FROM eventos
    WHERE nome = ?
");


$verificar->execute([
    "Festival Acesso Livre"
]);


if (!$verificar->fetch()) {

    $sql->execute([

        [
    "SHOW INFANTIL DA XUXA",
    "UM ESPETÁCULO INFANTIL CHEIO DE MÚSICA, ALEGRIA, BRINCADEIRAS E DIVERSÃO PARA TODA A FAMÍLIA.",
    "SÃO PAULO - SP",
    "2026-11-15",
    "15:00",
    120.00,
    "ESPAÇO ACESSÍVEL, LIBRAS, ACESSIBILIDADE FÍSICA E ÁREA RESERVADA PARA PCD.",
    "AUDIODESCRIÇÃO DISPONÍVEL DURANTE O ESPETÁCULO.",
    "MAPA SENSORIAL DISPONÍVEL PARA AUXILIAR CRIANÇAS E FAMÍLIAS."
]

    ]);

}


/*
========================================
EVENTO 2
========================================
*/

$verificar->execute([
    "Evento de Música"
]);


if (!$verificar->fetch()) {

    $sql->execute([

        "Evento de Música",

        "TURNE SHOW HISTÓRICO - LUAN SANTANA.",

        "SÃO PAULO - SP",

        "2026-11-15",

        "20:00",

        100.00,

        "Libras, audiodescrição e espaço PCD.",

        "Audiodescrição disponível.",

        "Mapa sensorial disponível."

    ]);

}


/*
========================================
EVENTO 3
========================================
*/

$verificar->execute([
    "Show Acesso Livre"
]);


if (!$verificar->fetch()) {

    $sql->execute([

   "SHOW HUMORÍSTICO ACESSO LIVRE",

"UMA NOITE DE MUITAS RISADAS, COMÉDIA E DIVERSÃO PARA TODOS.",

"SÃO PAULO - SP",

"2026-10-10",

"18:00",

80.00,

"ESPAÇO ACESSÍVEL, LIBRAS, ACESSIBILIDADE FÍSICA E ÁREA RESERVADA PARA PCD.",

"AUDIODESCRIÇÃO DISPONÍVEL DURANTE O EVENTO.",

"MAPA SENSORIAL DISPONÍVEL PARA AUXILIAR NA EXPERIÊNCIA DO PÚBLICO."

    ]);

}


/*
========================================
MOSTRA RESULTADO
========================================
*/

$total = $db->query("
    SELECT COUNT(*)
    FROM eventos
")->fetchColumn();


echo "<h1>Banco funcionando!</h1>";

echo "<p>Tabelas criadas corretamente.</p>";

echo "<p>Total de eventos cadastrados: <strong>"
    . $total .
    "</strong></p>";


echo "<h2>Eventos:</h2>";


$eventos = $db->query("
    SELECT id, nome
    FROM eventos
    ORDER BY id ASC
")->fetchAll();


foreach ($eventos as $evento) {

    echo "<p>✅ Evento "
        . $evento["id"]
        . " - "
        . htmlspecialchars($evento["nome"])
        . "</p>";

}

?>