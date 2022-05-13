<?php

ob_start(); //démarre la bufferisation
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

em_aff_debut('BookShop | Votre Panier', '../styles/bookshop.css', 'main');
em_aff_enseigne_entete();

$message = "";


$panier_livres = array();
$bd = em_bd_connecter();

$total_panier = 0;
$indexSuppression = 0;

deTableAArray();



function deTableAArray(){ //récupère le contenu de la table panier_actuel et le met dans l'array panier_livres
    global $bd;
    global $message;
    $sql = "SELECT panierIDlivre, panierLivreQuantite, liTitre, liPrix FROM panier_actuel JOIN livres on liID = panierIDlivre";
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);

    while($row = mysqli_fetch_assoc($res)){
        global $panier_livres;
        array_push($panier_livres,array(    'ID'      => $row["panierIDlivre"],
                                            'Titre'      => $row["liTitre"],
                                            'Prix'      => $row["liPrix"],
                                            'quantite'   => $row["panierLivreQuantite"],
                                            'total'   => $row["panierLivreQuantite"]*$row["liPrix"]));
    }
    if (empty($panier_livres)) $message = "Votre panier est vide.";
}



if(isset($_GET['action'])){ //s'il y a des variables dans l'url => 
                            //ajouter un livre, retirer un livre, supprimer une ligne ou passer une commande
    $action = $_GET['action'];
    

    if($action == 1) ajouter_livre();
    if($action == 0) supprimer_livre();
    if($action == 2) diminuer_livre();
    if($action == 3) traitement_commande();

    actualiser_baseDonnees();
}
else{
    eml_aff_contenu();
}

function ajouter_livre(){
    global $panier_livres;
    global $message;
    $idLivre = $_GET['livre'];
    if (empty($panier_livres)){ //si le panier est vide, on l'initialise avec le livre en paramètre
        global $bd;
        $sql = "SELECT liTitre, liPrix FROM livres WHERE liID = ".$idLivre."";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        $row = mysqli_fetch_assoc($res);
        $panier_livres = array(
            array(    'ID'      => $idLivre,
                                        'Titre'      => $row["liTitre"],
                                        'Prix'      => $row["liPrix"],
                                        'quantite'   => 1,
                                        'total'   => $row["liPrix"])
        );
    }
    else{
        ajouter_NouveauLivre($idLivre,1); //si le panier n'est pas vide, on ajoute le nouveau livre
    }
    $message = "Le livre a bien été ajouté a votre panier.";
    
    
}

function diminuer_livre(){
    global $panier_livres;
    global $message;
    global $indexSuppression;
    $idLivre = $_GET['livre'];
    
    foreach ($panier_livres as &$commande_livre){ //si le livre est déjà dans le panier, on incrémente la quantité
        if($commande_livre['ID']==$idLivre){
            $commande_livre['quantite'] --;
            $commande_livre['total'] -=  $commande_livre['Prix'];
            if($commande_livre['quantite']==0) supprimer_livre();
        }
        $indexSuppression++;
    }
}



function ajouter_NouveauLivre($idLivre,$qte){
    $count = 0;
    global $panier_livres;
    foreach ($panier_livres as &$commande_livre){ //si le livre est déjà dans le panier, on incrémente la quantité
        if($commande_livre['ID']==$idLivre){
            $commande_livre['quantite'] += $qte;
            $commande_livre['total'] +=  $commande_livre['Prix'];
            $count++;
        }
    }
    if($count == 0){ //s'il n'est pas dans le panier on crée une ligne pour le nouveau livre
        global $bd;
        $sql = "SELECT liTitre, liPrix FROM livres WHERE liID = ".$idLivre."";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
        $row = mysqli_fetch_assoc($res);
        array_push($panier_livres,array(
                                        'ID'      => $idLivre,
                                        'Titre'      => $row["liTitre"],
                                        'Prix'      => $row["liPrix"],
                                        'quantite'   => 1,
                                        'total'   => $row["liPrix"])
        );
    }
}


function actualiser_baseDonnees(){
    global $panier_livres;
    global $bd;
    $sql = "DELETE FROM panier_actuel";
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    foreach ($panier_livres as &$commande_livre){
        $sql = "INSERT INTO panier_actuel(`panierIDlivre`,`panierLivreQuantite`) VALUES (".$commande_livre["ID"].",".$commande_livre["quantite"].")";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    }
    eml_aff_contenu();
    
}


function supprimer_livre(){
    global $message;
    global $panier_livres;
    global $indexSuppression;
    if (isset($_GET['index'])) $index =  $_GET['index'];
    else $index = $indexSuppression;
    unset($panier_livres[$index]);
    $message = "Le livre a bien été supprimé de votre panier.";
}





function eml_aff_contenu(){
    global $panier_livres;
    global $total_panier;
    global $message;
    echo
        '<style>
        table, th, td {
        border: 1px solid;
        text-align: center;
        vertical-align: middle;
        padding: 5px;
        }
        table {
        border-spacing: 15px;
        margin-left: auto;
        margin-right: auto;
        }
        </style>

        <h1 style="text-align:center;">Votre panier</h1>
        <p style="text-align:center;">';
    if(empty($panier_livres)){ //si le panier est vide il n'y a rien a afficher
        echo $message;
    } 
    else{
        echo $message;
        echo '</p>
        <form method="post" action="panier.php?action=3">',
        '<table>
        <tr><th></th><th>Livre</th><th>Prix</th><th>Qté</th><th>Total</th><th>Action</th>';
        $index = 0;
        foreach ($panier_livres as &$commande_livre){
            echo '<tr>
                    <td><a href="details.php?article=', $commande_livre['ID'], '" title="Voir détails"><img src="../images/livres/', 
            $commande_livre['ID'], '_mini.jpg" alt="', $commande_livre['Titre'],'"></a></td>
                    <td>', $commande_livre['Titre'], '</td>
                    <td>', $commande_livre['Prix'], '</td>
                    <td>', $commande_livre['quantite'], '</td>
                    <td>', $commande_livre['total'], '</td>
                    <td><a href="panier.php?livre=', $commande_livre['ID'], '&action=1" title="Ajouter">+</a> 
                    &nbsp &nbsp &nbsp
                    <a href="panier.php?livre=', $commande_livre['ID'], '&action=2" title="Diminuer">-</a><br><br>
                        <a href="panier.php?action=0&index=',$index,'">Supprimer</a>
                        </td>
            </tr>';
            $total_panier += $commande_livre['total'];
            $index++;
        }
        echo    '</table>
                <p><h3 style="text-align:center";>Total du panier : ',$total_panier,' €</h3>
                <br><center><input type="submit" name="btnCommande" value="Commander"></center>
                </p>
                
                </form>';
    }
    



}

function traitement_commande(): void {
    // si l'utilisateur n'est pas authentifié, on le redirige sur la page login.php
    if (! em_est_authentifie()){
        header('Location: login.php');
        exit;

    }
     
    
    global $bd;
    global $panier_livres;
    global $message;
    $IDClient = $_SESSION['id'];

    


    //on verifie que l'utilisateur a renseigne son adresse
    $sql = "SELECT cliAdresse, cliCP, cliVille, cliPays FROM clients WHERE cliID=$IDClient";
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    $row = mysqli_fetch_assoc($res);
    
 
    if(empty($row['cliAdresse']) || empty($row['cliCP']) || empty($row['cliVille']) || empty($row['cliPays']) ){ //s'il n'y a pas d'adresse on le renvoie vers la page compte
        header('Location: compte.php');
        exit;
    }

    // on cree une nouvelle commande (nouvelle entree dans la table commandes)
    $sql = "INSERT INTO commandes(`coIDClient`, `coDate`, `coHeure`) VALUES
            ($IDClient,".date("Ymd").",".date("hi").")";
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);


    //on recupere l'ID de la commande qu'on vient de creer (ID max)
    $sql = "SELECT MAX(coID) as idMax
            FROM commandes";
    $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    $IDCommande = mysqli_fetch_assoc($res);



    //on remplit notre commande avec la table compo_commande
    foreach ($panier_livres as &$commande_livre){ //si le livre est déjà dans le panier, on incrémente la quantité
        $sql = "INSERT INTO compo_commande(ccIDCommande, ccIDLivre, ccQuantite) VALUES
                (".$IDCommande['idMax'].",".$commande_livre['ID'].",".$commande_livre['quantite'].")";
        $res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);
    }

    $panier_livres = array ();

    $message = "Votre commande a bien été effectuée.";
    
}


$panier_livres = array ();

em_aff_pied();

em_aff_fin('main');


?>