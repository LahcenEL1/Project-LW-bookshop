<?php
/** 2ème version : liste des livres, résumé et des auteurs */

ob_start(); //démarre la bufferisation

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

$bd = em_bd_connecter();

$sql = 'SELECT liID, liTitre, liPrix, liPages, liResume, liISBN13, edNom, edWeb, auNom, auPrenom
        FROM livres, editeurs, auteurs, aut_livre
        WHERE liIDEditeur = edID
        AND liID = al_IDLivre
        AND auID = al_IDAuteur
        ORDER BY liID';

$res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

em_aff_debut('BookShop | Recherche');

$lastID = -1;
while ($t = mysqli_fetch_assoc($res)) {
    if ($t['liID'] != $lastID) {
        if ($lastID != -1) {
            eml_aff_livre(em_html_proteger_sortie($livre)); 
        }
        $lastID = $t['liID'];
        $livre = array( 'id' => $t['liID'], 
                        'titre' => $t['liTitre'],
                        'edNom' => $t['edNom'],
                        'edWeb' => $t['edWeb'],
                        'resume' => $t['liResume'],
                        'pages' => $t['liPages'],
                        'ISBN13' => $t['liISBN13'],
                        'prix' => $t['liPrix'],
                        'auteurs' => array(array('prenom' => $t['auPrenom'], 'nom' => $t['auNom'])));
    }
    else {
        $livre['auteurs'][] = array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']);
    }       
}
// libération des ressources
mysqli_free_result($res);
mysqli_close($bd);

if ($lastID != -1) {
    eml_aff_livre(em_html_proteger_sortie($livre)); 
}

em_aff_fin();

// fin du script --> envoi de la page 
ob_end_flush();

/**
 *  Affichage d'un livre.
 *
 * @param   array   $t  tableau associatif des infos sur le livre (id, auteurs(nom, prenom), titre, prix, pages, ISBN13, edWeb, edNom, resume)
 */
function eml_aff_livre($t) {
    echo 
        '<p style="margin-top: 30px;">', 
            '<img src="../images/livres/', $t['id'], 
                '_mini.jpg" style="float: left; margin: 0 10px 10px 0; border: solid 1px #000; height: 100px;" alt="',
                $t['titre'], '">',
            '<strong>', $t['titre'], '</strong> <br>',
            'Ecrit par : ';
    $i = 0;
    foreach ($t['auteurs'] as $auteur) {
        if ($i > 0) {
            echo ', ';
        }
        $i++;
        echo $auteur['prenom'], ' ', $auteur['nom'];
    }
            
    echo    '<br>Editeur : <a href="http://', trim($t['edWeb']), '" target="_blank">', 
                $t['edNom'], '</a><br>',
            'Prix : ', $t['prix'], '<br>',
            'Pages : ', $t['pages'], '<br>',
            'ISBN13 : ', $t['ISBN13'], '<br>',
            'R&eacute;sum&eacute; : <em>', $t['resume'], '</em>', 
        '</p>';
        
}


?>
