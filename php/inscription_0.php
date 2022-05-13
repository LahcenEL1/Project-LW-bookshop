<?php

/* ------------------------------------------------------------------------------
    Architecture de la page
    - étape 1 : vérification des paramètres reçus dans l'URL
    - étape 2 : génération du code HTML de la page
------------------------------------------------------------------------------*/

ob_start(); //démarre la bufferisation

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

/*------------------------- Etape 1 --------------------------------------------
- vérification des paramètres reçus dans l'URL
------------------------------------------------------------------------------*/

// erreurs détectées dans l'URL
$erreurs = array();

// critères de recherche
$recherche = array('type' => 'auteur', 'quoi' => '');

if ($_GET){ // s'il y a des paramètres dans l'URL
    if (! em_parametres_controle('get', array('type', 'quoi'))){
        $erreurs[] = 'L\'URL doit être de la forme "recherche.php?type=auteur&quoi=Moore".';
    }
    else{
        $oks = array('titre', 'auteur');
        if (! in_array($_GET['type'], $oks)){
            $erreurs[] = 'La valeur du "type" doit être égale à "'.implode('" ou à "', $oks).'".';
        }
        $recherche['type'] = $_GET['type'];
        $recherche['quoi'] = trim($_GET['quoi']);
        $l1 = mb_strlen($recherche['quoi'], 'UTF-8');
        if ($l1 < 2){
            $erreurs[] = 'Le critère de recherche est trop court.';
        }
        if ($l1 != mb_strlen(strip_tags($recherche['quoi']), 'UTF-8')){
            $erreurs[] = 'Le critère de recherche ne doit pas contenir de tags HTML.';
        }
    }
}

/*------------------------- Etape 2 --------------------------------------------
- génération du code HTML de la page
------------------------------------------------------------------------------*/

em_aff_debut('BookShop | Recherche', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete(true);

eml_aff_contenu($recherche, $erreurs);

em_aff_pied();

em_aff_fin('main');

// fin du script --> envoi de la page 
ob_end_flush();

function le_aff_liste_nombre($name,$min,$max,$pas,$valeur){
    if($pas==true){
        for($i=$min;$i<=$max;$i++){
            if ($i==$valeur){
                echo '<option value="',$i,'" valeur>',$i,'</option>';
            }else{
                echo '<option value="',$i,'">',$i,'</option>';
            }        
        }  
    }else{
        for($i=$max;$i>=$min;$i--){
            if ($i==$valeur){
                echo '<option value="',$i,'" valeur>',$i,'</option>';
            }else{
                echo '<option value="',$i,'">',$i,'</option>';
            }        
        }  
    }

}

function le_aff_liste($name,$table,$selected){
    $i=1;
    foreach($table as $key => $value){
        if($i==$selected){
            echo '<option value="',$key,'" selected>',$value,'</option>';
        }else{
            echo '<option value="',$key,'">',$value,'</option>';
        }
        $i++;
    }
}
function le_aff_mois($name,$selected){
    $mois=array('1' => 'Janvier',
                '2' => 'Fevrier',
                '3' => 'Mars',
                '4' => 'Avril',
                '5' => 'Mai',
                '6' => 'Juin',
                '7' => 'Juillet',
                '8' => 'Aout',
                '9' => 'Septembre',
                '10' => 'Octobre',
                '11' => 'Novembre',
                '12' => 'Decembre'
    );

    le_aff_liste($name,$mois,$selected);
}







// ----------  Fonctions locales au script ----------- //

/**
 *  Contenu de la page : formulaire et résultats de la recherche
 *
 * @param array  $recherche     critères de recherche (type et quoi)
 * @param array  $erreurs       erreurs détectées dans l'URL
 */
function eml_aff_contenu($recherche, $erreurs) {

    
    
    
    /* choix de la méthode get pour avoir la même forme d'URL lors d'une soumission du formulaire, 
    et lorsqu'on accède à la page suite à un clic sur un nom d'un auteur */
    echo '<form action="inscription.php" method="post">',
            '<h1>Inscription A bookshop</h1>',
            '<p>Pour vous inscrire,merci de fournir les informations suivantes<p>',

            '<CENTER>',
            '<table>',


            '<tr >',
                '<td>', 
                    '<label> Votre adresse email</label>',
                '</td>',
                '<td>',
                    '<input type="email" name="Mail" >',
                '</td>',


            '</tr>',

            '<tr>',
                '<td>', 
                    '<label> Mot de passe</label>',
                '</td>',
                '<td>',
                    '<input type="password" name="passe1" >',
                '</td>',
            '</tr>',


            '<tr>',
                 '<td>', 
                    '<label> Repeter le mot de passe</label>',
                '</td>',
                '<td>',
                    '<input type="password" name="passe2" border-radius=>',
                '</td>',
            '</tr>',
                 
            '<tr>',
                '<td>', 
                    '<label> Nom et Prenom</label>',
                '</td>',
                '<td>',
                    '<input type="text" name="nomprenom" >',
                '</td>',
            '</tr>',

            '<tr>',
                '<td>', 
                    '<label> Date de naissances</label>',
                '</td>',
                '<td>',
                    '<select>';
                        le_aff_liste_nombre("naissance_j",1,31,false,27);                    
                    echo '</select>', 
                    '<select name="naissance_m" >';
                         le_aff_mois("naissance_m",1);                                     
                    echo '</select>', 
                    '<select>';
                    le_aff_liste_nombre("naissance_a",(date('Y')-120),date('Y'),false,date('Y'));
                     
                   echo '</select>', 

                '</td>',

            '</tr>',

            '<tr>',
                
                '<td colspan="2" align=CENTER>',
                    '<input type="submit" name="btnSInscrire" value="Sinscrire" >',
               
                    '<input type="reset"  value="Réinitialiser" >',
                '</td>',

            '</tr>',

           


           
            '</table>',
            '</CENTER>',
          '</form>';
    
    if ($erreurs) {
        $nbErr = count($erreurs);
        $pluriel = $nbErr > 1 ? 's':'';
        echo '<p class="error">',
                '<strong>Erreur',$pluriel, ' détectée', $pluriel, ' :</strong>';
        for ($i = 0; $i < $nbErr; $i++) {
                echo '<br>', $erreurs[$i];
        }
        echo '</p>';
        return; // ===> Fin de la fonction
    }

    
}




?>