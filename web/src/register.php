<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/randomstring.php');

if(!isset($_POST['reg_email']) ||
   !isset($_POST['reg_password']) ||
   !isset($_POST['reg_vorname']) ||
   !isset($_POST['reg_nachname'])){

    header("Location: /?problem=register&reason=missing_input");
    exit;

}

$_email = filter_var($_POST['reg_email'], FILTER_SANITIZE_EMAIL);
$_phone = (isset($_POST['reg_phone'])) ? filter_var($_POST['reg_phone'], FILTER_SANITIZE_EMAIL) : null;
$_password = $_POST['reg_password'];
$_vorname = filter_var($_POST['reg_vorname'], FILTER_SANITIZE_SPECIAL_CHARS);
$_nachname = filter_var($_POST['reg_nachname'], FILTER_SANITIZE_SPECIAL_CHARS);
$_token = randomstring(64);
$_linkOptin = SYS_URL . '/src/optin.php?token=' . $_token;

if(!filter_var($_email, FILTER_VALIDATE_EMAIL)){

    header("Location: /?problem=register&reason=failed_email");
    exit;

}

if(strlen($_password) < 8){

    header("Location: /?problem=register&reason=failed_password");
    exit;

}

$_password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);

try {

    $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    if($DB->single("SELECT id FROM accounts WHERE email=?", array($_email)) != null){

        header("Location: /?problem=register&reason=failed_alreadyadded");
        exit;

    };
    $DB->query("INSERT INTO accounts (email, password, vorname, nachname, optin, token, phone) VALUES(?, ?, ?, ?, ?, ?, ?)", array($_email,$_password,$_vorname,$_nachname,false,$_token,$_phone));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=register&reason=failed_db");
    exit;
}

try {

    $_mailContent = file_get_contents(dirname(__FILE__) . '/templates/email/register_optin.html');
    $_mailContent = str_replace('REPLACE_VORNAME', $_vorname, $_mailContent);
    $_mailContent = str_replace('REPLACE_LINK_OPTIN', $_linkOptin, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_URL', SYS_URL, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_NAME', SYS_NAME, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_ADMINMAIL', SYS_ADMINMAIL, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_ADMIN', SYS_ADMIN, $_mailContent);
    $_mailContent = utf8_decode($_mailContent);

    $Mail = new PHPMailer\PHPMailer\PHPMailer(false);
    $Mail->isSMTP();
    $Mail->Host       = SMTP_HOST;
    $Mail->SMTPAuth   = true;
    $Mail->Username   = SMTP_USER;
    $Mail->Password   = SMTP_PASS;
    $Mail->SMTPSecure = SMTP_ENCRYPTION;
    $Mail->Port       = SMTP_PORT;
    $Mail->setFrom(SMTP_SENDERMAIL, SMTP_SENDERFROM);
    $Mail->addAddress($_email, $_vorname . ' ' .  $_nachname);
    $Mail->isHTML(true);
    $Mail->Subject = utf8_decode('[' . SYS_NAME . '] Bitte bestätige deinen Account!');
    $Mail->Body = $_mailContent;
    $Mail->send();

} catch (\Throwable $th) {
 
     error_log('Konnte keinen Bestätigungsmail senden! Ursache: ' . $th->getMessage());
     header("Location: /?problem=register&reason=failed_optin");
     exit;

}

header("Location: /?success=register");

?>