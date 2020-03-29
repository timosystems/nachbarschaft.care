<?php

require(dirname(__FILE__) . '/helper/session.php');
session_destroy();
header("Location: /?nosplash=true");

?>