<?php

require_once('inc/init.php');

$title = 'Mes commandes';

if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// commandes de l'utilisateur connecté
$id_membre = $_SESSION["membre"]["id_membre"];
$commandes = execRequete("  SELECT *
                            FROM commande c
                            INNER JOIN produit p ON p.id_produit = c.id_produit
                            INNER JOIN salle s ON s.id_salle = p.id_salle
                            WHERE c.id_membre = :id_membre
                            ORDER BY c.date_enregistrement DESC
                        ", array('id_membre' => $id_membre));

require_once('inc/header.php');

// corps de la page
if ($commandes->rowCount() > 0) : ?>
    <!-- affichage des commandes -->

    <h1 class="mt-2">Mes commandes</h1>
    <hr>
    <table class="table table-bordered table-striped">
        <?php
        $last_cmd = 0;
        while ($commande = $commandes->fetch()) :
            // ligne d'entête d'une commande (on la répète 1fois)
            if ($commande['id_commande'] != $last_cmd) {
        ?>
                <tr class="thead-dark">
                    <th>Commande n°<?php echo $commande['id_commande'] ?></th>
                    <th>Date de la commande<br><?php echo date('d/m/Y à H:i', strtotime($commande['date_enregistrement'])) ?></th>
                    <th>Catégorie</th>
                    <th>Description</th>
                    <th>Montant</th>
                </tr>
            <?php
            }
            // détails 
            ?>
            <tr>
                <td><?php echo $commande['titre'] ?></td>
                <td><?php echo 'Salle réservée <br>du : ' . date('d/m/Y H:i', strtotime($commande['date_arrivee'])) . '<br>au : ' . date('d/m/Y H:i', strtotime($commande['date_depart'])) ?></td>
                <td><?php switch ($commande['categorie']) {
                        case 'reunion':
                            echo 'Réunion';
                            break;
                        case 'formation':
                            echo 'Formation';
                            break;
                        case 'bureau':
                            echo 'Bureau';
                            break;
                    } ?></td>
                <td><?php echo $commande['description'] ?></td>
                <td><?php echo number_format($commande['prix'], 2, ',', '&nbsp;') ?>&euro;</td>
            </tr>
        <?php
            $last_cmd = $commande['id_commande'];
        endwhile;
        ?>
    </table>
<?php else : ?>
    <div class="alert alert-info mt-2">Vous n'avez pas encore passé de commande</div>
<?php endif;
require_once('inc/footer.php');
