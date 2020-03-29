<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/session.php');

$DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

if(!isset($_SESSION['email'])){
 
    header("Location: /?problem=editaccount&reason=no_session");
    exit;

}

if(!isset($_POST['edit_vorname']) ||
   !isset($_POST['edit_nachname'])){

    header("Location: /?problem=editaccount&reason=missing_input");
    exit;

}

if($_POST['edit_vorname'] == '' ||
   $_POST['edit_nachname'] == '' ){

    header("Location: /?problem=editaccount&reason=missing_input");
    exit;

}

$_phone = ($_POST['edit_phone'] != '') ? filter_var($_POST['edit_phone'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
$_password = ($_POST['edit_password'] != '') ? $_POST['edit_password'] : null;
$_vorname = filter_var($_POST['edit_vorname'], FILTER_SANITIZE_SPECIAL_CHARS);
$_nachname = filter_var($_POST['edit_nachname'], FILTER_SANITIZE_SPECIAL_CHARS);

if($_password != null){

    if(strlen($_password) < 8){

        header("Location: /?problem=editaccount&reason=failed_password");
        exit;

    }

    $_password = password_hash($_password, PASSWORD_DEFAULT);

    try {

        $DB->query("UPDATE accounts SET password=? WHERE id=?", array($_password, $_SESSION['id']));

    } catch (\Throwable $th) {
    
        error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
        header("Location: /?problem=editaccount&reason=failed_db");
        exit;
    }

}

if(strlen($_phone) < 8 && strlen($_phone) > 3){

    header("Location: /?problem=editaccount&reason=failed_phone");
    exit;

}

try {

    $_phone = (strlen($_phone) == 0) ? null : ('+49' . $_phone);
    $DB->query("UPDATE accounts SET phone=? WHERE id=?", array($_phone, $_SESSION['id']));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=editaccount&reason=failed_db");
    exit;
}


try {

    $DB->query("UPDATE accounts SET vorname=?, nachname=? WHERE id=?", array($_vorname, $_nachname, $_SESSION['id']));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=editaccount&reason=failed_db");
    exit;
}

header("Location: /?success=editaccount");