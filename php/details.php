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

em_aff_debut('BookShop | Details', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete();

eml_aff_contenu();

function eml_aff_contenu() {

    echo 
        '<h1>Details du livre</h1>';

        $livId = $_GET['article'];
        $bd = em_bd_connecter();
        
        $sql = "SELECT * FROM livres JOIN editeurs ON liIDEditeur = edID WHERE liID = $livId";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        $row = mysqli_fetch_assoc($res);
        echo 
            '<table style="margin-right: 20px;
            margin-left: 20px;">',
                '<tr>',
                    '<td>ID: </td>',
                    '<td>', $row["liID"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>Editeur: </td>',
                    '<td>', $row["edNom"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>Titre: </td>',
                    '<td>', $row["liTitre"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>Pages: </td>',
                    '<td>', $row["liPages"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>Annee: </td>',
                    '<td>', $row["liAnnee"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>Prix: </td>',
                    '<td>', $row["liPrix"],'€ </td>',
                '</tr>',
                '<tr>',
                    '<td>Resume: </td>',
                    '<td>', $row["liResume"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>Langue: </td>',
                    '<td>', $row["liLangue"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>ISBN13: </td>',
                    '<td>', $row["liISBN13"],'</td>',
                '</tr>',
                '<tr>',
                    '<td>Categorie: </td>',
                    '<td>', $row["liCat"],'</td>',
                '</tr>',
            '</table>',
            '<p style="text-align: center">
            <a href="liste.php?livre=', $livId,'">Ajouter à la liste</a>
            &nbsp &nbsp &nbsp &nbsp
            <a href="panier.php?livre=', $livId, '&action=1">Ajouter au panier</a>
            </p>'
            ;

    
    
    
    
}




em_aff_pied();

em_aff_fin('main');

?>