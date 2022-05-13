<?php

require_once '../php/bibli_generale.php';
require_once ('../php/bibli_bookshop.php');

// bufferisation des sorties
ob_start();

// si formulaire soumis, traitement de la demande d'inscription
if (isset($_POST['btnSInscrire'])) {
    $Erreur = hk_traitement_inscription();
    
}
else{
    $Erreur = FALSE;
}

// génération de la page
em_aff_debut('BookShop | Inscription', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete(true);

hk_aff_formulaire($Erreur);

em_aff_pied();

em_aff_fin('main');

ob_end_flush(); //FIN DU SCRIPT

function hk_aff_liste_nombre($name,$nb1,$nb2,$pas,$selected){
    echo '<select name=',$name,'>';
    if($pas==true){
        for($i=$nb1;$i<=$nb2;$i++){
            if ($i==$selected){
                echo '<option value="',$i,'" selected>',$i,'</option>';
            }else{
                echo '<option value="',$i,'">',$i,'</option>';
            }        
        }  
    }else{
        for($i=$nb2;$i>=$nb1;$i--){
            if ($i==$selected){
                echo '<option value="',$i,'" selected>',$i,'</option>';
            }else{
                echo '<option value="',$i,'">',$i,'</option>';
            }        
        }  
    }
    echo '</select>';

}

function hk_aff_liste($name,$table,$selected){
    echo '<select name=',$name,'>';
    $i=1;
    foreach($table as $key => $value){
        if($i==$selected){
            echo '<option value="',$key,'" selected>',$value,'</option>';
        }else{
            echo '<option value="',$key,'">',$value,'</option>';
        }
        $i++;
    }



    echo '</select>';
}

function hk_aff_mois($name,$selected){
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

    hk_aff_liste($name,$mois,$selected);
}


function hk_aff_formulaire($Errs){

    $anneeCourante = (int) date('Y');

    // affectation des valeurs à afficher dans les zones du formulaire
    if (isset($_POST['btnSInscrire'])){
        $nomprenom = hk_protection(trim($_POST['nomprenom']));
        $email = hk_protection(trim($_POST['email']));
        $jour = (int)$_POST['naissance_j'];
        $mois = (int)$_POST['naissance_m'];
        $annee = (int)$_POST['naissance_a'];
    }
    else{
        $nomprenom = $email = '';
        $jour = $mois = 1;
        $annee = $anneeCourante;
    }    

    echo
        '<h2>Inscription à Bookshop</h2>',
        '<form method="post" action="inscription_4.php">';


    if ($Errs) {
        echo '<div class="erreur">Votre inscription n\'a pas pu être réalisée à cause des erreurs suivantes :<ul>';
        foreach ($Errs as $erreur) {
            echo '<li>', $erreur, '</li>';   
        }
        echo '</ul></div>';
    }


        echo
            'Pour vous inscrire, remplissez le formulaire ci-dessous:',  
            '<table>',
                '<tr>',
                    '<td>Votre adresse email</td>',
                    '<td>',
                        '<input type="email" name="email" value="">',
                    '</td>',
                '</tr>',
                '<tr>',
                    '<td>Choisissez un mot de passe</td>',
                    '<td>',
                        '<input type=password name="passe1" value="">',
                    '</td>',
                '</tr>',  
                '<tr>',
                    '<td>Répétez le mot de passe</td>',
                    '<td>',
                        '<input type=password name="passe2" value="">',
                    '</td>',
                '</tr>',   
                '<tr>',
                    '<td>Nom et prénom</td>',
                    '<td>',
                        '<input type=text name="nomprenom" value="">',
                    '</td>',
                '</tr>',
                '<tr>',
                    '<td>Votre date de naissance</td>',
                    '<td>',
                    hk_aff_liste_nombre("naissance_j",1,31,true, $jour),
                    hk_aff_mois("naissance_m",$mois),
                    hk_aff_liste_nombre("naissance_a",($anneeCourante-119),$anneeCourante,false,$anneeCourante),
                    '</td>',
                '</tr>',
                '<tr>',
                    '<td>',
                        '<input type="submit" name="btnSInscrire" value="S\'inscrire">',
                        '<input type="reset" name="btnReset" value="Réinitialiser">',
                    '</td>',
                '</tr>',
               
            '</table>';
}



function hk_traitement_inscription(){
    echo '<pre>';
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
 
    /*---------------Retourner les erreurs-----------------*/

    if (count($Errs) > 0) {
        return $Errs;   //===> FIN DE LA FONCTION
    }

    /* -------------------------------------------------------------*/

    /*---------------Enregistrement nouvel user-----------------*/

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
        exit();
    
}






?>