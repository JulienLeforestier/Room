<?php

require_once('../inc/init.php');

$title = 'Gestion des produits';

if (!isAdmin()) {
    header('location:' . URL . 'compte.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
    // delete en BDD
    execRequete("DELETE FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

// salles
$salles = execRequete('SELECT * FROM salle');

// produits
$produits = execRequete('SELECT * FROM produit');

// traitement du formulaire
if (!empty($_POST)) {
    // contrôles
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }

    if ($nb_champs_vides > 0) $errors[] = "Il manque $nb_champs_vides information(s)";

    if (empty($errors)) {
        $_POST['etat'] = 'libre';
        $_POST['date_arrivee'] = date('Y/m/d H:i:s', strtotime($_POST['date_arrivee']));
        $_POST['date_depart'] = date('Y/m/d H:i:s', strtotime($_POST['date_depart']));
        while ($produit = $produits->fetch()) {
            // if (
            //     $produit['id_salle'] == $_POST['id_salle']
            //     && strtotime($produit['date_depart']) < strtotime($_POST['date_arrivee'])
            //     && strtotime($produit['date_arrivee']) > strtotime($_POST['date_depart'])
            // ) {
            if (isset($_POST['id_produit'])) {
                // update en BDD
                execRequete("UPDATE produit SET 
                        id_salle=:id_salle,date_arrivee=:date_arrivee,date_depart=:date_depart,prix=:prix,etat=:etat
                        WHERE id_produit=:id_produit", $_POST);
            } else {
                // insert en BDD
                execRequete("INSERT INTO produit VALUES 
                        (NULL,:id_salle,:date_arrivee,:date_depart,:prix,:etat)", $_POST);
            }
            // on force le mode affichage des produits
            header('location:' . $_SERVER['PHP_SELF']);
            exit();
            // } else {
            //     echo 'NON';
            //     // header('location:' . $_SERVER['PHP_SELF']);
            //     // exit();
            // }
        }
    }
}

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Gestion des produits</h1>
<hr>

<ul class="nav nav-tabs nav-justify">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" href="?action=affichage">Affichage des produits</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) ? 'active' : '' ?>" href="?action=ajout">Ajouter/Editer un produit</a></li>
</ul>

<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) :
    // affichage des produits
    $produits = execRequete('SELECT * FROM produit');
    if ($produits->rowCount() == 0) : ?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de produits enregistrés</div>
    <?php else : ?>
        <p class="py-2">Il y a <?php echo $produits->rowCount() ?> produit<?php echo ($produits->rowCount() > 1) ? 's' : '' ?></p>
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <!-- entêtes de colonne -->
                <?php for ($i = 0; $i < $produits->columnCount(); $i++) :
                    $colonne = $produits->getColumnMeta($i);
                ?>
                    <th><?php echo ucfirst($colonne['name']); ?></th>
                <?php endfor; ?>
                <th colspan="2">Actions</th>
            </tr>
            <!-- données de colonne -->
            <?php while ($produit = $produits->fetch()) : ?>
                <tr>
                    <?php foreach ($produit as $key => $value) :
                        switch ($key) {
                            case 'id_salle':
                                $titre = execRequete("SELECT titre FROM salle WHERE id_salle=$value")->fetch()['titre'];
                                $value .= ' - ' . $titre;
                                break;
                            case 'date_arrivee':
                            case 'date_depart':
                                $value = date('d/m/Y H:i', strtotime($value));
                                break;
                            case 'prix':
                                $value = number_format($value, 2, ',', '&nbsp;') . '&euro;';
                                break;
                        }
                    ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                    <td><a href="?action=edit&id_produit=<?php echo $produit['id_produit'] ?>"><i class="fas fa-edit"></i></a></td>
                    <td><a href="?action=delete&id_produit=<?php echo $produit['id_produit'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
<?php endif;

if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) :
    // cas d'un formulaire d'édition d'un produit existant
    if ($_GET['action'] == 'edit' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
        $resultat = execRequete("SELECT * FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));
        $produit_courant = $resultat->fetch();
    }
    // formulaire d'édition de produit
?>
    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <?php echo implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>
    <form method="post" class="py-2">
        <?php if (!empty($produit_courant['id_produit'])) : ?>
            <input type="hidden" name="id_produit" value="<?php echo $produit_courant['id_produit'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="id_salle">Salle</label>
                <select class="form-control" id="id_salle" name="id_salle">
                    <?php while ($salle = $salles->fetch()) : ?>
                        <option value="<?php echo $salle['id_salle'] ?>" <?php echo ((isset($_POST['id_salle']) && $_POST['id_salle'] == $salle['id_salle']) || (isset($produit_courant['id_salle']) && $produit_courant['id_salle'] == $salle['id_salle'])) ? 'selected' : '' ?>>
                            <?php echo $salle['id_salle'] . ' - ' . $salle['titre']  . ' - ' . $salle['adresse'] . ', ' . $salle['cp'] . ', ' . $salle['ville']  . ' - ' . $salle['capacite'] . ' pers' ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group col-md-6 input-icon">
                <label for="prix">Tarif</label>
                <input type="number" class="form-control" id="prix" name="prix" placeholder="prix en euros" value="<?php echo $_POST['prix'] ?? $produit_courant['prix'] ?? '' ?>">
                <i>€</i>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="date_arrivee">Date d'arrivée</label>
                <input type="text" class="form-control" id="date_arrivee" name="date_arrivee" value="<?php echo $_POST['date_arrivee'] ?? $produit_courant['date_arrivee'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="date_depart">Date de départ</label>
                <input type="text" class="form-control" id="date_depart" name="date_depart" value="<?php echo $_POST['date_depart'] ?? $produit_courant['date_depart'] ?? '' ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
<?php endif;

require_once('../inc/footer.php');
