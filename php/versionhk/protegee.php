<?php
require_once '../php/bibli_generale.php';
require_once ('../php/bibli_bookshop.php');

// bufferisation des sorties
ob_start();

// démarrage de la session
session_start();

// Page accessible uniquement aux utilisateurs authentifiés
hk_est_authentifier();
    
// génération de la page
 echo '<h2>Accès restreint aux utilisateurs authentifiés</h2>';

echo '<main><section>',
     '<p>SID : ', session_id(), 
     '</p>',
     '<ul>';

$bd = em_bd_connecter();

$sql =   "SELECT *
        FROM utilisateur
        WHERE cliID = '{$_SESSION['login']['email']}'";
        
$r = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

$enr = mysqli_fetch_assoc($r);

// Libération de la mémoire associée au résultat de la requête
mysqli_free_result($r);

// fermeture de la connexion à la base de données
mysqli_close($bd);


$row = hk_protection($row);

foreach($row as $key => $value){
    echo '<li>', $key, ' : ', $value, '</li>';
}

echo '</ul>';


echo '</section></main>';
em_aff_pied();

ob_end_flush();


?>
