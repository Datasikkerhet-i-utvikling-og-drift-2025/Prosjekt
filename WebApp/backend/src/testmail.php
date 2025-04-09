<?php

require_once '/var/www/html/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    echo "PHPMailer loaded!";
} catch (Exception $e) {
    echo $e->getMessage();
}

?>