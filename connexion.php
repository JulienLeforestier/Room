<?php

require_once('inc/init.php');

$title = 'Connexion';

// gérer la déconnexion
if (isset($_GET['action']) && $_GET['action'] == 'deconnexion') {
    // destruction totale de la session
    // session_destroy();
    // destruction de la variable membre (on conserve un éventuel panier)
    unset($_SESSION['membre']);
    header('location:' . URL);
    exit();
}

// contrôle si le formulaire est posté
if (!empty($_POST)) {
    // contrôles des champs
    if (empty($_POST['pseudo']) || empty($_POST['mdp'])) $errors[] = 'Merci de remplir tous les champs';

    if (empty($errors)) {
        // contrôle l'existence de l'utilisateur
        if ($membre = getMembreByPseudo($_POST['pseudo'])) {
            // contrôle du mdp
            $infos = $membre->fetch();
            if (password_verify($_POST['mdp'], $infos['mdp'])) {
                $_SESSION['membre'] = $infos;
                header('location:' . URL . 'compte.php');
                exit();
            } else $errors[] = 'Erreur sur les identifiants';
        } else $errors[] = 'Erreur sur les identifiants';
    }
}

require_once('inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Connexion</h1>
<hr>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger mt-3">
        <?php echo implode('<br>', $errors) ?>
    </div>
<?php endif; ?>
<form method="post" class="pb-4">
    <div class="form-group">
        <label for="pseudo">Pseudo</label>
        <input type="text" class="form-control 
            <?php echo (!empty($_POST) && empty(trim($_POST['pseudo']))) ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ?? '' ?>">
        <div class="invalid-feedback">Merci de remplir ce champ</div>
    </div>
    <div class="form-group">
        <label for="mdp">Mot de passe</label>
        <input type="password" class="form-control
            <?php echo (!empty($_POST) && empty(trim($_POST['mdp']))) ? 'is-invalid' : '' ?>" id="mdp" name="mdp">
        <div class="invalid-feedback">Merci d'indiquer votre mot de passe</div>
    </div>
    <button type="submit" class="btn btn-primary">Se connecter</button>
</form>

<?php
require_once('inc/footer.php');
