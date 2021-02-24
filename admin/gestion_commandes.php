<?php

require_once('../inc/init.php');

$title = 'Gestion des commandes';

if (!isAdmin()) {
    header('location:' . URL . 'compte.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_commande']) && is_numeric($_GET['id_commande'])) {
    // delete en BDD
    execRequete("DELETE FROM commande WHERE id_commande=:id_commande", array('id_commande' => $_GET['id_commande']));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Gestion des commandes</h1>
<hr>

<?php

// affichage des commande
$commandes = execRequete('SELECT * FROM commande');
if ($commandes->rowCount() == 0) : ?>
    <div class="alert alert-info mt-5">Il n'y a pas encore de commandes enregistrés</div>
<?php else : ?>
    <p>Il y a <?php echo $commandes->rowCount() ?> commande<?php echo ($commandes->rowCount() > 1) ? 's' : '' ?></p>
    <table class="table table-bordered table-striped table-responsive-lg">
        <tr>
            <!-- entêtes de colonne -->
            <?php for ($i = 0; $i < $commandes->columnCount(); $i++) :
                $colonne = $commandes->getColumnMeta($i);
            ?>
                <th><?php echo ucfirst($colonne['name']); ?></th>
            <?php endfor; ?>
            <th>Prix</th>
            <th>Action</th>
        </tr>
        <!-- données de colonne -->
        <?php while ($commande = $commandes->fetch()) : ?>
            <tr>
                <?php foreach ($commande as $key => $value) :
                    switch ($key) {
                        case 'id_membre':
                            $email = execRequete("SELECT email FROM membre WHERE id_membre=$value")->fetch()['email'];
                            $value .= ' - ' . $email;
                            break;
                        case 'id_produit':
                            $titre = execRequete("SELECT titre FROM produit p INNER JOIN salle s ON s.id_salle = p.id_salle WHERE p.id_produit=$value")->fetch()['titre'];
                            $date_arrivee = execRequete("SELECT date_arrivee FROM produit WHERE id_produit=$value")->fetch()['date_arrivee'];
                            $date_depart = execRequete("SELECT date_depart FROM produit WHERE id_produit=$value")->fetch()['date_depart'];
                            $value .= ' - ' . $titre . ' ' . date('d/m/Y', strtotime($date_arrivee)) . ' au ' . date('d/m/Y', strtotime($date_depart));
                            break;
                        case 'date_enregistrement':
                            $value = date('d/m/Y à H:i', strtotime($value));
                            break;
                        case 'prix':
                            $value = number_format($value, 2, ',', '&nbsp;') . '&euro;';
                            break;
                    }
                ?>
                    <td><?php echo $value ?></td>
                <?php endforeach; ?>
                <td><?php
                    $prix = execRequete("SELECT prix FROM produit p INNER JOIN commande c ON c.id_produit = p.id_produit WHERE p.id_produit=$commande[id_produit]")->fetch()['prix'];
                    echo number_format($prix, 2, ',', '&nbsp;') . '&euro;';
                    ?></td>
                <td><a href="?action=delete&id_commande=<?php echo $commande['id_commande'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php endif;

require_once('../inc/footer.php');
