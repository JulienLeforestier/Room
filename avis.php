<?php

require_once('inc/init.php');

$title = 'Mon compte';

if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// contrôle si le formulaire est posté
if (!empty($_POST)) {
    // contrôles avant l'insertion en BDD
    $errors = array();
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) $errors[] = "Il manque $nb_champs_vides information(s)";
    // contrôle du commentaire
    if (iconv_strlen(trim($_POST['commentaire'])) < 10) $errors[] = "Un commentaire doit faire 10 caractères minimum";
    // contrôle du nombre d'erreur, si aucune, je peux procéder au dépôt de l'avis
    if (empty($errors)) {
        $_POST['id_membre'] = $_SESSION['membre']['id_membre'];
        execRequete("INSERT INTO avis VALUES (NULL,:id_membre,:id_salle,:commentaire,:note,NOW())", $_POST);
        header('location:' . URL);
        exit();
    }
}

if (!isset($_GET['id_salle'])) header('location:' . URL);

// salles
$salles = execRequete("SELECT * FROM salle");

require_once('inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Laisser votre avis</h1>
<hr>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger mt-3">
        <?php echo implode('<br>', $errors) ?>
    </div>
<?php endif; ?>

<form method="post">
    <div class="row">
        <div class="form-group col-sm-4">
            <label for="id_salle">Salle</label>
            <select class="form-control" id="id_salle" name="id_salle" disabled>
                <?php while ($salle = $salles->fetch()) : ?>
                    <option value="<?php echo $salle['id_salle'] ?>" <?php echo ((isset($_GET['id_salle']) && $_GET['id_salle'] == $salle['id_salle'])) ? 'selected' : '' ?>>
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
                    <option value="<?php echo $key ?>" <?php echo ($key == 5) ? 'selected' : '' ?>>
                        <?php echo $note ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="commentaire">Commentaire</label>
        <textarea class="form-control" id="commentaire" name="commentaire" rows="4" placeholder="Laissez nous votre avis" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
</form>

<?php

// affichage des avis
$resultats = execRequete('SELECT * FROM avis WHERE id_salle =' . $_GET['id_salle']);
if ($resultats->rowCount() == 0) : ?>
    <div class="alert alert-info mt-3">Il n'y a pas encore d'avis enregistrés</div>
<?php else : ?>
    <p class="mt-3">Il y a <?php echo $resultats->rowCount() ?> avis</p>
    <table class="table table-bordered table-striped table-responsive-lg mb-5">
        <tr>
            <!-- entêtes de colonne -->
            <?php for ($i = 0; $i < $resultats->columnCount(); $i++) :
                $colonne = $resultats->getColumnMeta($i);
            ?>
                <th><?php echo ucfirst($colonne['name']); ?></th>
            <?php endfor; ?>
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
                            $pseudo = execRequete("SELECT pseudo FROM membre WHERE id_membre=$value")->fetch()['pseudo'];
                            $value .= '&nbsp;-&nbsp;' . $pseudo;
                            break;
                        case 'id_salle':
                            $titre = execRequete("SELECT titre FROM salle WHERE id_salle=$value")->fetch()['titre'];
                            $value .= '&nbsp;-&nbsp;' . $titre;
                            break;
                        case 'date_enregistrement':
                            $value = date('d/m/Y à H:i', strtotime($value));
                            break;
                    }
                ?>
                    <td><?php echo $value ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endwhile; ?>
    </table>
<?php endif;
require_once('inc/footer.php');
