<?php
/** 1ère version : liste des livres */

ob_start(); //démarre la bufferisation

require_once '../php/bibli_generale.php';
require_once '../php/bibli_bookshop.php';

error_reporting(E_ALL); // toutes les erreurs sont capturées (utile lors de la phase de développement)

$bd = em_bd_connecter();

$sql = 'SELECT  liID, liTitre, liPrix, liPages, liISBN13, edNom, edWeb 
        FROM    livres, editeurs
        WHERE liIDEditeur = edID';

$res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

em_aff_debut('BookShop | Recherche');

while ($t = mysqli_fetch_assoc($res)) {
    echo '<p> Livre #', $t['liID'], '</p>',
        '<ul>',
            '<li><strong>', em_html_proteger_sortie($t['liTitre']), '</strong></li>',
            '<li>Edit&eacute; par : <a href="http://', em_html_proteger_sortie(trim($t['edWeb'])), '" target="_blank">', 
            em_html_proteger_sortie($t['edNom']), '</a></li>',
            '<li>Prix : ', $t['liPrix'], '&euro;</li>', 
            '<li>Pages : ', $t['liPages'], '</li>',
            '<li>ISBN13 : ', em_html_proteger_sortie($t['liISBN13']), '</li>',
        '</ul>';
}

// libération des ressources
mysqli_free_result($res);
mysqli_close($bd);

em_aff_fin();

// fin du script --> envoi de la page 
ob_end_flush();



?>
