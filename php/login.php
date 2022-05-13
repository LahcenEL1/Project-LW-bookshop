<?php
/* ------------------------------------------------------------------------------
    Architecture de la page
    - étape 1 : vérifications diverses et traitement des soumissions
    - étape 2 : génération du code HTML de la page
------------------------------------------------------------------------------*/

ob_start(); //démarre la bufferisation
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

// si l'utilisateur est pas authentifié, on le redirige sur la page login.php
if (em_est_authentifie()){
    header('Location: ../index.php');
    exit;
}


error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

/*------------------------- Etape 1 --------------------------------------------
- vérifications diverses et traitement des soumissions
------------------------------------------------------------------------------*/
// traitement si soumission du formulaire d'inscription
$err = isset($_POST['btnConnecter']) ? eml_traitement_connexion() : array(); 


/*------------------------- Etape 2 --------------------------------------------
- génération du code HTML de la page
------------------------------------------------------------------------------*/

em_aff_debut('BookShop | Connexion', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete();

eml_aff_contenu($err);

em_aff_pied();

em_aff_fin('main');

ob_end_flush();


// ----------  Fonctions locales du script ----------- //

/**
 * Affichage du contenu de la page (formulaire d'inscription)
 *
 * @param   array   $err    tableau d'erreurs à afficher
 * @global  array   $_POST
 */
function eml_aff_contenu($err) {

    // réaffichage des données soumises en cas d'erreur, sauf les mots de passe
    $email = isset($_POST['email']) ? em_html_proteger_sortie(trim($_POST['email'])) : '';
       
    echo 
        '<h1>Connexion à BookShop</h1>';
        
    if (count($err) > 0) {
        echo '<p class="error">Connexion impossible. ';
        foreach ($err as $v) {
            echo '<br>', $v;
        }
        echo '</p>';    
    }


    echo    
        '<form method="post" action="login.php">',
            '<table>';

    em_aff_ligne_input('Adresse email :', array('type' => 'email', 'name' => 'email', 'value' => $email, 'required' => false));
    em_aff_ligne_input('Mot de passe :', array('type' => 'password', 'name' => 'passe', 'value' => '', 'required' => false));
          
    echo 
                '</table>',
                    '<center>',
                        '<input type="submit" name="btnConnecter" value="Se connecter">',
                        '</form><br><br>',
                        '<form action="inscription.php" method="get">',
                        '<input type="submit" name="btnInscription" value="S\'inscrire"></form>',
                    '</center>';
            
        
}   


/**
 *  Traitement de l'inscription 
 *
 *      Etape 1. vérification de la validité des données
 *                  -> return des erreurs si on en trouve
 *      Etape 2. enregistrement du nouvel inscrit dans la base
 *      Etape 3. ouverture de la session et redirection vers la page protegee.php 
 *
 * Toutes les erreurs détectées qui nécessitent une modification du code HTML sont considérées comme des tentatives de piratage 
 * et donc entraînent l'appel de la fonction em_session_exit() sauf les éventuelles suppressions des attributs required 
 * car l'attribut required est une nouveauté apparue dans la version HTML5 et nous souhaitons que l'application fonctionne également 
 * correctement sur les vieux navigateurs qui ne supportent pas encore HTML5
 *
 * @global array    $_POST
 *
 * @return array    tableau assosiatif contenant les erreurs
 */
$row1;

function eml_traitement_connexion() {

    global $row1;

    if( !em_parametres_controle('post', array('email', 'passe', 'btnConnecter'))) {
        em_session_exit();   
    }
    
    $erreurs = array();
    $donnes = array();
    // vérification du format de l'adresse email
    $email = trim($_POST['email']);
    if (empty($email)){
        $erreurs[] = 'L\'adresse mail ne doit pas être vide.'; 
    }
    else {
        $bd = em_bd_connecter();
        $sql = "SELECT * FROM `clients` WHERE cliEmail = '$email'";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        $row1 = mysqli_fetch_assoc($res);
        if (empty($row1)){
            $erreurs[] = 'Cette adresse mail n\'est pas dans notre base de données.'; 
        }
        else{
            // vérification des mots de passe
            $passe = trim($_POST['passe']);
            if(!password_verify($passe,$row1['cliPassword'])){
                $erreurs[] = 'Mot de passe erroné';
            }
        }
    }
    
    

    
   

    if (count($erreurs) == 0) {
        // mémorisation de l'ID dans une variable de session 
        // cette variable de session permet de savoir si le client est authentifié
        $_SESSION['id'] = $row1['cliID'];
        
        // libération des ressources
        mysqli_close($bd);

        // redirection vers la page protegee.php
        header('Location:' . $_SERVER['HTTP_REFERER']); //TODO : à modifier dans le projet
        exit();
        
    }
    
    // s'il y a des erreurs ==> on retourne le tableau d'erreurs    
    if (count($erreurs) > 0) {  
        return $erreurs;    
    }

    
    
}
    

?>
