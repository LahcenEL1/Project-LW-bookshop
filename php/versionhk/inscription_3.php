<?php

require_once '../php/bibli_generale.php';
require_once ('../php/bibli_bookshop.php');

em_aff_debut('BookShop | Inscription2','main');
echo '<pre>';
echo    '<h2>Réception du formulaire<br>Inscription utilisateur</h2>';


if( !em_parametres_controle('post', array('nomprenom', 'naissance_j', 'naissance_m', 'naissance_a', 
                                            'passe1', 'passe2', 'email', 'btnSInscrire'))) {
    header('Location: ../index.php');
    exit();     
}

$Errs =array();

/* --------------Verif mot de passe---------------------*/
$passe1=trim($_POST['passe1']);
$passe2=trim($_POST['passe2']);
$passelen=strlen($passe1);


if ( $passelen < 4 || $passelen > 20 ){
    $Errs[]='Le mot de passe doit avoir entre 6 et 8 caractères';
}
else if ( $passe1 != $passe2){
        $Errs[]='Les 2 mot de passe doivent être identiques';
}
/* -------------------------------------------------------------*/



/* --------------Verif nom et prenom---------------------*/

$nomprenom = trim($_POST['nomprenom']);
 mb_regex_encoding ('UTF-8'); //définition de l'encodage des caractères pour les expressions rationnelles multi-octets
    if (empty($nomprenom)){
        $Errs[] = "Le champ nom et prenom ne doit pas être vide.";
    }
    else if(strip_tags($nomprenom) != $nomprenom){
        $Errs[] = "Le champ nom et prenom ne doit pas contenir de tags HTML";
    }
    elseif(!mb_ereg_match('^[[:alpha:]]([\' -]?[[:alpha:]]+)*$', $nomprenom)){
        $Errs[] = "Le champ nom et prenom contient des caractères non autorisés";
    }

/* -------------------------------------------------------------*/


/* --------------Verif date---------------------*/

$jour = (int)$_POST['naissance_j'];
$mois = (int)$_POST['naissance_m'];
$annee = (int)$_POST['naissance_a'];

if (! (is_int($jour) && hk_est_entre($jour, 1, 31))){
    header('Location: ../index.php');
    exit();   
}

if (! (is_int($mois) && hk_est_entre($mois, 1, 12))){
    header('Location: ../index.php');
    exit();
}

$anneeCourante = (int) date('Y');
if (! (is_int($annee) && hk_est_entre($annee, $anneeCourante  - 120, $anneeCourante))){
    header('Location: ../index.php');
    exit(); 
}

if (!checkdate($mois, $jour, $annee)) {
    echo $mois;
    echo $jour;
    echo $annee;
    $Errs[] = 'La date de naissance n\'est pas valide.';
}
else if (mktime(0,0,0,$mois,$jour,$annee+18) > time()) {
    $Errs[] = 'Vous devez avoir au moins 18 ans pour vous inscrire.'; 
}

/* -------------------------------------------------------------*/


/* --------------Verif email---------------------*/
$email = trim($_POST['email']);
if (empty($email)){
    $Errs[] = 'L\'adresse mail ne doit pas être vide.'; 
}
else if(! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $Errs[] = 'L\'adresse mail n\'est pas valide.';
}

// vérification de l'existence de l'email
if (count($Errs) == 0) {
    $bd =  em_bd_connecter();
    $emailV = mysqli_real_escape_string($bd, $email);
    $sql = "SELECT cliEmail FROM clients WHERE cliEmail = '{$emailV}'";
    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

    while($tab = mysqli_fetch_assoc($res)) {
        if ($tab['cliEmail'] == $email){
            $Errs[] = 'Cette adresse email est déjà inscrite.';
        }
    }

    mysqli_free_result($res);
    mysqli_close($bd);
}

/* -------------------------------------------------------------*/
 
/*---------------Afichage des erreurs-----------------*/

if (count($Errs) > 0) {
    foreach( $Errs as $value ){
        echo $value,"\n";
    }
    exit('</body></html>'); 
}

/* -------------------------------------------------------------*/

/*---------------Enregistrement nouvel user-----------------*/

if (count($Errs) == 0) {
    $bd =  em_bd_connecter();
    $passe = password_hash($passe1, PASSWORD_DEFAULT);

    if ($mois < 10) {
        $mois = '0' . $mois;   
    }
    if ($jour < 10) {
        $jour = '0' . $jour;   
    }
    $CP=0;

    $sql = "INSERT INTO clients(cliEmail, cliPassword, cliNomPrenom, cliAdresse, cliCP, cliVille, cliPays, cliDateNaissance) 
            VALUES ('{$emailV}', '{$passe}','{$nomprenom}', '','{$CP}', '', '', {$jour}{$mois}{$annee})";
    
    mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

    mysqli_close($bd);

    echo '<p>Un nouvel utilisateur a bien été enregistré</p>';
}
    ob_end_flush();

/* -------------------------------------------------------------*/

echo '</pre>';
em_aff_fin('main');

function hk_est_entre($nb,$min,$max){
    if ($nb<=$min || $nb>=$min){
        return true;
    }
    return false;
}
?>
