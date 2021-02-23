<?php

require_once('../inc/init.php');

$title = 'Gestion des commandes';

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

if (!empty($_POST)) {
    execRequete("UPDATE commande SET etat = :newetat WHERE id_commande = :id_commande
    ", array('newetat' => $_POST["newetat"], 'id_commande' => $_POST["id_commande"]));
    // conserve les éventuelles valeurs en $_GET() contrairement au PHP_SELF
    header('location:' . $_SERVER['REQUEST_URI']);
    // header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

// commandes 
$commandes = execRequete("  SELECT * FROM commande c
                            INNER JOIN membre m on m.id_membre = c.id_membre
                            ORDER BY c.date_enregistrement DESC");

if (isset($_GET["action"]) && $_GET["action"] == 'details' && !empty($_GET["id_commande"])) {
    $resultats = execRequete("  SELECT *, p.prix AS prixU 
                                FROM produit p
                                INNER JOIN commande c ON c.id_produit = p.id_produit
                                INNER JOIN membre m ON m.id_membre = c.id_membre
                                WHERE c.id_commande = :id_commande
                            ", array('id_commande' => $_GET["id_commande"]));
    $details_commande = $resultats->fetchAll();
}

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Gestion des commandes</h1>
<hr>

<div class="row">
    <div class="col-md-7">
        <table class="table table-bordered table-striped table-hover" id="tabcommandes">
            <tr>
                <th>N°</th>
                <th>Date</th>
                <th>Montant</th>
                <th>Client</th>
                <th>État</th>
            </tr>
            <?php
            while ($commande = $commandes->fetch()) {
                $datecmd = new DateTime($commande['date_enregistrement']);
            ?>
                <tr data-idcmd="<?php echo $commande['id_commande'] ?>">
                    <td><?php echo $commande['id_commande'] ?></td>
                    <td><?php echo $datecmd->format('d/m/Y à H:i:s') ?></td>
                    <td><?php echo number_format($commande['montant'], 2, ',', '&nbsp;') ?>&euro;</td>
                    <td><?php echo $commande['nom'] . ' ' . $commande['prenom'] ?></td>
                    <td>
                        <form method="post">
                            <div class="form-row">
                                <div class="form-group col-11">
                                    <input type="hidden" name="id_commande" value="<?php echo $commande['id_commande'] ?>">
                                    <select name="newetat" class="form-control form-control-sm">
                                        <option>en cours de traitement</option>
                                        <option <?php echo ($commande['etat'] == 'envoyée') ? 'selected' : '' ?>>envoyée</option>
                                        <option <?php echo ($commande['etat'] == 'livrée') ? 'selected' : '' ?>>livrée</option>
                                    </select>
                                </div>
                                <div class="form-group col-1">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i></button>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
    </div>
    <div class="col-md-5">
        <?php
        if (isset($details_commande)) :
        ?>
            <div class="h4 pt-2">Détails de la commande n°<?php echo $details_commande[0]['id_commande'] ?></div>
            <p><?php echo 'État : ' . $details_commande[0]['etat'] ?><br>
                <?php echo 'Date de la commande : ' . $details_commande[0]['date_enregistrement'] ?></p>
            <div class="pt-2">
                <?php echo ($details_commande[0]['civilite'] == 'm') ? 'M' : 'Mme' ?>
                <?php echo $details_commande[0]['nom'] ?>
                <?php echo $details_commande[0]['prenom'] ?>
                <br>
                <?php echo $details_commande[0]['adresse'] ?>
                <br>
                <?php echo $details_commande[0]['code_postal'] ?>
                <?php echo $details_commande[0]['ville'] ?>
                <hr>
                <!-- détail des articles -->
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>Référence</th>
                        <th>Article</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                    </tr>
                    <?php
                    $montant_total = 0;
                    $quantite_total = 0;
                    foreach ($details_commande as $lignecmd) {
                    ?>
                        <tr>
                            <td><?php echo $lignecmd['reference'] ?></td>
                            <td><?php echo $lignecmd['titre'] ?><br>Taille : <?php echo $lignecmd['taille'] ?></td>
                            <td><?php echo $lignecmd['quantite'] ?></td>
                            <td><?php echo number_format($lignecmd['prixU'], 2, ',', '&nbsp;') ?>&euro;</td>
                        </tr>
                    <?php
                        $montant_total += $lignecmd['quantite'] * $lignecmd['prixU'];
                        $quantite_total += $lignecmd['quantite'];
                    }
                    if (count($details_commande) > 1) :
                    ?>
                        <tr>
                            <td>Total</td>
                            <td></td>
                            <td><?php echo $quantite_total ?></td>
                            <td><?php echo number_format($montant_total, 2, ',', '&nbsp;') . '&euro;' ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once('../inc/footer.php');
