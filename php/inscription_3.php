<?php

ob_start(); 

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';
ini_set('display', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 



em_aff_debut('BookShop | Recherche', '../styles/bookshop.css', 'main');


$a= array('nomprenom', 'naissance_j', 'naissance_m', 'naissance_a','passe1', 'passe2', 'email', 'btnSInscrire');



if(!em_parametres_controle('post',$a)){
    header('Location; ./index.php');
    exit(1);
}

$Erreur=array();
$pass_length=strlen($_POST['passe1']);


// Verification mot de passe

if ($pass_length<4 ||$pass_length >20 ){
	$Erreur[]='Le mot de passe doit avoir entre 6 et 8 caractères';
}

// Verification saisie 1 et 2 

if ($_POST['passe1'] != $_POST['passe2'] ){
	$Erreur[]='Le mot de pase doit etre identique a la deuxieme saisie';
}


// Verification Prenom

if(empty($_POST['nomprenom'])){
	$Erreur[]='Veuillez saisir votre prenom et nom';
}

if(strip_tags($nomprenom) != $nomprenom){
        $Erreur[]='Veuillez eviter de faire de HTML';
}

if(! preg_match("/^[a-zA-Z][a-zA-Z\-']{1,19}$/", $nomprenom)){

    $Erreur[]='Veuillez eviter de faire de HTML';

}

// <!-- Verification date de naissance  -->
$j=(int)$_POST['naissance_j'];
$m=(int)$_POST['naissance_m'];
$a=(int)$_POST['naissance_a'];

if ($j<1 || $j>31 || $m<1 || $m>12  || $a<1){
	header('Location; ./index.php');
    exit(1);
}

if (!checkdate($m,$j,$a)) {
    $Erreur[]='date errone';
}


if (empty($_POST['email'])){
     $Erreur[]='Veuillez saisir votre adresse mail';
}

// Verification Mail existance
$bd =  em_bd_connecter();
$emailV = mysqli_real_escape_string($bd, $email);
$sql = "SELECT cliEmail FROM clients WHERE cliEmail = '{$emailV}'";
$res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
while($tab = mysqli_fetch_assoc($res)) {
	if ($tab['cliEmail'] == $email){
        $Erreur[]='Mail deja utilise';
    }
}

mysqli_free_result($res);
mysqli_close($bd);

// New utulisateur


if(count($Erreur) == 0){
	$bd =  em_bd_connecter();
    $passe = password_hash($passe1, PASSWORD_DEFAULT);

    if ($m < 10) {
        $m = '0' . $mois;   
    }
    if ($jour < 10) {
        $jour = '0' . $jour;   
    }
    $CP=0;

    $sql = "INSERT INTO clients(cliEmail, cliPassword, cliNomPrenom, cliAdresse, cliCP, cliVille, cliPays, cliDateNaissance) 
            VALUES ('{$emailV}', '{$passe}','{$nomprenom}', '','{$CP}', '', '', {$jour}{$mois}{$annee})";
    
    mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    mysqli_close($bd);
    echo '<h2>Reception du formulaire</h2>';
    echo '<h2>Inscription utilisateur</h2>';
    echo '<p>Un nouvel utilisateur a bien été enregistré</p>';
   
}
ob_end_flush();
em_aff_fin('main');
?>





