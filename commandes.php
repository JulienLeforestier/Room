<?php

require_once('inc/init.php');

$title = 'Mes commandes';

if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// commandes de l'utilisateur connecté
$id_membre = $_SESSION["membre"]["id_membre"];
$commandes = execRequete("  SELECT *, c.id_commande AS numero, p.prix AS prixU 
                            FROM commande c
                            INNER JOIN produit p ON p.id_produit = c.id_produit
                            WHERE c.id_membre = :id_membre
                            ORDER BY c.date_enregistrement DESC
                        ", array('id_membre' => $id_membre));

require_once('inc/header.php');

// corps de la page
if ($commandes->rowCount() > 0) {
    // affichage des commandes
?>

    <h1 class="mt-2">Mes commandes</h1>
    <hr>
    <table class="table table-bordered table-striped">
        <?php
        $last_cmd = 0;
        while ($commande = $commandes->fetch()) :
            // ligne d'entête d'une commande (on la répète 1fois)
            if ($commande['numero'] != $last_cmd) {
        ?>
                <tr class="thead-dark">
                    <th>Commande n°<?php echo $commande['numero'] ?></th>
                    <th colspan="2">Date : <?php echo date('d/m/Y à H:i:s', strtotime($commande['date_enregistrement'])) ?></th>
                    <th class="w-25">Etat : <?php echo $commande['etat'] ?></th>
                    <th colspan="3">Montant total : <?php echo number_format($commande['montant'], 2, ',', '&nbsp;') ?>&euro;</th>
                </tr>
            <?php
            }
            // détails 
            ?>
            <tr>
                <td><?php echo $commande['reference'] ?></td>
                <td><?php echo $commande['titre'] ?></td>
                <td>Taille : <?php echo $commande['taille'] ?><br>
                    Catégorie : <?php echo $commande['categorie'] ?><br>
                    Public : <?php echo $commande['public'] ?></td>
                <td><img src="<?php echo URL . 'photos/' . $commande['photo'] ?>" alt="<?php echo $commande['titre'] ?>" class="img-fluid vignette"></td>
                <td><?php echo number_format($commande['prixU'], 2, ',', '&nbsp;') ?>&euro;</td>
                <td><?php echo $commande['quantite'] ?></td>
                <td><?php echo number_format($commande['prixU'] * $commande['quantite'], 2, ',', '&nbsp;') ?>&euro;</td>
            </tr>
        <?php
            $last_cmd = $commande['numero'];
        endwhile;
        ?>
    </table>
<?php
} else {
?>
    <div class="alert alert-info mt-2">Vous n'avez pas encore passé de commande</div>
<?php
}

require_once('inc/footer.php');
