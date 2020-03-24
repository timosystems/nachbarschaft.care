<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/session.php');

if(!isset($_SESSION['email'])){
 
    header("Location: /?problem=removehelp&reason=no_session");
    exit;

}

if(!isset($_POST['help_id'])) {

    header("Location: /?problem=removehelp&reason=missing_input");
    exit;

}

if($_POST['help_id'] == '') {

    header("Location: /?problem=removehelp&reason=missing_input");
    exit;

}

$_helpID = filter_var($_POST['help_id'], FILTER_SANITIZE_NUMBER_INT);

try {

    $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    $DB->query("DELETE FROM hilfsangebote WHERE ref_account=? AND id=?", array($_SESSION['id'], $_helpID));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=removehelp&reason=failed_db");
    exit;
}

header("Location: /?success=removehelp");