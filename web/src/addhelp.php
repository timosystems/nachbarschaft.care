<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');
require(dirname(__FILE__) . '/config/config.php');
require(dirname(__FILE__) . '/helper/session.php');

if(!isset($_SESSION['email'])){
 
    header("Location: /?problem=addhelp&reason=no_session");
    exit;

}

if($_POST['add_what'] == '' ||
   $_POST['add_plz'] == '') {

    header("Location: /?problem=addhelp&reason=missing_input");
    exit;

}

if($_POST['add_what'] == 'sonstiges' &&
   $_POST['add_what_sonstiges'] == '') {

    header("Location: /?problem=addhelp&reason=missing_input");
    exit;

}

if(!isset($_POST['add_montag']) &&
   !isset($_POST['add_dienstag']) &&
   !isset($_POST['add_mittwoch']) &&
   !isset($_POST['add_donnerstag']) &&
   !isset($_POST['add_freitag']) &&
   !isset($_POST['add_samstag']) &&
   !isset($_POST['add_sonntag'])){

    header("Location: /?problem=addhelp&reason=missing_days");
    exit;
}

$_what = ($_POST['add_what'] == 'sonstiges')?'Sonstiges - '.filter_var($_POST['add_what_sonstiges'], FILTER_SANITIZE_SPECIAL_CHARS):filter_var($_POST['add_what'], FILTER_SANITIZE_SPECIAL_CHARS);
$_description = filter_var($_POST['add_description'], FILTER_SANITIZE_SPECIAL_CHARS);
$_plz_array = explode(',', str_replace(' ', '', filter_var($_POST['add_plz'], FILTER_SANITIZE_SPECIAL_CHARS)));
$_plz_array_checked = array();

foreach($_plz_array as $_plz_array_item){

    if(preg_match('/^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/', $_plz_array_item)){
        array_push($_plz_array_checked, $_plz_array_item);
    }

}

if(count($_plz_array_checked) == 0){
    header("Location: /?problem=addhelp&reason=failed_plz");
    exit;
}


if(isset($_POST['add_montag'])){
    $_montag = true;
}
else{
    $_montag = false;
}
if(isset($_POST['add_dienstag'])){
    $_dienstag = true;
}
else{
    $_dienstag = false;
}
if(isset($_POST['add_mittwoch'])){
    $_mittwoch = true;
}
else{
    $_mittwoch = false;
}
if(isset($_POST['add_donnerstag'])){
    $_donnerstag = true;
}
else{
    $_donnerstag = false;
}
if(isset($_POST['add_freitag'])){
    $_freitag = true;
}
else{
    $_freitag = false;
}
if(isset($_POST['add_samstag'])){
    $_samstag = true;
}
else{
    $_samstag = false;
}
if(isset($_POST['add_sonntag'])){
    $_sonntag = true;
}
else{
    $_sonntag = false;
}

foreach($_plz_array_checked as $_plz_array_checked_item){

    try {

        $DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
        $DB->query("INSERT INTO hilfsangebote (what, description, plz, ref_account, montag, dienstag, mittwoch, donnerstag, freitag, samstag, sonntag) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($_what,$_description,$_plz_array_checked_item,$_SESSION['id'],$_montag,$_dienstag,$_mittwoch,$_donnerstag,$_freitag,$_samstag,$_sonntag));
    
    } catch (\Throwable $th) {
    
        error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
        header("Location: /?problem=addhelp&reason=failed_db");
        exit;
    }

}

header("Location: /?success=addhelp&plz=" . $_plz_array_checked[0]);

?>