<?php
//Config
date_default_timezone_set("Europe/Paris");
setlocale(LC_TIME, 'en_EN');
define('DIR_BASE', dirname( dirname( __FILE__ ) ) . '/');

//Database connexion parameters
define('SQL_USER',   'root');
define('SQL_PASS',   'benbenben');
define('SQL_HOST',   'localhost:3306');
define('SQL_DTB',    'drawer');

spl_autoload_register(function ($className) {
  switch ($className) {
    case 'Db' :
      include_once DIR_BASE . 'model/db/Db.class.php';
      break;
    case 'DbHelper' :
      include_once DIR_BASE . 'model/db/DbHelper.class.php';
      break;
    case 'Rectangle' :
      include_once DIR_BASE . 'model/drawer/Rectangle.class.php';
      break;
    default:
      break;
  }
});
if(session_id() === "") {
  session_start();
}

