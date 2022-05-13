<?php

require_once '../php/bibli_generale.php';
require_once ('../php/bibli_bookshop.php');

em_aff_debut('BookShop | Inscription', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete(true);


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



echo '<form method="post" action="inscription_3.php">',
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
                hk_aff_liste_nombre("naissance_j",1,31,true,27),
                hk_aff_mois("naissance_m",1),
                hk_aff_liste_nombre("naissance_a",(date('Y')-119),date('Y'),false,date('Y')),
                '</td>',
            '</tr>',
            '<tr>',
                '<td>',
                    '<input type="submit" name="btnSInscrire" value="S\'inscrire">',
                    '<input type="reset" name="btnReset" value="Réinitialiser">',
                '</td>',
            '</tr>',
               
        '</table>';        

em_aff_pied();

em_aff_fin('main');


?>