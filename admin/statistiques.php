<?php

require_once('../inc/init.php');

$title = 'Statistiques';

if (!isAdmin()) {
    header('location:' . URL . 'compte.php');
    exit();
}

// salles les mieux notées
$salles_mieux_notees = execRequete("SELECT titre,ROUND(AVG(note),1) AS note_moyenne FROM salle s 
                                    INNER JOIN avis a ON a.id_salle = s.id_salle
                                    GROUP BY titre
                                    ORDER BY note_moyenne DESC
                                    LIMIT 5");

// salles les plus commandées
$salles_plus_commandees = execRequete(" SELECT titre,COUNT(id_commande) AS nombre_de_commande FROM salle s 
                                        INNER JOIN produit p ON p.id_salle = s.id_salle
                                        INNER JOIN commande c ON c.id_produit = p.id_produit
                                        GROUP BY titre
                                        ORDER BY nombre_de_commande DESC
                                        LIMIT 5");

// membres qui achètent le plus (en quantité)
$membres_achetent_quantite = execRequete("  SELECT pseudo,nom,prenom,email,COUNT(id_commande) AS nombre_de_commande FROM commande c
                                            INNER JOIN membre m ON m.id_membre = c.id_membre
                                            GROUP BY pseudo
                                            ORDER BY nombre_de_commande DESC
                                            LIMIT 5");

// membres qui achètent le plus (en prix)
$membres_achetent_prix = execRequete("  SELECT pseudo,nom,prenom,email,SUM(prix) AS prix_total FROM commande c
                                        INNER JOIN membre m ON m.id_membre = c.id_membre
                                        INNER JOIN produit p ON p.id_produit = c.id_produit
                                        GROUP BY pseudo
                                        ORDER BY prix_total DESC
                                        LIMIT 5");

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Statistiques (Top 5)</h1>
<hr>

<div id="tabs">
    <ul>
        <li><a href="#tabs-1">Salles les mieux notées</a></li>
        <li><a href="#tabs-2">Salles les plus commandées</a></li>
        <li><a href="#tabs-3">Meilleurs acheteurs (en quantité)</a></li>
        <li><a href="#tabs-4">Meilleurs acheteurs (en prix)</a></li>
    </ul>
    <div id="tabs-1">
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <!-- entêtes de colonne -->
                <?php for ($i = 0; $i < $salles_mieux_notees->columnCount(); $i++) :
                    $colonne = $salles_mieux_notees->getColumnMeta($i);
                ?>
                    <th><?php echo ucfirst($colonne['name']); ?></th>
                <?php endfor; ?>
            </tr>
            <!-- données de colonne -->
            <?php while ($ligne = $salles_mieux_notees->fetch()) : ?>
                <tr>
                    <?php foreach ($ligne as $key => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <div id="tabs-2">
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <!-- entêtes de colonne -->
                <?php for ($i = 0; $i < $salles_plus_commandees->columnCount(); $i++) :
                    $colonne = $salles_plus_commandees->getColumnMeta($i);
                ?>
                    <th><?php echo ucfirst($colonne['name']); ?></th>
                <?php endfor; ?>
            </tr>
            <!-- données de colonne -->
            <?php while ($ligne = $salles_plus_commandees->fetch()) : ?>
                <tr>
                    <?php foreach ($ligne as $key => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <div id="tabs-3">
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <!-- entêtes de colonne -->
                <?php for ($i = 0; $i < $membres_achetent_quantite->columnCount(); $i++) :
                    $colonne = $membres_achetent_quantite->getColumnMeta($i);
                ?>
                    <th><?php echo ucfirst($colonne['name']); ?></th>
                <?php endfor; ?>
            </tr>
            <!-- données de colonne -->
            <?php while ($ligne = $membres_achetent_quantite->fetch()) : ?>
                <tr>
                    <?php foreach ($ligne as $key => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <div id="tabs-4">
        <table class="table table-bordered table-striped table-responsive-lg">
            <tr>
                <!-- entêtes de colonne -->
                <?php for ($i = 0; $i < $membres_achetent_prix->columnCount(); $i++) :
                    $colonne = $membres_achetent_prix->getColumnMeta($i);
                ?>
                    <th><?php echo ucfirst($colonne['name']); ?></th>
                <?php endfor; ?>
            </tr>
            <!-- données de colonne -->
            <?php while ($ligne = $membres_achetent_prix->fetch()) : ?>
                <tr>
                    <?php foreach ($ligne as $key => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php
require_once('../inc/footer.php');
