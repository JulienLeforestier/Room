<?php

require_once('../inc/init.php');

$title = 'Gestion des produits';

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
    // récupération du produit en BDD pour obtenir le nom du fichier de la photo
    $produit_asup = execRequete("SELECT photo FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));
    if ($produit_asup->rowCount() == 1) {
        $infos = $produit_asup->fetch();
        $photo = $infos['photo'];
        // suppression du fichier physique
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo)) {
            // suppression du fichier existant (unlink())
            unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $photo);
        }
        // delete en BDD
        execRequete("DELETE FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// traitement du formulaire
if (!empty($_POST)) {
    // var_dump($_POST);
    // var_dump($_FILES);
    // contrôles
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }
    // si je souhaite rendre la photo obligatoire et bien formatée
    if (empty($_FILES['photo']['name'])) {
        // si j'ai photo actuelle (produit en édition) je ne considère pas que c'est un champs vide
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
            $nomPhotoBDD = $_POST['reference'] . '_' . $_FILES['photo']['name'];
            $dossierPhotos = $_SERVER['DOCUMENT_ROOT'] . URL . 'photos/';
            // déplacement du fichier temporaire vers le dossier 'photos' sous un nom unique (composé de la référence et du nom original du fichier)
            move_uploaded_file($_FILES['photo']['tmp_name'], $dossierPhotos . $nomPhotoBDD);
        } else {
            $nomPhotoBDD = $_POST['photo_actuelle'];
        }

        unset($_POST['photo_actuelle']);
        $_POST['photo'] = $nomPhotoBDD;

        if (isset($_POST['id_produit'])) {
            // update en BDD
            execRequete("UPDATE produit SET 
            reference=:reference,categorie=:categorie,titre=:titre,description=:description,couleur=:couleur,taille=:taille,public=:public,photo=:photo,prix=:prix,stock=:stock 
            WHERE id_produit=:id_produit", $_POST);
        } else {
            // insert en BDD
            execRequete("INSERT INTO produit VALUES 
            (NULL,:reference,:categorie,:titre,:description,:couleur,:taille,:public,:photo,:prix,:stock)", $_POST);
        }
        // on force le mode affichage des produits
        //$_GET['action'] = 'affichage';
        // --- OU ---
        // header('location:' . URL . 'gestion_produits.php');
        // exit();
        // --- OU ---
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
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

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {
    // affichage des produits
    $resultats = execRequete('SELECT * FROM produit');
    if ($resultats->rowCount() == 0) {
?>
        <div class="alert alert-info mt-5">Il n'y a pas encore de produits enregistrés</div>
    <?php
    } else {
    ?>
        <p class="py-5">Il y a <?php echo $resultats->rowCount() ?> produit(s)</p>
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <?php
                // entêtes de colonne
                for ($i = 0; $i < $resultats->columnCount(); $i++) {
                    $colonne = $resultats->getColumnMeta($i);
                    // var_dump(get_class_methods($resultats));
                ?>
                    <th><?php echo ucfirst($colonne['name']); ?></th>
                <?php
                }
                ?>
                <th colspan="2">Actions</th>
            </tr>
            <?php
            // données de colonne
            while ($ligne = $resultats->fetch()) {
            ?>
                <tr>
                    <?php
                    foreach ($ligne as $key => $value) {
                        switch ($key) {
                            case 'photo':
                                if (!empty($value)) $value = '<img class="img-fluid" src="' . URL . 'photos/' . $value . '" alt="' . $ligne['titre'] . '"';
                                break;
                            case 'prix':
                                $value = number_format($value, 2, ',', '&nbsp;') . '&euro;';
                                break;
                            case 'public':
                                $publics = array('m' => 'Homme', 'f' => 'Femme', 'mixte' => 'Mixte');
                                $value = $publics[$value];
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
                    <?php
                    }
                    ?>
                    <td><a href="?action=edit&id_produit=<?php echo $ligne['id_produit'] ?>"><i class="fas fa-edit"></i></a></td>
                    <td><a href="?action=delete&id_produit=<?php echo $ligne['id_produit'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
                </tr>
            <?php
            }
            ?>
        </table>
    <?php
    }
}

if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) {
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
    <form method="post" enctype="multipart/form-data" class="py-5">
        <?php if (!empty($produit_courant['id_produit'])) : ?>
            <input type="hidden" name="id_produit" value="<?php echo $produit_courant['id_produit'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="reference">Référence</label>
                <input type="text" class="form-control" id="reference" name="reference" value="<?php echo $_POST['reference'] ?? $produit_courant['reference'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="categorie">Catégorie</label>
                <input type="text" class="form-control" id="categorie" name="categorie" value="<?php echo $_POST['categorie'] ?? $produit_courant['categorie'] ?? '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="titre">Titre</label>
            <input type="text" class="form-control" id="titre" name="titre" value="<?php echo $_POST['titre'] ?? $produit_courant['titre'] ?? '' ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="7"><?php echo $_POST['description'] ?? $produit_courant['description'] ?? '' ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="couleur">Couleur</label>
                <input type="text" class="form-control" id="couleur" name="couleur" value="<?php echo $_POST['couleur'] ?? $produit_courant['couleur'] ?? '' ?>">
            </div>
            <div class="form-group col-md-4">
                <label for="taille">Taille</label>
                <select class="form-control" id="taille" name="taille">
                    <?php
                    $tailles = array('S', 'M', 'L', 'XL');
                    foreach ($tailles as $taille) {
                    ?>
                        <option <?php echo ((isset($_POST['taille']) && $_POST['taille'] == $taille) || (isset($produit_courant['taille']) && $produit_courant['taille'] == $taille)) ? 'selected' : '' ?>>
                            <?php echo $taille ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="public">Public</label>
                <select class="form-control" id="public" name="public">
                    <?php
                    $publics = array('m' => 'Homme', 'f' => 'Femme', 'mixte' => 'Mixte');
                    foreach ($publics as $key => $public) {
                    ?>
                        <option value="<?php echo $key ?>" <?php echo ((isset($_POST['public']) && $_POST['public'] == $key) || (isset($produit_courant['public']) && $produit_courant['public'] == $key)) ? 'selected' : '' ?>>
                            <?php echo $public ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="photo"><i class="fas fa-camera-retro iconephoto"></i></label>
            <input type="file" class="form-control d-none" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
            <div id="preview">
                <?php
                if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($produit_courant['photo'])) {
                ?>
                    <img src="<?php echo URL . 'photos/' . $produit_courant['photo'] ?>" alt="<?php echo $produit_courant['titre'] ?>" class="img-fluid vignette" id="placeholder">
                <?php
                } else {
                ?>
                    <img src="<?php echo URL . 'img/placeholder600.png' ?>" alt="placeholder" class="img-fluid vignette" id="placeholder">
                <?php
                }
                ?>
            </div>
            <?php
            // mémorisation du nom du fichier actuel pour un produit en édition
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($produit_courant['photo'])) {
            ?>
                <input type="hidden" name="photo_actuelle" value="<?php echo $produit_courant['photo'] ?>">
            <?php
            }
            ?>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="prix">Prix</label>
                <input type="number" class="form-control" id="prix" name="prix" step="0.01" value="<?php echo $_POST['prix'] ?? $produit_courant['prix'] ?? '' ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="stock">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $_POST['stock'] ?? $produit_courant['stock'] ?? '' ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
<?php
}

require_once('../inc/footer.php');
