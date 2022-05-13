<?php

/* ------------------------------------------------------------------------------
    Architecture de la page
    - étape 1 : Connexion a la base de donnees
    - étape 2 : Affichage des donnees
------------------------------------------------------------------------------*/

ob_start(); //démarre la bufferisation
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

em_aff_debut('BookShop | Compte', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete();

// si l'utilisateur n'est pas authentifié, on le redirige sur la page login.php
if (! em_est_authentifie()){
    header('Location: login.php');
    exit;
}

// traitement si soumission de modification
$err = isset($_POST['btnUpdate']) ? eml_traitement_modification() :  array();

$bd = em_bd_connecter();

$IDClient = $_SESSION['id'];

eml_aff_contenu($err);


function eml_aff_contenu($err) {

    global $IDClient;
    global $bd;
    
    
   

    echo 
        '<h1>Informations Utilisateur</h1>';
    
    
    if (count($err) > 0) {
        echo '<p class="error">La modification n\'a pas pu être réalisée à cause des erreurs suivantes : ';
        foreach ($err as $v) {
            echo '<br> - ', $v;
        }
        echo '</p>';    
    }

    $sql = "SELECT * FROM clients WHERE cliID = $IDClient";
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    $row = mysqli_fetch_assoc($res);

    // réaffichage des données soumises en cas d'erreur, sauf les mots de passe
    $email = isset($_POST['email']) ? em_html_proteger_sortie(trim($_POST['email'])) : $row["cliEmail"];    
    $nomprenom = isset($_POST['nomprenom']) ? em_html_proteger_sortie(trim($_POST['nomprenom'])) : $row["cliNomPrenom"];
    $adresse = isset($_POST['address']) ? em_html_proteger_sortie(trim($_POST['address'])) : $row["cliAdresse"];
    $code = isset($_POST['cp']) ? em_html_proteger_sortie(trim($_POST['cp'])) : $row["cliCP"];
    $ville = isset($_POST['ville']) ? em_html_proteger_sortie(trim($_POST['ville'])) : $row["cliVille"];
    $pays = isset($_POST['pays']) ? em_html_proteger_sortie(trim($_POST['pays'])) : $row["cliPays"];

    //calcul de la date de Naissance
    $aa = intdiv(intval($row["cliDateNaissance"]),10000);
    $mm = intdiv(intval($row["cliDateNaissance"])-$aa*10000,100);
    $jj = intval($row["cliDateNaissance"])-$aa*10000-$mm*100;

    echo 
        '<form method="post" action="compte.php" style="margin: 10px 0 0 20px">',
            '<label for="email" >Email: </label>',
            '<input type="text" id="email" name="email" value="'.$email.'"><br><br>',
            '<label for="mdp" >Mot de passe: </label>',
            '<input type="password" id="mdp" name="mdp" value="..........""><br><br>',
            '<label for="nomprenom" >Nom et Prenom: </label>',
            '<input type="text" id="nomprenom" name="nomprenom" value="'.$nomprenom.'"><br><br>',
            '<label for="date" >Date de Naissance: </label>',
            '<input type="text" id="date" name="date"  value="'.$jj.'/'.$mm.'/'.$aa.'" readonly><br><br>',
            '<label for="address" >Adresse: </label>',
            '<input type="text" id="address" name="address" value="'.$adresse.'"><br><br>',
            '<label for="cp" >Code Postal: </label>',
            '<input type="text" id="cp" name="cp" value="'.$code.'"><br><br>',
            '<label for="ville" >Ville: </label>',
            '<input type="text" id="ville" name="ville" value="'.$ville.'"><br><br>',
            '<label for="pays" >Pays: </label>',
            '<input type="text" id="pays" name="pays" value="'.$pays.'">',
            '<br><br><input type="submit" name="btnUpdate" value="Modifier">',
        '</form><br>',
        '<center><form action="recap_commande.php" method="get">',
                        '<input type="submit" name="btnRecap" value="Commandes"></form><center>';   
}


function eml_traitement_modification() {
    
    $erreurs = array();
    global $IDClient;
    global $bd;
    $change_mdp = true;

    // vérification du format de l'adresse email
    $email = trim($_POST['email']);
    if (empty($email)){
        $erreurs[] = 'L\'adresse mail ne doit pas être vide.'; 
    }
    else {
        if (mb_strlen($email, 'UTF-8') > LMAX_EMAIL){
            $erreurs[] = 'L\'adresse mail ne peut pas dépasser '.LMAX_EMAIL.' caractères.';
        }
        // la validation faite par le navigateur en utilisant le type email pour l'élément HTML input
        // est moins forte que celle faite ci-dessous avec la fonction filter_var()
        // Exemple : 'l@i' passe la validation faite par le navigateur et ne passe pas
        // celle faite ci-dessous
        if(! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'L\'adresse mail n\'est pas valide.';
        }
    }
    
    // vérification des mots de passe
    //on verifie uniquement si le mot de passe a ete modifie
    $passe = trim($_POST['mdp']);
    if($passe != ".........."){
        $nb = mb_strlen($passe, 'UTF-8');
        if ($nb < LMIN_PASSWORD || $nb > LMAX_PASSWORD){
        $erreurs[] = 'Le mot de passe doit être constitué de '. LMIN_PASSWORD . ' à ' . LMAX_PASSWORD . ' caractères.';
        }
    }
    else{
        $change_mdp = false;
    }
    

    
    // vérification des noms et prenoms
    $nomprenom = trim($_POST['nomprenom']);
    
    if (empty($nomprenom)) {
        $erreurs[] = 'Le nom et le prénom doivent être renseignés.'; 
    }
    else {
        if (mb_strlen($nomprenom, 'UTF-8') > LMAX_NOMPRENOM){
            $erreurs[] = 'Le nom et le prénom ne peuvent pas dépasser ' . LMAX_NOMPRENOM . ' caractères.';
        }
        $noTags = strip_tags($nomprenom);
        if ($noTags != $nomprenom){
            $erreurs[] = 'Le nom et le prénom ne peuvent pas contenir de code HTML.';
        }
        else {
            mb_regex_encoding ('UTF-8'); //définition de l'encodage des caractères pour les expressions rationnelles multi-octets
            if( !mb_ereg_match('^[[:alpha:]]([\' -]?[[:alpha:]]+)*$', $nomprenom)){
                $erreurs[] = 'Le nom et le prénom contiennent des caractères non autorisés';
            }
        }
    }

    $adresse = trim($_POST['address']);
    $codePostal = trim($_POST['cp']);
    $ville = trim($_POST['ville']);
    $pays = trim($_POST['pays']);

    

   
    
    // s'il y a des erreurs ==> on retourne le tableau d'erreurs    
    if (count($erreurs) > 0) {  
        return $erreurs;    
    }
    
    // pas d'erreurs ==> enregistrement de l'utilisateur
    $bd = em_bd_connecter();
    $IDClient = $_SESSION['id'];

    $nomprenom = em_bd_proteger_entree($bd, $nomprenom);
    
    $passe = password_hash($passe, PASSWORD_DEFAULT);
    $passe = em_bd_proteger_entree($bd, $passe);
    

    
    $sql = "UPDATE clients
            SET cliEmail='$email', cliNomPrenom='$nomprenom',cliAdresse='$adresse',cliCP=$codePostal,cliVille='$ville',cliPays='$pays'
            WHERE cliID=$IDClient";
            
    mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

    if($change_mdp){ //si le mot de passe a ete modifie
        $sql = "UPDATE clients
            SET cliPassword='$passe'
            WHERE cliID=$IDClient";
    
        mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    }
    
    
    // libération des ressources
    mysqli_close($bd);
    
    // redirection vers la page protegee.php
    header('Location: protegee.php'); //TODO : à modifier dans le projet
    exit();
}

em_aff_pied();

em_aff_fin('main');

?>