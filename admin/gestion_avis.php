<?php

require_once('../inc/init.php');

$title = 'Gestion des avis';

if (!isAdmin()) {
    header('location:' . URL . 'compte.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {
    // delete en BDD
    execRequete("DELETE FROM avis WHERE id_avis=:id_avis", array('id_avis' => $_GET['id_avis']));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

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
        if (isset($_POST['id_avis'])) {
            // update en BDD
            execRequete("UPDATE avis SET 
            id_salle=:id_salle,commentaire=:commentaire,note=:note
            WHERE id_avis=:id_avis", $_POST);
        }
    }
}

// salles
$salles = execRequete("SELECT * FROM salle");

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Gestion des avis</h1>
<hr>

<?php

// affichage des avis
$resultats = execRequete('SELECT * FROM avis');
if ($resultats->rowCount() == 0) : ?>
    <div class="alert alert-info mt-5">Il n'y a pas encore d'avis enregistrés</div>
<?php else : ?>
    <p>Il y a <?php echo $resultats->rowCount() ?> avis</p>
    <table class="table table-bordered table-striped table-responsive-lg">
        <tr>
            <!-- entêtes de colonne -->
            <?php for ($i = 0; $i < $resultats->columnCount(); $i++) :
                $colonne = $resultats->getColumnMeta($i);
            ?>
                <th><?php echo ucfirst($colonne['name']); ?></th>
            <?php endfor; ?>
            <th colspan="2">Actions</th>
        </tr>
        <!-- données de colonne -->
        <?php while ($ligne = $resultats->fetch()) : ?>
            <tr>
                <?php foreach ($ligne as $key => $value) :
                    switch ($key) {
                        case 'note':
                            $notes = array('1' => '★', '2' => '★★', '3' => '★★★', '4' => '★★★★', '5' => '★★★★★');
                            $value = $notes[$value];
                            break;
                        case 'id_membre':
                            $email = execRequete("SELECT email FROM membre WHERE id_membre=$value")->fetch()['email'];
                            $value .= ' - ' . $email;
                            break;
                        case 'id_salle':
                            $titre = execRequete("SELECT titre FROM salle WHERE id_salle=$value")->fetch()['titre'];
                            $value .= ' - ' . $titre;
                            break;
                        case 'date_enregistrement':
                            $value = date('d/m/Y à H:i:s', strtotime($value));
                            break;
                    }
                ?>
                    <td><?php echo $value ?></td>
                <?php endforeach; ?>
                <td><a href="?action=edit&id_avis=<?php echo $ligne['id_avis'] ?>"><i class="fas fa-edit"></i></a></td>
                <td><a href="?action=delete&id_avis=<?php echo $ligne['id_avis'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php endif;

if (isset($_GET['action']) && $_GET['action'] == 'edit') :
    // cas d'un formulaire d'édition d'un avis existant
    if ($_GET['action'] == 'edit' && !empty($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {
        $resultat = execRequete("SELECT * FROM avis WHERE id_avis=:id_avis", array('id_avis' => $_GET['id_avis']));
        $avis_courant = $resultat->fetch();
    }
    // formulaire d'édition d'avis
?>
    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <?php echo implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="py-2">
        <?php if (!empty($avis_courant['id_avis'])) : ?>
            <input type="hidden" name="id_avis" value="<?php echo $avis_courant['id_avis'] ?>">
        <?php endif; ?>
        <div class="row">
            <div class="form-group col-sm-4">
                <label for="id_salle">Salle</label>
                <select class="form-control" id="id_salle" name="id_salle">
                    <?php while ($salle = $salles->fetch()) : ?>
                        <option value="<?php echo $salle['id_salle'] ?>" <?php echo ((isset($_POST['id_salle']) && $_POST['id_salle'] == $salle['id_salle']) || (isset($avis_courant['id_salle']) && $avis_courant['id_salle'] == $salle['id_salle'])) ? 'selected' : '' ?>>
                            <?php echo $salle['titre'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group col-sm-2">
                <label for="note">Note</label>
                <select class="form-control" id="note" name="note">
                    <?php
                    $notes = array('1' => '★', '2' => '★★', '3' => '★★★', '4' => '★★★★', '5' => '★★★★★');
                    foreach ($notes as $key => $note) :
                    ?>
                        <option value="<?php echo $key ?>" <?php echo ((isset($_POST['note']) && $_POST['note'] == $key) || (isset($avis_courant['note']) && $avis_courant['note'] == $key)) ? 'selected' : '' ?>>
                            <?php echo $note ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="commentaire">Commentaire</label>
            <textarea class="form-control" id="commentaire" name="commentaire" rows="4"><?php echo $_POST['commentaire'] ?? $avis_courant['commentaire'] ?? '' ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
<?php endif;

require_once('../inc/footer.php');
