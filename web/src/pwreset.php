<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/randomstring.php');

if(!isset($_POST['dopwre_token']) || 
   !isset($_POST['dopwre_newpassword'])){

    header("Location: /?problem=dopwreset&reason=missing_input");
    exit;

}

if($_POST['dopwre_token'] == '' ||
   $_POST['dopwre_newpassword'] == ''){

    header("Location: /?problem=dopwreset&reason=missing_input");
    exit;

}

$_newpass = $_POST['dopwre_newpassword'];
$_token = filter_var($_POST['dopwre_token'], FILTER_SANITIZE_SPECIAL_CHARS);

if(strlen($_newpass) < 8){

    header("Location: /?problem=dopwreset&reason=failed_password");
    exit;

}

$_newpass = password_hash($_newpass, PASSWORD_DEFAULT);

try {

    $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    $_accountID = $DB->single("SELECT ref_account FROM pwreset WHERE token=? AND timestamp > (UNIX_TIMESTAMP() - 86400)", array($_token));

    if($_accountID == null){

        header("Location: /?problem=dopwreset&reason=wrong_input");
        exit;

    }
    
    $DB->query("DELETE FROM pwreset WHERE token=?", array($_token));

    $DB->query("UPDATE accounts SET password = ? WHERE id = ?", array($_newpass, $_accountID));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=dopwreset&reason=failed_db");
    exit;
}

header("Location: /?success=dopwreset");