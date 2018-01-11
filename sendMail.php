<?php
namespace curlMail\sendmail;
use curlMail\mailers\Gmail ;

$gmail=  Gmail::getInstance("user@gmail.com","passwd");
$gmail->send("rmontoyacordero@gmail.com","prueba","esto es una prueba"); 