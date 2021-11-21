<?php
/**
 * Short description for project
 *
 * PHP version 7.2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Timo Widmaier <ich@timo.systems>
 * @version    2.0.0
 * 
 */

// include all the stuff we need for the beep beep server action
require(dirname(__FILE__) . '/src/helper/timeelapsed.php');
require(dirname(__FILE__) . '/vendor/autoload.php');
require(dirname(__FILE__) . '/src/config/config.php');
require(dirname(__FILE__) . '/src/helper/session.php');
$DB = new Db(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

// prepare pw-reset if token has been sent 
$_pw_reset_token = null;
if(isset($_GET['token'])){
    $_pw_reset_token = $_GET['token'];
}

// prepare captcha
$_securimage = new Securimage();

// prepare account data
$_account_login = (isset($_SESSION['email'])) ? true : false;
$_account_email = (isset($_SESSION['email'])) ? $_SESSION['email'] : false;
$_account_id = (isset($_SESSION['id'])) ? $_SESSION['id'] : false;
$_account_vorname = null;
$_account_nachname = null; 
$_account_phone = null; 
if($_account_login == true){
    $_account_vorname = $DB->single("SELECT vorname FROM accounts WHERE id=?", array($_account_id));
    $_account_nachname = $DB->single("SELECT nachname FROM accounts WHERE id=?", array($_account_id));
    $_account_phone = $DB->single("SELECT phone FROM accounts WHERE id=?", array($_account_id));
}

// prepare the modal that has to shown 
$_modal = null;
if(!isset($_GET['success']) && !isset($_GET['problem']) && !isset($_POST['takehelp-mail']) && !isset($_POST['takehelp-phone']) && !isset($_GET['plz']) && !isset($_GET['dopwreset']) && !isset($_GET['nosplash'])){
    $_modal = 'splash';
}
if(isset($_GET['dopwreset'])){
    $_modal = 'dopwreset';
}
if(isset($_POST['takehelp-mail'])){
    $_modal = 'takehelp-mail';
}
if(isset($_POST['takehelp-phone'])){
    $_modal = 'takehelp-phone';
}

// prepare the data for showing the number of help offers on the map 
$_plz_help_count = $DB->query("SELECT plz, COUNT(plz) as count FROM hilfsangebote GROUP BY plz;");
$_plz_help_count_coordinates = array();
for($i = 0; $i < count($_plz_help_count); $i++){
    $_plz_coordinate = $DB->query("SELECT center FROM plz_centers WHERE plz=?", array($_plz_help_count[$i]['plz']));
    if($_plz_coordinate != null){
        // quickfix because some regions in eastern germany have fucking LEADING ZEROS which get removed when stored as integer
        $_plz_help_count[$i]['plz'] = (strlen($_plz_help_count[$i]['plz']) == 4) ? '0' . $_plz_help_count[$i]['plz'] : $_plz_help_count[$i]['plz'];
        array_push($_plz_help_count_coordinates, array($_plz_help_count[$i]['plz'], $_plz_help_count[$i]['count'], explode(', ', $_plz_coordinate[0]['center'])));
    }
}

// prepare the data for the selected plz
$_plz = null;
$_plz_center = null;
$_plz_help_offers = null;
$_plz_polygon_coordinates = null;
if(isset($_GET['plz'])){
    $_plz = filter_var($_GET['plz'], FILTER_SANITIZE_NUMBER_INT);
    $_plz_center = null;
    if(preg_match('/^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/', $_plz)){
        try {
            $_plz_polygon = $DB->query("SELECT * FROM plz_polygons WHERE plz=?", array($_plz));
            $_plz_polygon_coordinates = array();
            $_biggest = 0;
            foreach($_plz_polygon as $_plz_polygon_value){
                $_plz_polygon_coordinates_value_single = json_decode($_plz_polygon_value['polygon'])->coordinates[0];
                if(count($_plz_polygon_coordinates_value_single) > $_biggest){
                    $_biggest = count($_plz_polygon_coordinates_value_single);
                    $_plz_center = explode(', ', $_plz_polygon_value['center']);
                }
                $_plz_polygon_coordinates_singlelist = array();
                foreach($_plz_polygon_coordinates_value_single as $_plz_polygon_coordinates_value_single_value){
                    array_push($_plz_polygon_coordinates_singlelist, array($_plz_polygon_coordinates_value_single_value[1], $_plz_polygon_coordinates_value_single_value[0]));
                }
                array_push($_plz_polygon_coordinates, $_plz_polygon_coordinates_singlelist);
            }
        } catch (\Throwable $th) {
            error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
            header("Location: /?problem=loadplz&reason=failed_db");
            exit;
        }
        try {
            $_plz_help_offers = $DB->query("SELECT * FROM hilfsangebote WHERE plz=? ORDER BY timestamp DESC LIMIT 1000", array($_plz));
            for($i = 0; $i < count($_plz_help_offers); $i++){
                $_plz_help_offers[$i]['phone'] = $DB->single("SELECT phone FROM accounts WHERE id=?", array($_plz_help_offers[$i]['ref_account']));
                $_plz_help_offers[$i]['timestamp'] = time_elapsed_string($_plz_help_offers[$i]['timestamp']); 
            }
        } catch (\Throwable $th) {
            error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
            header("Location: /?problem=loadplz&reason=failed_db");
            exit;
        }
    }
}

// prepare the data for editing the own help offers
$_own_help = null;
if(isset($_SESSION['email'])){ ?>
    <?php
    
    $_own_help = $DB->query("SELECT * FROM hilfsangebote WHERE ref_account=? ORDER BY timestamp DESC", array($_SESSION['id']));
    for($i = 0; $i < count($_own_help); $i++){
        $_own_help[$i]['timestamp'] = time_elapsed_string($_own_help[$i]['timestamp']); 
    }
}

// prepare the data for contacting a helping person via its help offer
$_help_offer_data = null;
$_takehelp_id = null;
$_captcha = (isset($_POST['captcha_code'])) ? filter_var($_POST['captcha_code'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
$_show_phone = false;
if(isset($_POST['takehelp-mail']) || isset($_POST['takehelp-phone'])){
    if(isset($_POST['takehelp-mail'])){
        $_takehelp_id = filter_var($_POST['takehelp-mail'], FILTER_SANITIZE_NUMBER_INT);
    } else {
        $_takehelp_id = filter_var($_POST['takehelp-phone'], FILTER_SANITIZE_NUMBER_INT);
    }
    try {
        $_help_offer_data = $DB->query("SELECT * FROM hilfsangebote WHERE id=?", array($_takehelp_id))[0];
        // quickfix because some regions in eastern germany have fucking LEADING ZEROS which get removed when stored as integer
        $_help_offer_data['plz'] = (strlen($_help_offer_data['plz']) == 4) ? '0' . $_help_offer_data['plz'] : $_help_offer_data['plz'];
        $_help_offer_data['timestamp'] = time_elapsed_string($_help_offer_data['timestamp']);
        $_help_offer_data['phone'] = $DB->single("SELECT phone FROM accounts WHERE id=?", array($_help_offer_data['ref_account']));
    } catch (\Throwable $th) {
        error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
        header("Location: /?problem=takehelp&reason=failed_db");
        exit;
    }
}
if($_captcha != null){
    $_securimage = new Securimage();
    if ($_securimage->check($_captcha) == false) {
        header("Location: /?problem=getphone&reason=wrong_captcha");
        exit;
    }
    try {
        $_stats_for_plz = $DB->single("SELECT asked_phone FROM statistics WHERE plz=?", array($_help_offer_data['plz']));
        if(is_null($_stats_for_plz)){
            $DB->query("INSERT INTO statistics (plz, asked_phone) VALUES (?, ?)", array($_help_offer_data['plz'], 1));
        } else {
            $_stats_for_plz++;
            $DB->query("UPDATE statistics SET asked_phone=? WHERE plz=?", array($_stats_for_plz,$_help_offer_data['plz']));
        }
    } catch (\Throwable $th) {
        error_log('Konnte keinen Datenbankeintrag durchführen! Ursache: ' . $th->getMessage());
        header("Location: /?problem=register&reason=failed_db");
        exit;
    }
    $_show_phone = true;
}

// generate error and success reports from GET parameters
$_error_problem = (isset($_GET['problem'])) ? $_GET['problem'] : null;
$_error_reason = (isset($_GET['reason'])) ? $_GET['reason'] : null;
$_success = (isset($_GET['success'])) ? $_GET['success'] : null;

// load the twig environment from class, initialize it with the data and output the compiled html
$Loader = new \Twig\Loader\FilesystemLoader(dirname(__FILE__) . '/src/templates/page');
$Twig = new \Twig\Environment($Loader, [
//    'cache' => '/src/templates/page/cache',
]);
echo $Twig->render('main.twig', array(
    'config' => array(
        'title' => SYS_NAME,
        'url' => SYS_URL,
        'admin' => array(
            'mail' => SYS_ADMIN_MAIL,
            'name' => SYS_ADMIN_NAME,
            'web' => SYS_ADMIN_WEB
        ),
        'imprint' => array(
            'name' => IMPRESSUM_ANSPRECHPARTNER,
            'mail' => IMPRESSUM_EMAIL,
            'phone' => IMPRESSUM_PHONE,
            'address' => IMPRESSUM_ADRESSE,
            'plz' => IMPRESSUM_PLZ,
            'city' => IMPRESSUM_ORT,
            'web' => IMPRESSUM_WEB
        ),
        'dsgvo' => array(
            'name' => DSGVO_ANSPRECHPARTNER,
            'mail' => DSGVO_EMAIL,
            'phone' => DSGVO_PHONE,
            'address' => DSGVO_ADRESSE,
            'plz' => DSGVO_PLZ,
            'city' => DSGVO_ORT,
            'web' => DSGVO_WEB
        )
    ),
    'pw_reset_token' => $_pw_reset_token,
    'modal' => $_modal, 
    'error' => array(
        'problem' => $_error_problem,
        'reason' => $_error_reason,
    ),
    'success' => $_success,
    'captcha' => $_securimage->getCaptchaHtml(
        array('input_text' => '',
            'input_attributes' => array(
                'class' => 'form-control',
                'required' => 'required',
            )
        )
    ),
    'user' => array(
        'login' => $_account_login,
        'email' => $_account_email,
        'id' => $_account_id,
        'vorname' => $_account_vorname,
        'nachname' => $_account_nachname,
        'phone' => $_account_phone,
    ),
    'data' => array(
        'map' => array(
            'help' => $_plz_help_count_coordinates,
        ),
        'selection' => array(
            'plz' => $_plz,
            'polygon' => $_plz_polygon_coordinates,
            'center' => $_plz_center,
            'offers' => $_plz_help_offers,
        ),
        'ownhelp' => $_own_help,
        'help' => $_help_offer_data,
        'show_phone' => $_show_phone,
    )
));
?>