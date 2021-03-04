<?php

require_once('../inc/init.php');

$title = 'Gestion des salles';

if (!isAdmin()) {
    header('location:' . URL . 'compte.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
    // récupération de la salle en BDD pour obtenir le nom du fichier de la photo
    $salle_asup = execRequete("SELECT photo FROM salle WHERE id_salle=:id_salle", array('id_salle' => $_GET['id_salle']));
    if ($salle_asup->rowCount() == 1) {
        $infos = $salle_asup->fetch();
        $photo = $infos['photo'];
        // suppression du fichier physique
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo)) {
            // suppression du fichier existant (unlink())
            unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo);
        }
        // delete en BDD
        execRequete("DELETE FROM salle WHERE id_salle=:id_salle", array('id_salle' => $_GET['id_salle']));
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// traitement du formulaire
if (!empty($_POST)) {
    // contrôles
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }
    // si je souhaite rendre la photo obligatoire et bien formatée
    if (empty($_FILES['photo']['name'])) {
        // si j'ai photo actuelle (salle en édition) je ne considère pas que c'est un champs vide
        if (empty($_POST['photo_actuelle'])) $nb_champs_vides++;
    } else {
        $mimeAutorises = array('image/jpeg', 'image/png', 'image/webp');
        if (!in_array($_FILES['photo']['type'], $mimeAutorises)) $errors[] = 'Format incorrect : ' . $_FILES['photo']['type'] . '<br>Fichiers JPEG, PNG et WEBP seulement';
    }

    if ($nb_champs_vides > 0) $errors[] = "Il manque $nb_champs_vides information(s)";

    if (empty($errors)) {
        if (!empty($_FILES['photo']['name'])) {
            // si j'avais déjà une photo
            if (isset($_POST['photo_actuelle']) && file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle'])) {
                // suppression du fichier existant (unlink())
                unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle']);
            }
            // gérer la photo (copie physique du fichier)
            $nomPhotoBDD = str_replace(' ', '_', $_POST['titre'] . '_' . $_FILES['photo']['name']);
            $dossierPhotos = $_SERVER['DOCUMENT_ROOT'] . URL . 'photos/';
            // déplacement du fichier temporaire vers le dossier 'photos' sous un nom unique (composé du titre et du nom original du fichier)
            move_uploaded_file($_FILES['photo']['tmp_name'], $dossierPhotos . $nomPhotoBDD);
        } else {
            $nomPhotoBDD = $_POST['photo_actuelle'];
        }

        unset($_POST['photo_actuelle']);
        $_POST['photo'] = $nomPhotoBDD;

        if (isset($_POST['id_salle'])) {
            // update en BDD
            execRequete("UPDATE salle SET 
            titre=:titre,description=:description,photo=:photo,pays=:pays,ville=:ville,adresse=:adresse,cp=:cp,capacite=:capacite,categorie=:categorie
            WHERE id_salle=:id_salle", $_POST);
        } else {
            // insert en BDD
            execRequete("INSERT INTO salle VALUES 
            (NULL,:titre,:description,:photo,:pays,:ville,:adresse,:cp,:capacite,:categorie)", $_POST);
        }
        // on force le mode affichage des salles
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Gestion des salles</h1>
<hr>

<ul class="nav nav-tabs nav-justify">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" href="?action=affichage">Affichage des salles</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) ? 'active' : '' ?>" href="?action=ajout">Ajouter/Editer une salle</a></li>
</ul>

<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) :
    // affichage des salles
    $resultats = execRequete('SELECT * FROM salle');
    if ($resultats->rowCount() == 0) : ?>
        <div class="alert alert-info mt-3">Il n'y a pas encore de salles enregistrées</div>
    <?php else : ?>
        <p class="mt-3">Il y a <?php echo $resultats->rowCount() ?> salle<?php echo ($resultats->rowCount() > 1) ? 's' : '' ?></p>
        <table class="table table-bordered table-striped table-responsive-lg mb-5">
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
                            case 'photo':
                                if (!empty($value)) $value = '<img class="img-fluid vignette" src="' . URL . 'photos/' . $value . '" alt="' . $ligne['titre'] . '"';
                                break;
                            case 'categorie':
                                $categories = array('reunion' => 'Réunion', 'formation' => 'Formation', 'bureau' => 'Bureau');
                                $value = $categories[$value];
                                break;
                            case 'description':
                                $extrait = (iconv_strlen($value) > 35) ? substr($value, 0, 35) : $value;
                                if ($extrait != $value) {
                                    $lastSpace = strrpos($extrait, ' ');
                                    $value = substr($extrait, 0, $lastSpace)  . '...';
                                }
                                break;
                        }
                    ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                    <td><a href="?action=edit&id_salle=<?php echo $ligne['id_salle'] ?>"><i class="fas fa-edit"></i></a></td>
                    <td><a href="?action=delete&id_salle=<?php echo $ligne['id_salle'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
<?php endif;

if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) :
    // cas d'un formulaire d'édition d'une salle existante
    if ($_GET['action'] == 'edit' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
        $resultat = execRequete("SELECT * FROM salle WHERE id_salle=:id_salle", array('id_salle' => $_GET['id_salle']));
        $salle_courante = $resultat->fetch();
    }
    // formulaire d'édition de salle
?>
    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <?php echo implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="mb-5">
        <?php if (!empty($salle_courante['id_salle'])) : ?>
            <input type="hidden" name="id_salle" value="<?php echo $salle_courante['id_salle'] ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="titre">Titre</label>
            <input type="text" class="form-control" id="titre" name="titre" value="<?php echo $_POST['titre'] ?? $salle_courante['titre'] ?? '' ?>">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="capacite">Capacité</label>
                <input type="number" class="form-control" id="capacite" name="capacite" value="<?php echo $_POST['capacite'] ?? $salle_courante['capacite'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="categorie">Catégorie</label>
                <select class="form-control" id="categorie" name="categorie">
                    <?php
                    $categories = array('reunion' => 'Réunion', 'formation' => 'Formation', 'bureau' => 'Bureau');
                    foreach ($categories as $key => $categorie) :
                    ?>
                        <option value="<?php echo $key ?>" <?php echo ((isset($_POST['categorie']) && $_POST['categorie'] == $key) || (isset($salle_courante['categorie']) && $salle_courante['categorie'] == $key)) ? 'selected' : '' ?>>
                            <?php echo $categorie ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="photo"><i class="fas fa-camera-retro iconephoto"></i></label>
                <input type="file" class="form-control d-none" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                <div id="preview">
                    <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_courante['photo'])) : ?>
                        <img src="<?php echo URL . 'photos/' . $salle_courante['photo'] ?>" alt="<?php echo $salle_courante['titre'] ?>" class="img-fluid vignette" id="placeholder">
                    <?php else : ?>
                        <img src="<?php echo URL . 'img/placeholder600.png' ?>" alt="placeholder" class="img-fluid vignette" id="placeholder">
                    <?php endif; ?>
                </div>
                <!-- mémorisation du nom du fichier actuel pour une salle en édition -->
                <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_courante['photo'])) : ?>
                    <input type="hidden" name="photo_actuelle" value="<?php echo $salle_courante['photo'] ?>">
                <?php endif; ?>
            </div>
            <div class="form-group col-md-6">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="7"><?php echo $_POST['description'] ?? $salle_courante['description'] ?? '' ?></textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="pays">Pays</label>
                <input type="text" class="form-control" id="pays" name="pays" value="<?php echo $_POST['pays'] ?? $salle_courante['pays'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="ville">Ville</label>
                <input type="text" class="form-control" id="ville" name="ville" value="<?php echo $_POST['ville'] ?? $salle_courante['ville'] ?? '' ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="adresse">Adresse</label>
                <textarea class="form-control" id="adresse" name="adresse" rows="2"><?php echo $_POST['adresse'] ?? $salle_courante['adresse'] ?? '' ?></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="cp">Code Postal</label>
                <input type="number" class="form-control" id="cp" name="cp" value="<?php echo $_POST['cp'] ?? $salle_courante['cp'] ?? '' ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
<?php endif;
require_once('../inc/footer.php');
