<?php

try {

    $db = new PDO(
        //"mysql:host=127.0.0.1;port=3306;dbname=acessolivre;charset=utf8mb4",
        "mysql:host=sql111.infinityfree.com;port=3306;dbname=if0_43347124_XXX;charset=utf8mb4",
        "if0_43347124",
        "0wba9vMy2mR9"
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Erro ao acessar o banco de dados: " . $e->getMessage());

}

?>
