<?php

require_once('../inc/init.php');

$title = 'Gestion des membres';

if (!isAdmin()) {
    header('location:' . URL . 'compte.php');
    exit();
}

// contrôle si le formulaire a été annulé
if (isset($_POST["cancel"])) {
    unset($_POST["cancel"]);
    header('location:' . $_SERVER['PHP_SELF']);
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
    // contrôle du pseudo
    if (iconv_strlen(trim($_POST['pseudo'])) < 2 || iconv_strlen(trim($_POST['pseudo'])) > 20) $errors[] = "Pseudo invalide";
    // contrôle du nombre d'erreur, si aucune, je peux procéder à l'inscription
    if (empty($errors)) {
        execRequete("UPDATE membre SET pseudo=:pseudo,nom=:nom,prenom=:prenom,email=:email,civilite=:civilite,statut=:statut WHERE id_membre=:id_membre", $_POST);
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Gestion des membres</h1>
<hr>

<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) :
    // affichage des membres
    $membres = execRequete('SELECT * FROM membre');
    if ($membres->rowCount() == 0) : ?>
        <div class="alert alert-info mt-3">Il n'y a pas encore de membres enregistrés</div>
    <?php else : ?>
        <p class="mt-3">Il y a <?php echo $membres->rowCount() ?> membre<?php echo ($membres->rowCount() > 1) ? 's' : '' ?></p>
        <table class="table table-bordered table-striped table-responsive-lg mb-5">
            <tr>
                <!-- entêtes de colonne -->
                <?php for ($i = 0; $i < $membres->columnCount(); $i++) :
                    $colonne = $membres->getColumnMeta($i);
                    if ($colonne['name'] != 'mdp') : ?>
                        <th><?php echo ucfirst($colonne['name']); ?></th>
                    <?php endif; ?>
                <?php endfor; ?>
                <th colspan="2">Actions</th>
            </tr>
            <!-- données de colonne -->
            <?php while ($membre = $membres->fetch()) : ?>
                <tr>
                    <?php foreach ($membre as $key => $value) : ?>
                        <?php if ($key != 'mdp') :
                            switch ($key) {
                                case 'civilite':
                                    $publics = array('m' => 'Homme', 'f' => 'Femme');
                                    $value = $publics[$value];
                                    break;
                                case 'statut':
                                    $roles = array('1' => 'Membre', '2' => "Admin");
                                    $value = $roles[$value];
                                    break;
                                case 'date_enregistrement':
                                    $value = date('d/m/Y H:i:s', strtotime($value));
                                    break;
                            }
                        ?>
                            <td><?php echo $value ?></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <td><a href="?action=edit&id_membre=<?php echo $membre['id_membre'] ?>"><i class="fas fa-edit"></i></a></td>
                    <td><a href="?action=delete&id_membre=<?php echo $membre['id_membre'] ?>" class="confirm"><i class="fas fa-trash-alt"></i></a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
<?php endif;

if (isset($_GET['action']) && $_GET['action'] == 'edit') :
    // cas d'un formulaire d'édition d'un membre existant
    if ($_GET['action'] == 'edit' && !empty($_GET['id_membre']) && is_numeric($_GET['id_membre'])) {
        $resultat = execRequete("SELECT * FROM membre WHERE id_membre=:id_membre", array('id_membre' => $_GET['id_membre']));
        $membre_courant = $resultat->fetch();
    }
    // formulaire d'édition de membre
?>
    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <?php echo implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>
    <form method="post" class="mb-5">
        <?php if (!empty($membre_courant['id_membre'])) : ?>
            <input type="hidden" name="id_membre" value="<?php echo $membre_courant['id_membre'] ?>">
        <?php endif; ?>
        <div class="row">
            <div class="form-group col">
                <label for="pseudo">Pseudo</label>
                <input type="text" class="form-control 
            <?php echo (!empty($_POST) &&
                (empty(trim($_POST['pseudo'])) ||
                    iconv_strlen(trim($_POST['pseudo'])) < 2 ||
                    iconv_strlen(trim($_POST['pseudo'])) > 20))
                ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ?? $membre_courant['pseudo'] ?? '' ?>">
                <div class="invalid-feedback">Merci de renseigner un pseudo (entre 2 et 20 caractères)</div>
            </div>
            <div class="form-group col">
                <label for="email">Email</label>
                <input type="email" class="form-control
            <?php echo (!empty($_POST) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?php echo $_POST['email'] ?? $membre_courant['email'] ?? ''  ?>">
                <div class="invalid-feedback">Merci de saisir une adresse mail valide</div>
            </div>
            <div class="form-group col">
                <label for="statut">Statut</label>
                <select class="form-control" id="statut" name="statut">
                    <?php
                    $statuts = array(1 => 'Membre', 2 => 'Admin');
                    foreach ($statuts as $key => $statut) :
                    ?>
                        <option value="<?php echo $key ?>" <?php echo (isset($membre_courant['statut']) && $membre_courant['statut'] == $key) ? 'selected' : '' ?>>
                            <?php echo $statut ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-2">
                <label for="civilite">Civilité</label>
                <select name="civilite" id="civilite" class="form-control">
                    <option value="m" <?php echo ($membre_courant['civilite'] == "m") ? 'selected' : '' ?>>M</option>
                    <option value="f" <?php echo ($membre_courant['civilite'] == "f") ? 'selected' : '' ?>>Mme</option>
                </select>
            </div>
            <div class="form-group col">
                <label for="nom">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ?? $membre_courant['nom'] ?? '' ?>">
            </div>
            <div class="form-group col">
                <label for="prenom">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ?? $membre_courant['prenom'] ?? '' ?>">
            </div>
        </div>
        <button type="cancel" name="cancel" class="btn btn-primary">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
<?php endif;
require_once('../inc/footer.php');
