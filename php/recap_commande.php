<?php

ob_start(); //démarre la bufferisation
session_start();

require_once 'bibli_generale.php';
require_once 'bibli_bookshop.php';

em_aff_debut('BookShop | Recapitulatif des Commandes', '../styles/bookshop.css', 'main');
em_aff_enseigne_entete();



$panier_livres = array();
$bd = em_bd_connecter();

$sql = "SELECT coID, coIDClient, coDate, coHeure, GROUP_CONCAT(ccIDLivre) as livres ,GROUP_CONCAT(ccQuantite) as qtes,GROUP_CONCAT(liPrix) as prix
        FROM commandes as com 
        JOIN compo_commande as compo ON com.coID = compo.ccIDCommande 
        JOIN livres as li ON li.liID = compo.ccIDLivre 
        WHERE coIDClient = {$_SESSION['id']}
        GROUP BY coID ";

$res = mysqli_query($bd,$sql) or em_bd_erreur($bd,$sql);


$recap = array();

while($row = mysqli_fetch_assoc($res)){
    array_push($recap, array(   'IdCommande' => $row["coID"],
                                'Date' => $row["coDate"],
                                'Heure' => $row["coHeure"],
                                'Livres' => $row["livres"] ,
                                'Quantite' => $row["qtes"],
                                'Prix' => $row["prix"]
        )
    );
}

eml_afficher_contenu();


function eml_afficher_contenu(){
    global $recap;

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

            <h1 style="text-align:center;">Recapitulatif de vos commandes</h1>';


    echo
            '<table>
            <tr><th>Commande</th><th>Date</th><th>Heure</th><th>Livres(Quantite)</th><th>Total</th>';

    foreach($recap as $commande){


        echo '<tr>
                    <td>', $commande['IdCommande'], '</td>
                    <td>', $commande['Date'], '</td>
                    <td>', $commande['Heure'], '</td>
                    <td>';
        $ind = 0;
        $total = 0;
        $comm_livres = explode(",",$commande['Livres']);
        $comm_qte = explode(",",$commande['Quantite']);
        $comm_prix = explode(",",$commande['Prix']);
        foreach($comm_livres as $livre){
            echo $livre,"(",$comm_qte[$ind],")  ";
            $total += floatval($comm_qte[$ind])*floatval($comm_prix[$ind]);
            $ind ++;
        }
        echo        '</td>,<td>',$total,'</td>
         
            </tr>';
    }
        echo    '</table>';




}







em_aff_pied();

em_aff_fin('main');


?>