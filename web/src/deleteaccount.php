<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/randomstring.php');
require(dirname(__FILE__) . '/helper/session.php');

if(!isset($_SESSION['email'])){
 
    header("Location: /?problem=delaccount&reason=no_session");
    exit;

}

if(!isset($_POST['del_email'])){

    header("Location: /?problem=delaccount&reason=missing_input");
    exit;

}

if($_POST['del_email'] == ''){

    header("Location: /?problem=delaccount&reason=missing_input");
    exit;

}

$_email = filter_var($_POST['del_email'], FILTER_SANITIZE_EMAIL);

if(!filter_var($_email, FILTER_VALIDATE_EMAIL)){

    header("Location: /?problem=delaccount&reason=failed_email");
    exit;

}

if($_SESSION['email'] != $_email){

    header("Location: /?problem=delaccount&reason=failed_account");
    exit;

}

try {

    $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    $_account = $DB->single("SELECT id FROM accounts WHERE email=?", array($_SESSION['email']));

    $DB->query("DELETE FROM pwreset WHERE ref_account=?", array($_account));
    $DB->query("DELETE FROM hilfsangebote WHERE ref_account=?", array($_account));
    $DB->query("DELETE FROM accounts WHERE id=?", array($_account));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=delaccount&reason=failed_db");
    exit;
}

session_destroy();
header("Location: /?success=delaccount");