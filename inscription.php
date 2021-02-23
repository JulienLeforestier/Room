<?php

require_once('inc/init.php');

$title = 'Inscription';

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
    // contrôle du mdp
    if (!preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['mdp'])) $errors[] = "Complexité du mot de passe non respectée";
    // contrôle de l'email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Format de mail invalide";
    // contrôle de l'unicité du pseudo
    if (getMembreByPseudo($_POST['pseudo'])) $errors[] = "Pseudo indisponible, merci d'en choisir un autre";
    // contrôle du nombre d'erreur, si aucune, je peux procéder à l'inscription
    if (empty($errors)) {
        $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        execRequete("INSERT INTO membre VALUES (NULL,:pseudo,:mdp,:nom,:prenom,:email,:civilite,0,NOW())", $_POST);
        $_SESSION['membre'] = getMembreByPseudo($_POST['pseudo'])->fetch();
        header('location:' . URL . 'compte.php');
        exit(); // stop la suite du script en attendant la redirection
    }
}

require_once('inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Inscription</h1>
<hr>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger mt-3">
        <?php echo implode('<br>', $errors) ?>
    </div>
<?php endif; ?>
<form method="post" class="pb-4">
    <fieldset>
        <legend>Identifiants</legend>
        <div class="form-group">
            <label for="pseudo">Pseudo</label>
            <input type="text" class="form-control 
            <?php echo (!empty($_POST) &&
                (empty(trim($_POST['pseudo'])) ||
                    iconv_strlen(trim($_POST['pseudo'])) < 2 ||
                    iconv_strlen(trim($_POST['pseudo'])) > 20))
                ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ?? '' ?>">
            <!-- trim() retire les espaces au debut et en fin de chaine -->
            <div class="invalid-feedback">Merci de renseigner un pseudo (entre 2 et 20 caractères)</div>
        </div>
        <div class="form-group">
            <label for="mdp">Mot de passe</label>
            <input type="password" class="form-control
            <?php echo (!empty($_POST) &&
                !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['mdp']))
                ? 'is-invalid' : '' ?>" id="mdp" name="mdp">
            <div class="invalid-feedback">Merci de saisir un mot de passe entre 8 et 20 caractères comportant au moins 1 miniscule, 1 majuscule, 1 chiffre et 1 caractère spécial ($ ! - _ @)</div>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control
            <?php echo (!empty($_POST) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?php echo $_POST['email'] ?? '' ?>">
            <div class="invalid-feedback">Merci de saisir une adresse mail valide</div>
        </div>
    </fieldset>
    <fieldset>
        <legend>Coordonnées</legend>
        <div class="form-row">
            <div class="form-group col-2">
                <label for="civilite">Civilité</label>
                <select name="civilite" id="civilite" class="form-control">
                    <option value="m">M</option>
                    <option value="f" <?php echo (!empty($_POST) && $_POST['civilite'] == "f") ? 'selected' : '' ?>>Mme</option>
                </select>
            </div>
            <div class="form-group col">
                <label for="nom">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $_POST['nom'] ?? '' ?>">
            </div>
            <div class="form-group col">
                <label for="prenom">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $_POST['prenom'] ?? '' ?>">
            </div>
        </div>
    </fieldset>
    <button type="submit" class="btn btn-primary">S'inscrire</button>
</form>

<?php
require_once('inc/footer.php');
