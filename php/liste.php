<?php

ob_start(); //démarre la bufferisation
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

em_aff_debut('BookShop | Liste de souhaits', '../styles/bookshop.css', 'main');
em_aff_enseigne_entete();


// si l'utilisateur n'est pas authentifié, on le redirige sur la page login.php
if (! em_est_authentifie()){
    header('Location: login.php');
    exit;
}


$liste_souhaits = array();
$message;
$bd = em_bd_connecter();

deTableAArray();


if(isset($_GET['livre'])){ //s'il y a une variable livre l'url => ajouter un livre dans la liste
    $livreID = $_GET['livre'];
    
    ajouter_livre($livreID);
    
}
else{
    if(isset($_GET['index'])){
        supprimer_livre($_GET['index']);
    }
}

actualiser_baseDonnees();





function deTableAArray(){ //récupère le contenu de la table listes et le met dans l'array liste_souhaits
    global $bd;
    $sql = "SELECT listIDLivre, liTitre, liPrix
            FROM listes as lis
            JOIN livres as liv ON lis.listIDLivre = liv.liID
            WHERE lis.listIDClient = {$_SESSION['id']}";   
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);

    while($row = mysqli_fetch_assoc($res)){
        global $liste_souhaits;
        array_push($liste_souhaits,array(   'ID'      => $row["listIDLivre"],
                                            'Titre'      => $row["liTitre"],
                                            'Prix'      => $row["liPrix"]));
    }
}






function ajouter_livre($livreID){
    global $liste_souhaits;
    if (empty($liste_souhaits)){ //si la liste est vide, on l'initialise avec le livre en paramètre
        global $bd;
        $sql = "SELECT liTitre, liPrix FROM livres WHERE liID = ".$livreID."";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        $row = mysqli_fetch_assoc($res);
        $liste_souhaits = array(
            array(                      'ID'      => $livreID,
                                        'Titre'      => $row["liTitre"],
                                        'Prix'      => $row["liPrix"])
        );
    }
    else{
        ajouter_NouveauLivre($livreID); //si la liste n'est pas vide, on ajoute le nouveau livre
    }
    
    
}



function ajouter_NouveauLivre($livreID){
    $count = 0;
    global $liste_souhaits;
    global $message;
    foreach ($liste_souhaits as &$livre){ //si le livre est déjà dans la liste, on ne fait rien
        if($livre['ID']==$livreID){
            $message = "Ce livre fait déjà partie de votre liste.";
            $count++;
        }
    }
    if($count == 0){ //s'il n'est pas dans la liste on crée une ligne pour le nouveau livre
        global $bd;
        $sql = "SELECT liTitre, liPrix FROM livres WHERE liID = ".$livreID."";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        $row = mysqli_fetch_assoc($res);
        array_push($liste_souhaits,array(
                                            'ID'      => $livreID,
                                            'Titre'      => $row["liTitre"],
                                            'Prix'      => $row["liPrix"])
        );
        $message = "Le livre a bien été ajouté a votre liste.";
    }
}


function actualiser_baseDonnees(){
    global $liste_souhaits;
    global $bd;
    global $message;
    $sql = "DELETE FROM listes WHERE listIDClient = {$_SESSION['id']}";
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    foreach ($liste_souhaits as &$livre){
        $sql = "INSERT INTO listes (`listIDClient`, `listIDLivre`) VALUES ({$_SESSION['id']},".$livre["ID"].")";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    }
    if(empty($liste_souhaits)) $message = "Votre liste est vide";
    eml_aff_contenu();
    
}


function supprimer_livre($index){
    global $message;
    global $liste_souhaits;
    unset($liste_souhaits [$index]);
    $message = "Le livre a bien été supprimé de votre liste.";
}





function eml_aff_contenu(){
    global $liste_souhaits;
    global $message;
    echo
        '<style>
        table, th, td {
        text-align: center;
        border: 1px solid;
        vertical-align: middle;
        padding: 5px;
        }
        table {
        border-spacing: 15px;
        margin-left: auto;
        margin-right: auto;
        }
        </style>

        <h1 style="text-align:center;">Votre liste de souhaits</h1>
        <p style="text-align:center;">';
    if(empty($liste_souhaits)){ //si la liste est vide il n'y a rien a afficher
        echo $message;
    } 
    else{
        echo $message;
        echo '</p>
            <table>
            <tr><th></th><th>Livre</th><th>Prix</th><th>Action</th>';
        $index = 0;
        foreach ($liste_souhaits as &$livre){
            echo '<tr>
                    <td><a href="details.php?article=', $livre['ID'], '" title="Voir détails"><img src="../images/livres/', 
            $livre['ID'], '_mini.jpg" alt="', $livre['Titre'],'"></a></td>
                    <td>', $livre['Titre'], '</td>
                    <td>', $livre['Prix'], '</td>
                    <td><a href="panier.php?livre=', $livre['ID'], '&action=1">Ajouter au panier</a><br>
                        <a href="liste.php?index=',$index,'">Supprimer</a>
                        </td>
            </tr>';
            $index++;
        }
        echo    '</table>';
    }
    



}

$panier_livres = array ();

em_aff_pied();

em_aff_fin('main');


?>