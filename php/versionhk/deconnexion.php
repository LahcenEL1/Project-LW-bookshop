<?php
require_once('./bibli_bookshop.php');

// démarrage de la session
session_start();

hk_session_exit(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php');

?>