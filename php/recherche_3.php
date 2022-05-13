<?php
/**
3ème version :  afficher la liste des livres d'un auteur dont le nom est passé dans l'URL.
Exemple : pour obtenir les livres écrits par Alan Moore, il faut utiliser l'URL :
recherche_3.php?type=auteur&quoi=Moore
*/

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

// nom de l'auteur dont on recherche les livres
$quoi = '';

if (! em_parametres_controle('get', array('type', 'quoi'))){
    $erreurs[] = 'L\'URL doit être de la forme "recherche_3.php?type=auteur&quoi=Moore".';
}
else{
    if ($_GET['type'] != 'auteur'){
        $erreurs[] = 'La valeur du "type" doit être égale à "auteur".';
    }
    $quoi = trim($_GET['quoi']);
    $l1 = mb_strlen($quoi, 'UTF-8');
    if ($l1 != mb_strlen(strip_tags($quoi), 'UTF-8')){
        $erreurs[] = 'Le nom de l\'auteur ne doit pas contenir de tags HTML.';
    }
}

/*------------------------- Etape 2 --------------------------------------------
- génération du code HTML de la page
------------------------------------------------------------------------------*/

em_aff_debut('BookShop | Recherche', '../styles/bookshop.css', 'main');

em_aff_enseigne_entete(true);

eml_aff_contenu($quoi, $erreurs);

em_aff_pied();

em_aff_fin('main');

// fin du script --> envoi de la page 
ob_end_flush();


// ----------  Fonctions locales au script ----------- //

/**
 *  Contenu de la page : résultats de la recherche, ou erreurs
 *
 * @param string $quoi      nom de l'auteur recherché
 * @param array  $erreurs   erreurs détectées dans l'URL
 */
function eml_aff_contenu($quoi, $erreurs) {
    
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

    // ouverture de la connexion, requête
    $bd = em_bd_connecter();
    
    $q = em_bd_proteger_entree($bd, $quoi); 
    
    $sql = "SELECT liID, liTitre, liPrix, liPages, liISBN13, edNom, edWeb, auNom, auPrenom 
            FROM livres INNER JOIN editeurs ON liIDEditeur = edID 
                        INNER JOIN aut_livre ON al_IDLivre = liID 
                        INNER JOIN auteurs ON al_IDAuteur = auID 
            WHERE liID in (SELECT al_IDLivre FROM aut_livre INNER JOIN auteurs ON al_IDAuteur = auID WHERE auNom = '$q')
            ORDER BY liID";

    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd,$sql);
    
    echo '<h3>Livre(s) écrit(s) par l\'auteur de nom "', em_html_proteger_sortie($quoi), '"</h3>'; 
    
    $lastID = -1;
    while ($t = mysqli_fetch_assoc($res)) {
        if ($t['liID'] != $lastID) {
            if ($lastID != -1) {
                eml_aff_livre($livre); 
            }
            $lastID = $t['liID'];
            $livre = array( 'id' => $t['liID'], 
                            'titre' => $t['liTitre'],
                            'edNom' => $t['edNom'],
                            'edWeb' => $t['edWeb'],
                            'pages' => $t['liPages'],
                            'ISBN13' => $t['liISBN13'],
                            'prix' => $t['liPrix'],
                            'auteurs' => array(array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']))
                        );
        }
        else {
            $livre['auteurs'][] = array('prenom' => $t['auPrenom'], 'nom' => $t['auNom']);
        }       
    }
    // libération des ressources
    mysqli_free_result($res);
    mysqli_close($bd);
    
    if ($lastID != -1) {
        eml_aff_livre($livre); 
    }
    else{
        echo '<p>Aucun livre trouvé</p>';
    }
    

        
}   
    
/**
 *  Affichage d'un livre.
 *
 *  @param  array       $livre      tableau associatif des infos sur un livre (id, auteurs(nom, prenom), titre, prix, pages, ISBN13, edWeb, edNom)
 *
 */
function eml_aff_livre($livre) {
    // Le nom de l'auteur doit être encodé avec urlencode() avant d'être placé dans une URL, sans être passé auparavant par htmlentities()
    $auteurs = $livre['auteurs'];
    $livre = em_html_proteger_sortie($livre);
    echo 
        '<article class="arRecherche">', 
            // TODO : à modifier pour le projet  
            '<a class="addToCart" href="#" title="Ajouter au panier"></a>',
            '<a class="addToWishlist" href="#" title="Ajouter à la liste de cadeaux"></a>',
            '<a href="details.php?article=', $livre['id'], '" title="Voir détails"><img src="../images/livres/', $livre['id'], '_mini.jpg" alt="', 
            $livre['titre'],'"></a>',
            '<h5>', $livre['titre'], '</h5>',
            'Ecrit par : ';
    $i = 0;
    foreach ($auteurs as $auteur) {
        echo $i > 0 ? ', ' : '', '<a href="recherche.php?type=auteur&amp;quoi=', urlencode($auteur['nom']), '">',
        em_html_proteger_sortie($auteur['prenom']), ' ', em_html_proteger_sortie($auteur['nom']) ,'</a>';
        $i++;
    }
            
    echo    '<br>Editeur : <a class="lienExterne" href="http://', trim($livre['edWeb']), '" target="_blank">', $livre['edNom'], '</a><br>',
            'Prix : ', $livre['prix'], ' &euro;<br>',
            'Pages : ', $livre['pages'], '<br>',
            'ISBN13 : ', $livre['ISBN13'], 
        '</article>';
}

?>
