<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/session.php');
$DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

if(!isset($_POST['help_id']) ||
   !isset($_POST['take_name']) ||
   !isset($_POST['take_email']) ||
   !isset($_POST['take_message']) ||
   !isset($_POST['take_plz']) ||
   !isset($_POST['captcha_code'])){

    header("Location: /?problem=takehelp&reason=missing_input");
    exit;

}

if($_POST['help_id'] == '' ||
   $_POST['take_name'] == '' ||
   $_POST['take_email'] == '' ||
   $_POST['take_message'] == '' ||
   $_POST['take_plz'] == '' || 
   $_POST['captcha_code'] == ''){

    header("Location: /?problem=takehelp&reason=missing_input");
    exit;

}

$_helpID =  filter_var($_POST['help_id'], FILTER_SANITIZE_NUMBER_INT);
$_name =    filter_var($_POST['take_name'], FILTER_SANITIZE_SPECIAL_CHARS);
$_email =   filter_var($_POST['take_email'], FILTER_SANITIZE_EMAIL);
$_strasse = (isset($_POST['take_strasse'])) ? filter_var($_POST['take_strasse'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
$_nummer =  (isset($_POST['take_nummer'])) ? filter_var($_POST['take_nummer'], FILTER_SANITIZE_SPECIAL_CHARS) : '';
$_message = filter_var($_POST['take_message'], FILTER_SANITIZE_SPECIAL_CHARS);
$_plz =     filter_var($_POST['take_plz'], FILTER_SANITIZE_NUMBER_INT);
$_captcha = filter_var($_POST['captcha_code'], FILTER_SANITIZE_SPECIAL_CHARS);

$_securimage = new Securimage();
if ($_securimage->check($_captcha) == false) {
    header("Location: /?problem=takehelp&reason=wrong_captcha");
    exit;
}

if(!filter_var($_email, FILTER_VALIDATE_EMAIL)){

    header("Location: /?problem=takehelp&reason=failed_email");
    exit;

}

if(!preg_match('/^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/', $_plz)){

    header("Location: /?problem=takehelp&reason=failed_plz");
    exit;

}

try {

    $_hilfsangebot = $DB->query("SELECT * FROM hilfsangebote WHERE id=?", array($_helpID))[0];
    $_helfer = $DB->query("SELECT * FROM accounts WHERE id=?", array($_hilfsangebot['ref_account']))[0];

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=register&reason=failed_db");
    exit;
}

try {

    $_stats_for_plz = $DB->single("SELECT asked_mail FROM statistics WHERE plz=?", array($_hilfsangebot['plz']));
    if($_stats_for_plz == null){
        $DB->query("INSERT INTO statistics (plz, asked_mail) VALUES (?, ?)", array($_hilfsangebot['plz'], 1));
    } else {
        $_stats_for_plz++;
        $DB->query("UPDATE statistics SET asked_mail=? WHERE plz=?", array($_stats_for_plz,$_hilfsangebot['plz']));
    }

} catch (\Throwable $th) {

    error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
    header("Location: /?problem=register&reason=failed_db");
    exit;
}

try {

    $_mailContent = file_get_contents(dirname(__FILE__) . '/templates/email/help_askfor_copy.html');
    $_mailContent = str_replace('REPLACE_HELFER_VORNAME', '[Anonym]', $_mailContent);
    $_mailContent = str_replace('REPLACE_HILFSANGEBOT_WHAT', ucfirst($_hilfsangebot['what']), $_mailContent);
    $_mailContent = str_replace('REPLACE_HILFSANGEBOT_PLZ', $_hilfsangebot['plz'], $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_NAME', $_name, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_EMAIL', $_email, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_PLZ', $_plz, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_STRASSE', $_strasse, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_NUMMER', $_nummer, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_MESSAGE', $_message, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_URL', SYS_URL, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_NAME', SYS_NAME, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_ADMINMAIL', SYS_ADMIN_MAIL, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_ADMIN', SYS_ADMIN_NAME, $_mailContent);
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
    $Mail->addReplyTo($_email);
    $Mail->addAddress($_email, $_name);
    $Mail->isHTML(true);
    $Mail->Subject = utf8_decode('[' . SYS_NAME . '] Folgende Nachricht wurde an den*die Helfer*in versendet (Kopie für dich)!');
    $Mail->Body = $_mailContent;
    $Mail->send();

} catch (\Throwable $th) {
 
     error_log('Konnte keinen Bestätigungsmail senden! Ursache: ' . $th->getMessage());
     header("Location: /?problem=takehelp&reason=failed_contact");
     exit;

}

try {

    $_mailContent = file_get_contents(dirname(__FILE__) . '/templates/email/help_askfor.html');
    $_mailContent = str_replace('REPLACE_HELFER_VORNAME', $_helfer['vorname'], $_mailContent);
    $_mailContent = str_replace('REPLACE_HILFSANGEBOT_WHAT', ucfirst($_hilfsangebot['what']), $_mailContent);
    $_mailContent = str_replace('REPLACE_HILFSANGEBOT_PLZ', $_hilfsangebot['plz'], $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_NAME', $_name, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_EMAIL', $_email, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_PLZ', $_plz, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_STRASSE', $_strasse, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_NUMMER', $_nummer, $_mailContent);
    $_mailContent = str_replace('REPLACE_FRAGENDER_MESSAGE', $_message, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_URL', SYS_URL, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_NAME', SYS_NAME, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_ADMINMAIL', SYS_ADMIN_MAIL, $_mailContent);
    $_mailContent = str_replace('REPLACE_SYS_ADMIN', SYS_ADMIN_NAME, $_mailContent);
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
    $Mail->addReplyTo($_email);
    $Mail->addAddress($_helfer['email'], $_helfer['vorname'] . ' ' . $_helfer['nachname']);
    $Mail->isHTML(true);
    $Mail->Subject = utf8_decode('[' . SYS_NAME . '] Jemand benötigt deine Hilfe!');
    $Mail->Body = $_mailContent;
    $Mail->send();

} catch (\Throwable $th) {
 
     error_log('Konnte keinen Bestätigungsmail senden! Ursache: ' . $th->getMessage());
     header("Location: /?problem=takehelp&reason=failed_contact");
     exit;

}

header("Location: /?success=takehelp");