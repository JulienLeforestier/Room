<?php

require_once('inc/init.php');

$title = 'Mon compte';

if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// formulaire de mise à jour des données utlisateur
if (isset($_POST["modif_coord"])) {
    // on retire de post le bouton qui nous a servi à identifier le formulaire
    unset($_POST["modif_coord"]);
    // contrôles avant l'update en BDD
    $errors_coord = array();
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) $errors_coord[] = "Il manque $nb_champs_vides information(s)";
    // contrôle de l'email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors_coord[] = "Format de mail invalide";
    // contrôle du nombre d'erreur, si aucune, je peux procéder à la mise à jour
    if (empty($errors_coord)) {
        $_POST["id_membre"] = $_SESSION["membre"]["id_membre"];
        execRequete("UPDATE membre SET civilite=:civilite,nom=:nom,prenom=:prenom,email=:email WHERE id_membre=:id_membre", $_POST);
        $_SESSION["membre"]["civilite"] = $_POST["civilite"];
        $_SESSION["membre"]["nom"] = $_POST["nom"];
        $_SESSION["membre"]["prenom"] = $_POST["prenom"];
        $_SESSION["membre"]["email"] = $_POST["email"];
        $_SESSION["message"] = 'Coordonnées mises à jour';
        header('location:' . $_SERVER["PHP_SELF"]);
        exit();
    }
}

// formulaire de changement de mot de passe
if (isset($_POST["modif_mdp"])) {
    // on retire de post le bouton qui nous a servi à identifier le formulaire
    unset($_POST["modif_mdp"]);
    // contrôles avant l'update en BDD
    $errors_mdp = array();
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) $errors_mdp[] = "Il manque $nb_champs_vides information(s)";
    // contrôle du mdp actuel
    if (!empty($_POST["mdp"]) && !password_verify($_POST['mdp'], $_SESSION["membre"]["mdp"])) $errors_mdp[] = "Mot de passe actuel incorrect";
    // contrôle du nouveau mdp
    if (!empty($_POST["newmdp"]) && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['newmdp'])) $errors_mdp[] = "Merci de saisir un mot de passe entre 8 et 20 caractères comportant au moins 1 miniscule, 1 majuscule, 1 chiffre et 1 caractère spécial ($ ! - _ @)";
    // contrôle de la confirmation du nouveau mdp
    if (!empty($_POST["confirm"]) && $_POST['confirm'] !== $_POST['newmdp']) $errors_mdp[] = "Confirmation différente du nouveau mot de passe";
    // contrôle du nouveau mot de passe (différent de l'ancien)
    if (!empty($_POST["mdp"]) && $_POST["newmdp"] === $_POST["mdp"]) $errors_mdp[] = "Le nouveau mot de passe doit petre différent du mot de passe actuel";
    // contrôle du nombre d'erreur, si aucune, je peux procéder à la mise à jour
    if (empty($errors_mdp)) {
        $newmdp = password_hash($_POST["newmdp"], PASSWORD_DEFAULT);
        execRequete("UPDATE membre SET mdp=:newmdp WHERE id_membre=:id_membre", array('newmdp' => $newmdp, 'id_membre' => $_SESSION["membre"]["id_membre"]));
        $_SESSION["membre"]["mdp"] = $newmdp;
        $_SESSION["message2"] = 'Mot de passe mis à jour';
        header('location:' . $_SERVER["PHP_SELF"]);
        exit();
    }
}

require_once('inc/header.php');

// corps de la page
?>

<div class="row mt-2">
    <div class="col-md-6">
        <form method="post">
            <h2>Identifiants</h2>
            <hr>
            <p>Pseudo : <strong><?php echo $_SESSION["membre"]["pseudo"] ?></strong></p>
            <?php if (!empty($errors_coord)) : ?>
                <div class="alert alert-danger mt-3">
                    <?php echo implode('<br>', $errors_coord) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION["message"])) : ?>
                <div class="alert alert-success mt-3">
                    <?php echo $_SESSION["message"] ?>
                </div>
            <?php
                unset($_SESSION["message"]);
            endif; ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control <?php echo (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?php echo $_POST["email"] ?? $_SESSION["membre"]["email"] ?>">
                <div class="invalid-feedback">Merci de saisir une adresse mail valide</div>
            </div>
            <h2>Identité</h2>
            <hr>
            <div class="form-row">
                <div class="form-group col-2">
                    <label for="civilite">Civilité</label>
                    <select name="civilite" id="civilite" class="form-control">
                        <option value="m">M</option>
                        <option value="f" <?php echo ((!empty($_POST['civilite']) && $_POST['civilite'] == "f") || ($_SESSION["membre"]['civilite'] == "f")) ? 'selected' : '' ?>>Mme</option>
                    </select>
                </div>
                <div class="form-group col">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ?? $_SESSION["membre"]['nom'] ?>">
                </div>
                <div class="form-group col">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ?? $_SESSION["membre"]['prenom'] ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" name="modif_coord">Mettre à jour</button>
        </form>
    </div>
    <div class="col-md-6">
        <h2>Changer le mot de passe</h2>
        <hr>
        <?php if (!empty($errors_mdp)) : ?>
            <div class="alert alert-danger mt-3">
                <?php echo implode('<br>', $errors_mdp) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION["message2"])) : ?>
            <div class="alert alert-success mt-3">
                <?php echo $_SESSION["message2"] ?>
            </div>
        <?php
            unset($_SESSION["message2"]);
        endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="mdp">Mot de passe actuel</label>
                <input type="password" class="form-control" id="mdp" name="mdp">
            </div>
            <div class="form-group">
                <label for="newmdp">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="newmdp" name="newmdp">
            </div>
            <div class="form-group">
                <label for="confirm">Confirmation</label>
                <input type="password" class="form-control" id="confirm" name="confirm">
            </div>
            <button type="submit" class="btn btn-primary" name="modif_mdp">Changer le mot de passe</button>
        </form>
    </div>
</div>

<?php
require_once('inc/footer.php');
