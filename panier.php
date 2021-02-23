<?php

require_once('inc/init.php');

$title = 'Panier';

// valider un panier => commande
if (isset($_GET["action"]) && $_GET["action"] == 'valider') {
    $feu_vert = true;
    // contrôles de la situation prix/stock
    for ($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
        $resultat = execRequete("SELECT * FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_SESSION['panier']['id_produit'][$i]));
        $produit = $resultat->fetch();
        $message = '';
        // contrôles et réajustements du panier
        if (
            $_SESSION['panier']['quantite'][$i] > 10
            || $produit['stock'] < $_SESSION['panier']['quantite'][$i]
            || $produit['prix'] != $_SESSION['panier']['prix'][$i]
        ) $feu_vert = false;
    }

    if ($feu_vert) {
        // alimenter la table commande
        $id_membre = $_SESSION["membre"]["id_membre"]; // id client
        $montantTotal = montantPanier();
        execRequete(
            "INSERT INTO commande VALUES (NULL,:id_membre,:montant,NOW(),'en cours de traitement')",
            array('id_membre' => $id_membre, 'montant' => $montantTotal)
        );
        $id_commande = $pdo->lastInsertId();
        // alimenter la table details_commande
        for ($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
            $id_produit = $_SESSION['panier']['id_produit'][$i];
            $quantite = $_SESSION['panier']['quantite'][$i];
            $prix = $_SESSION['panier']['prix'][$i];
            execRequete(
                "INSERT INTO details_commande VALUES (NULL,:id_commande,:id_produit,:quantite,:prix)",
                array(
                    'id_commande' => $id_commande, 'id_produit' => $id_produit, 'quantite' => $quantite, 'prix' => $prix
                )
            );
            // mettre à jour le stock
            execRequete(
                "UPDATE produit SET stock=stock-:quantite WHERE id_produit=:id_produit",
                array('quantite' => $quantite, 'id_produit' => $id_produit)
            );
        }
        // vider le panier
        unset($_SESSION["panier"]);
        header('location:' . URL . 'commandes.php');
        exit();
    } else {
        $errors[] = "La commande n'a pas été validée en raison de modifications concernant le stock ou le prix des articles. Merci de valider à nouveau après vérification.";
    }
}

// on vérifie un index de post nous permettant d'identifier le formulaire de provenance
if (isset($_POST['ajout_panier'])) {
    $resultat = execRequete("SELECT prix FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_POST['id_produit']));
    if ($resultat->rowCount() == 1) {
        $produit = $resultat->fetch();
        ajoutPanier($_POST['id_produit'], $_POST['quantite'], $produit['prix']);
        header('location:' . URL . 'fiche.php?id_produit=' . $_POST['id_produit'] . '&sp=ok');
        exit();
    }
}

// modification de quantité depuis le panier
if (isset($_POST["majquantite"])) {
    $position_produit = array_search($_POST["id_produit"], $_SESSION["panier"]["id_produit"]);
    $_SESSION["panier"]["quantite"][$position_produit] = $_POST["quantite"];
}

// cas de suppression d'une ligne du panier
if (isset($_GET['action']) && $_GET['action'] == 'supligne' && !empty($_GET['id_produit'])) {
    $position_produit = array_search($_GET['id_produit'], $_SESSION['panier']['id_produit']);
    retirerPanier($_GET['id_produit'], $_SESSION['panier']['quantite'][$position_produit]);
}

require_once('inc/header.php');

// corps de la page
if (empty($_SESSION['panier']['id_produit'])) {
?>
    <div class="alert alert-info">Votre panier est vide</div>
<?php
} else {
?>
    <h2 class="mt-2">Voici votre panier : </h2>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger mt-3">
            <?php echo implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <tr>
            <th>Référence</th>
            <th>Titre</th>
            <th>Quantité</th>
            <th>Prix unitaire</th>
            <th>Total</th>
            <th>Action</th>
        </tr>
        <?php
        for ($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
            $resultat = execRequete("SELECT * FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_SESSION['panier']['id_produit'][$i]));
            $produit = $resultat->fetch();
            $message = '';
            // contrôles et réajustements du panier
            if ($_SESSION['panier']['quantite'][$i] > 10) {
                $_SESSION['panier']['quantite'][$i] = 10;
                $message = '<br>La quantité a été réajustée en fonction du stock et dans la limite de 10 exemplaires de ce produit par commande. ';
            }
            if ($produit['stock'] < $_SESSION['panier']['quantite'][$i]) {
                $_SESSION['panier']['quantite'][$i] = $produit['stock'];
                $message = '<br>La quantité a été réajustée en fonction du stock et dans la limite de 10 exemplaires de ce produit par commande. ';
            }
            if ($produit['prix'] != $_SESSION['panier']['prix'][$i]) {
                $_SESSION['panier']['prix'][$i] = $produit['prix'];
                $message .= '<br>Le prix a été actualisé.';
            }
        ?>
            <tr>
                <td><?php echo $produit['reference'] ?></td>
                <td class="w-25">
                    <br>
                    <img src="<?php echo URL . 'photos/' . $produit['photo'] ?>" alt="<?php echo $produit['titre'] ?>" class="img-fluid vignette">
                    <?php echo $produit['titre'] . $message ?>
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id_produit" value="<?php echo $produit['id_produit'] ?>">
                        <?php $quantite = $_SESSION['panier']['quantite'][$i] ?>
                        <div class="form-row">
                            <div class="form-group col-4">
                                <input type="number" name="quantite" value="<?php echo $quantite ?>" min="1" max="<?php echo ($produit['stock'] < 10) ? $produit['stock'] : 10 ?>" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-8">
                                <button type="submit" name="majquantite" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i></button>
                            </div>
                        </div>
                    </form>
                </td>
                <td><?php echo number_format($_SESSION['panier']['prix'][$i], 2, ',', '&nbsp;') . '&euro;' ?></td>
                <td><?php echo number_format($_SESSION['panier']['prix'][$i] * $_SESSION['panier']['quantite'][$i], 2, ',', '&nbsp;') . '&euro;' ?></td>
                <td><a href="?action=supligne&id_produit=<?php echo $produit['id_produit'] ?>"><i class="fas fa-trash-alt"></i></a></td>
            </tr>
        <?php
        }
        ?>
        <tr>
            <th colspan="4" class="text-right">Montant total</th>
            <th colspan="2"><?php echo number_format(montantPanier(), 2, ',', '&nbsp;') . '&euro;' ?></th>
        </tr>
    </table>
    <?php
    if (isConnected()) {
    ?>
        <div class="d-flex justify-content-end">
            <a href="?action=valider" class="btn btn-primary">Commander</a>
        </div>
    <?php
    } else {
    ?>
        <p class="alert alert-info">
            Veuillez vous <a href="<?php echo URL . 'inscription.php' ?>">inscrire</a> ou vous <a href="<?php echo URL . 'connexion.php' ?>">connecter</a>
            afin de valider votre commande.
        </p>
<?php
    }
}

require_once('inc/footer.php');
