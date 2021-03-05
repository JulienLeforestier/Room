<?php

require_once('init.php');

$whereclause = "WHERE etat != 'reservation' AND date_arrivee >= NOW()";
$args = array();

if (!empty($_GET)) {
    foreach ($_GET as $key => $value) {
        if ($key == 'capacite') $whereclause .= ' AND ' . $key . '>=:' . $key;
        else if ($key == 'prix') $whereclause .= ' AND ' . $key . '<=:' . $key;
        else if ($key == 'date_arrivee') $whereclause .= ' AND ' . $key . '>=:' . $key;
        else if ($key == 'date_depart') $whereclause .= ' AND ' . $key . '<=:' . $key;
        else $whereclause .= ' AND ' . $key . '=:' . $key;
        $args[$key] = $value;
    }
}

$produits = execRequete("   SELECT * FROM salle s
                            INNER JOIN produit p ON p.id_salle = s.id_salle
                            LEFT JOIN avis a ON a.id_salle = s.id_salle
                            $whereclause
                            GROUP BY id_produit", $args);

//corps de la page
if ($produits->rowCount() == 0) : ?>
    <div class="alert alert-info mt-5">Aucun produit ne correspond à vos critères de recherche pour le moment. <a href="">Réinitialiser les filtres</a></div>
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
<?php endif;
