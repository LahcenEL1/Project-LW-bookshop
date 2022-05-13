<?php

require_once '../php/bibli_generale.php';
require_once ('../php/bibli_bookshop.php');

em_aff_debut('BookShop | Inscription1','main');
echo '<pre>';
    foreach( $_POST as $key => $value ){
        echo $key,' => ',$value,"\n";
    }
    echo '<hr>';
    var_dump($_POST);
    echo '<hr>';
    print_r($_POST);
        
echo '</pre>';
em_aff_fin('main');


?>