<?php

require_once('inc/init.php');

$title = 'Recherche';

if (empty(trim(htmlspecialchars($_POST["critere"])))) {
    header('location:' . URL);
    exit();
}

$critere = $_POST["critere"];

$produits = execRequete("  SELECT * FROM salle s
                            INNER JOIN produit p ON p.id_salle = s.id_salle
                            LEFT JOIN avis a ON a.id_salle = s.id_salle
                            WHERE titre LIKE CONCAT('%',:critere,'%')
                            OR description LIKE CONCAT('%',:critere,'%')
                            OR categorie LIKE CONCAT('%',:critere,'%')
                            OR prix LIKE CONCAT('%',:critere,'%')
                            GROUP BY id_produit", array('critere' => $critere));

require_once('inc/header.php');

// corps de la page
?>

<div class="row my-5">
    <div class="col">
        <?php if ($produits->rowCount() == 0) : ?>
            <div class="alert alert-info">Nous n'avons pas trouvé de produit correspondant à votre recherche :
                <?php echo trim(htmlspecialchars($critere)) ?>
            </div>
        <?php else : ?>
            <p class="lead">Nous avons trouvé <?php echo $produits->rowCount() ?> produit<?php echo ($produits->rowCount() > 1) ? 's' : ''  ?></p>
            <div class="row col-lg-12 justify-content-center">
                <?php while ($produit = $produits->fetch()) : ?>
                    <div class="col-md-4 p-1">
                        <div class="border">
                            <div class="thumbmail">
                                <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>">
                                    <img src="<?php echo URL . 'photos/' . $produit['photo'] ?>" alt="<?php echo $produit['titre'] ?>" class="img-fluid">
                                </a>
                                <div class="caption m-2">
                                    <h4 class="float-right"><?php echo number_format($produit['prix'], 2, ',', '&nbsp;') . '&euro;' ?></h4>
                                    <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>">
                                        <h4><?php echo $produit['titre'] ?></h4>
                                    </a>
                                </div>
                                <div class="caption m-2">
                                    <?php
                                    $value = $produit['description'];
                                    $extrait = (iconv_strlen($value) > 30) ? substr($value, 0, 30) : $value;
                                    if ($extrait != $value) {
                                        $lastSpace = strrpos($extrait, ' ');
                                        $value = substr($extrait, 0, $lastSpace)  . '...';
                                    }
                                    echo $value;
                                    ?>
                                </div>
                                <div class="caption m-2">
                                    <?php echo 'Du ' . date('d/m/Y', strtotime($produit['date_arrivee'])) . ' au ' . date('d/m/Y', strtotime($produit['date_depart'])) ?>
                                </div>
                                <div class="caption m-2">
                                    <?php if ($produit['note']) : ?>
                                        <!-- calcul de la note moyenne de la salle actuelle -->
                                        <?php $note = execRequete(" SELECT ROUND(AVG(note)) AS note_moyenne FROM salle s 
                                                                    INNER JOIN avis a ON a.id_salle = s.id_salle
                                                                    WHERE s.id_salle = :id_salle", array('id_salle' => $produit['id_salle']))->fetch()['note_moyenne']; ?>
                                        <?php switch ($note) {
                                            case '1':
                                                echo '★';
                                                break;
                                            case '2':
                                                echo '★★';
                                                break;
                                            case '3':
                                                echo '★★★';
                                                break;
                                            case '4':
                                                echo '★★★★';
                                                break;
                                            case '5':
                                                echo '★★★★★';
                                                break;
                                        }
                                        ?>
                                    <?php endif; ?>
                                    <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>" class="float-right"><i class="fas fa-search"></i> Voir</a>
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
