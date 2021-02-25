<?php

require_once('inc/init.php');

$title = 'Accueil';

require_once('inc/header.php');

$categories = execRequete(" SELECT DISTINCT categorie FROM salle ORDER BY categorie");
$villes = execRequete(" SELECT DISTINCT ville FROM salle ORDER BY ville");

$whereclause = "WHERE etat != 'reservation'";
$args = array();

if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        $whereclause .= ' AND ' . $key . '=:' . $key;
        $args[$key] = $value;
    }
}

$produits = execRequete("   SELECT * FROM salle s
                            INNER JOIN produit p ON p.id_salle = s.id_salle
                            LEFT JOIN avis a ON a.id_salle = s.id_salle
                            $whereclause
                            GROUP BY id_produit", $args);

// corps de la page
?>

<div class="row mt-3 mb-5">
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
        <p class="lead pt-3">Villes</p>
        <div class="list-group">
            <select class="form-control">
                <?php while ($ville = $villes->fetch()) : ?>
                    <option class="list-group-item <?php echo (isset($_GET['ville']) && $_GET['ville'] == $ville['ville']) ? 'active' : '' ?>" href="?ville=<?php echo $ville['ville'] ?>">
                        <?php echo ucfirst($ville['ville']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <p class="lead pt-3">Capacité</p>
        <div class="list-group">
            <input type="number" class="form-control" id="capacite" name="capacite" value="50">
        </div>
        <p class="lead pt-3">Prix</p>
        <div class="list-group">
            <input type="range" id="prix" name="prix" class="range" min="100" max="10000" value="10000" step="100">
            <output></output>
        </div>
        <p class="lead pt-3">Période</p>
        <div class="list-group">
            <label for="date_arrivee">Date d'arrivée</label>
            <input type="text" class="form-control" id="date_arrivee" name="date_arrivee" placeholder="00/00/0000 00:00">
        </div>
        <div class="list-group">
            <label for="date_depart">Date de départ</label>
            <input type="text" class="form-control" id="date_depart" name="date_depart" placeholder="00/00/0000 00:00">
        </div>
    </div>
    <div class="col-md-9">
        <?php if ($produits->rowCount() == 0) : ?>
            <div class="alert alert-info mt-5">Plus de produits pour le moment. Revenez bientôt!</div>
        <?php else : ?>
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
