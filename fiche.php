<?php

require_once('inc/init.php');

$title = 'Fiche produit';

if (!empty($_GET['id_produit'])) {
    $produit = execRequete("SELECT * FROM produit WHERE id_produit=:id_produit", array('id_produit' => $_GET['id_produit']));
    if ($produit->rowCount() == 0) {
        $errors[] = 'Référence inexistante <a href="' . URL . '">Revenir à l\'accueil</a>';
    } else {
        $infos = $produit->fetch();
        $title .= ' : ' . $infos['titre'];
    }
} else {
    header('location:' . URL);
    exit();
}

require_once('inc/header.php');

// corps de la page
if (!empty($errors)) : ?>
    <div class="alert alert-danger mt-3">
        <?php echo implode('<br>', $errors) ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col">
        <?php if (!empty($infos)) : ?>
            <h1 class="page-header text-center mt-5"><?php echo $infos['titre'] ?></h1>
            <div class="row">
                <div class="col-md-7">
                    <img src="<?php echo URL . 'photos/' . $infos['photo'] ?>" alt="<?php echo $infos['titre'] ?>" class="img-fluid">
                </div>
                <div class="col-md-5">
                    <h2>Description</h2>
                    <p><?php echo $infos['description'] ?></p>
                    <h2>Détails</h2>
                    <ul>
                        <li>Catégorie : <?php echo $infos['categorie'] ?></li>
                        <li>Taille : <?php echo $infos['taille'] ?></li>
                        <li>Couleur : <?php echo $infos['couleur'] ?></li>
                    </ul>
                    <p class="lead">Prix : <?php echo number_format($infos['prix'], 2, ',', '&nbsp;') ?>&euro;</p>
                    <?php if ($infos['stock'] > 0) : ?>
                        <?php if ($infos['stock'] > 5) : ?>
                            <p class="text-success">En stock</p>
                        <?php else : ?>
                            <p class="text-warning">Plus que <?php echo $infos['stock'] ?> exemplaires</p>
                        <?php endif; ?>
                        <!-- formulaire d'ajout au panier -->
                        <form action="panier.php" method="post">
                            <input type="hidden" name="id_produit" value="<?php echo $infos['id_produit'] ?>">
                            <div class="form-row">
                                <div class="form-group col-2">
                                    <select name="quantite" class="form-control">
                                        <?php
                                        for ($i = 1; $i <= $infos['stock'] && $i <= 10; $i++) {
                                        ?>
                                            <option><?php echo $i ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-10">
                                    <button type="submit" name="ajout_panier" class="btn btn-primary">Ajouter au panier</button>
                                </div>
                            </div>
                        </form>
                    <?php else : ?>
                        <p class="alert alert-warning">Produit en cours de réapprovisionnement</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
if (isset($_GET['sp']) && $_GET['sp'] == 'ok') {
?>
    <div class="modal fade" id="modalConfirm" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Le produit a été ajouté au panier</h4>
                </div>
                <div class="modal-body">
                    <a class="btn btn-primary" href="<?php echo URL . 'panier.php' ?>">Voir le panier</a>
                    <a class="btn btn-primary" href="<?php echo URL ?>">Continuer mes achats</a>
                </div>
            </div>
        </div>
    </div>
<?php
}

require_once('inc/footer.php');
