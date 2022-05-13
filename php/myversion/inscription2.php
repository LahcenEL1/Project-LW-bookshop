<?php

/* ------------------------------------------------------------------------------
    Architecture de la page
    - étape 1 : vérification des paramètres reçus dans l'URL
    - étape 2 : génération du code HTML de la page
------------------------------------------------------------------------------*/

ob_start(); //démarre la bufferisation

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';
ini_set('display', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)



em_aff_debut('BookShop | Recherche', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete(true);


$y= array('mail','passe1','passe2','nomprenom','naissance_j','naissance_m','naissances_a','inscrire');

$z=array('chkHtml','chkCss','chkJS','chkPHP');
if(!parametre_controle('post',$y,$z)){
    header('Location; ./index.php');
    exit(1);
}


$nb=strlen($_POST['passe1']);
if($nb<4)








em_aff_pied();

em_aff_fin('main');

// fin du script --> envoi de la page 
ob_end_flush();



?>