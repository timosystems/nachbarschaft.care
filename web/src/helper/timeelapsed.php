<?php
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'Jahr',
        'm' => 'Monat',
        'w' => 'Woche',
        'd' => 'Tag',
        'h' => 'Stunde',
        'i' => 'Minute',
        's' => 'Sekunde',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            if($v == 'Tag'){
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 'en' : '');
            } else {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 'n' : '');
            }
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return implode(', ', $string);
}
?>