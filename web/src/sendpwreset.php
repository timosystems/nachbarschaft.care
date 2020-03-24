<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/randomstring.php');

if(!isset($_POST['pwre_email'])){

    header("Location: /?problem=pwreset&reason=missing_input");
    exit;

}

if($_POST['pwre_email'] == ''){

    header("Location: /?problem=pwreset&reason=missing_input");
    exit;

}

$_email = filter_var($_POST['pwre_email'], FILTER_SANITIZE_EMAIL);

if(!filter_var($_email, FILTER_VALIDATE_EMAIL)){

    header("Location: /?problem=pwreset&reason=failed_email");
    exit;

}

try {

    $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    $_account = $DB->query("SELECT * FROM accounts WHERE email=?", array($_email))[0];
    $_token = randomstring(128);
    $_link = SYS_URL . '/?dopwreset=true&token=' . $_token;

    if($_account == null){

        header("Location: /?success=pwreset");
        exit;

    };
    $DB->query("INSERT INTO pwreset (ref_account, token) VALUES(?, ?)", array($_account['id'],$_token));

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=pwre&reason=failed_db");
    exit;
}

try {

    $_mailContent = file_get_contents(dirname(__FILE__) . '/templates/email/passwort_reset.html');
    $_mailContent = str_replace('REPLACE_VORNAME', $_account['vorname'], $_mailContent);
    $_mailContent = str_replace('REPLACE_LINK', $_link, $_mailContent);
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
    $Mail->addAddress($_account['email'], $_account['vorname'] . ' ' .  $_account['nachname']);
    $Mail->isHTML(true);
    $Mail->Subject = utf8_decode('[' . SYS_NAME . '] Link um Passwort neu zu setzen');
    $Mail->Body = $_mailContent;
    $Mail->send();

} catch (\Throwable $th) {
 
     error_log('Konnte keinen Bestätigungsmail senden! Ursache: ' . $th->getMessage());
     header("Location: /?problem=pwreset&reason=failed_email");
     exit;

}

header("Location: /?success=pwreset");