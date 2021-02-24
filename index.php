<?php

require_once('inc/init.php');

$title = 'Accueil';

require_once('inc/header.php');

$categories = execRequete(" SELECT DISTINCT categorie FROM salle 
                            ORDER BY categorie");

$whereclause = '';
$args = array();
// gestion d'un éventuel filtre sur la catégorie
if (!empty($_GET['categorie'])) {
    $whereclause = 'WHERE categorie=:categorie';
    $args['categorie'] = $_GET['categorie'];
}

$produits = execRequete("SELECT * FROM salle $whereclause", $args);

// corps de la page
?>

<div class="row mt-2">
    <div class="col-md-3">
        <p class="lead pt-3">Catégories</p>
        <div class="list-group">
            <a class="list-group-item <?php echo (!isset($_GET['categorie'])) ? 'active' : '' ?>" href="<?php echo URL ?>">Toutes</a>
            <?php while ($categorie = $categories->fetch()) : ?>
                <a class="list-group-item <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == $categorie['categorie']) ? 'active' : '' ?>" href="?categorie=<?php echo $categorie['categorie'] ?>">
                    <?php
                    switch ($categorie['categorie']) {
                        case 'reunion':
                            echo 'Réunion';
                            break;
                        case 'formation':
                            echo 'Formation';
                            break;
                        case 'bureau':
                            echo 'Bureau';
                            break;
                    }
                    ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
    <div class="col-md-9">
        <?php if ($produits->rowCount() == 0) : ?>
            <div class="alert alert-info mt-5">Pas encore de produits. Revenez bientôt!</div>
        <?php else : ?>
            <div class="row">
                <?php while ($produit = $produits->fetch()) : ?>
                    <div class="col-md-4 p-1">
                        <div class="border">
                            <div class="thumbmail">
                                <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>">
                                    <img src="<?php echo URL . 'photos/' . $produit['photo'] ?>" alt="<?php echo $produit['titre'] ?>" class="img-fluid">
                                </a>
                                <div class="caption m-2">
                                    <h4 class="float-right"><?php echo number_format($produit['prix'], 2, ',', '$nbsp;') . '&euro;' ?></h4>
                                    <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>">
                                        <h4><?php echo $produit['titre'] ?></h4>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once('inc/footer.php');
