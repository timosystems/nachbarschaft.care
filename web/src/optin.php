<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');

if(!isset($_GET['token'])){

    header("Location: /?problem=optin&reason=missing_input");
    exit;

}

$_token = filter_var($_GET['token'], FILTER_SANITIZE_EMAIL);

try {

    $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    $_account = $DB->single("SELECT id FROM accounts WHERE token=?", array($_token));

    if($_account == null){
        header("Location: /?problem=optin&reason=wrong_input");
        exit;
    };

    $DB->query("UPDATE accounts SET optin = true WHERE id = ?", array($_account));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=optin&reason=failed_db");
    exit;

}

header("Location: /?success=optin");

?>