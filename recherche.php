<?php

require_once('inc/init.php');

$title = 'Recherche';

if (empty(trim(htmlspecialchars($_POST["critere"])))) {
    header('location:' . URL);
    exit();
}

$critere = $_POST["critere"];
$resultats = execRequete("  SELECT * FROM salle s
                            INNER JOIN produit p ON p.id_salle = s.id_salle
                            WHERE titre LIKE CONCAT('%',:critere,'%')
                            OR description LIKE CONCAT('%',:critere,'%')
                            OR categorie LIKE CONCAT('%',:critere,'%')
                            OR prix LIKE CONCAT('%',:critere,'%')
                        ", array('critere' => $critere));

require_once('inc/header.php');

// corps de la page
?>

<div class="row mt-2">
    <div class="col">
        <?php if ($resultats->rowCount() == 0) : ?>
            <div class="alert alert-info">Nous n'avons pas trouvé de produit correspondant à votre recherche :
                <?php echo trim(htmlspecialchars($critere)) ?>
            </div>
        <?php else : ?>
            <p class="lead">Nous avons trouvé <?php echo $resultats->rowCount() ?> article<?php echo ($resultats->rowCount() > 1) ? 's' : ''  ?></p>
            <div class="row">
                <?php while ($produit = $resultats->fetch()) : ?>
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
