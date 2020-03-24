<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/session.php');

if(!isset($_POST['log_email']) ||
   !isset($_POST['log_password'])){

    header("Location: /?problem=login&reason=missing_input");
    exit;

}

$_email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL);
$_password = $_POST['log_password'];

try {

    $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    $_accountpass = $DB->single("SELECT password FROM accounts WHERE email=?", array($_email));
    $_accountid = $DB->single("SELECT id FROM accounts WHERE email=?", array($_email));
    $_accountoptin = $DB->single("SELECT optin FROM accounts WHERE email=?", array($_email));

    if($_accountpass == null){

        header("Location: /?problem=login&reason=wrong_input");
        exit;

    }

    if($_accountoptin == false){

        header("Location: /?problem=login&reason=missing_optin");
        exit;

    }

    if(!password_verify($_password, $_accountpass)){

        header("Location: /?problem=login&reason=wrong_input");
        exit;

    }

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=optin&reason=failed_db");
    exit;

}

$_SESSION['email'] = $_email;
$_SESSION['id'] = $_accountid;

header("Location: /?success=login");

?>