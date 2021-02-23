<?php

require_once('../inc/init.php');

$title = 'Gestion des membres';

if (!isAdmin()) {
    header('location:' . URL . 'compte.php');
    exit();
}

// action de changement de rôle
if (!empty($_POST)) {
    execRequete("UPDATE membre SET statut=:newstatut WHERE id_membre=:id_membre", array('newstatut' => $_POST["newstatut"], 'id_membre' => $_POST["id_membre"]));
}

require_once('../inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Gestion des membres</h1>
<hr>

<?php
// affichage des membres
$resultats = execRequete('SELECT * FROM membre ORDER BY nom,prenom');
?>
<p class="py-2">Nombre de membres : <?php echo $resultats->rowCount() ?></p>
<table class="table table-bordered table-striped table-responsive-lg">
    <tr>
        <?php
        // entêtes de colonne
        for ($i = 0; $i < $resultats->columnCount(); $i++) {
            $colonne = $resultats->getColumnMeta($i);
            if ($colonne['name'] != 'mdp') {
                // var_dump(get_class_methods($resultats));
        ?>
                <th><?php echo ucfirst($colonne['name']); ?></th>
        <?php
            }
        }
        ?>
        <th>Rôle</th>
        
    </tr>
    <?php
    // données de colonne
    while ($membre = $resultats->fetch()) {
    ?>
        <tr>
            <?php
            foreach ($membre as $key => $value) {
                if ($key != 'mdp') {
                    switch ($key) {
                        case 'civilite':
                            $publics = array('m' => 'Homme', 'f' => 'Femme');
                            $value = $publics[$value];
                            break;
                        case 'statut':
                            $roles = array('0' => 'Membre', '1' => 'Admin');
                            $value = $roles[$value];
                            break;
                    }
            ?>
                    <td><?php echo $value ?></td>
            <?php
                }
            }
            // possibilité de promouvoir les membres
            ?>
            <td>
                <?php if ($membre['id_membre'] != $_SESSION["membre"]["id_membre"]) { ?>
                    <form method="post">
                        <input type="hidden" name="id_membre" value="<?php echo $membre['id_membre'] ?>">
                        <div class="form-check form-chek-inline">
                            <input class="form-check-input" type="radio" id="membre" name="newstatut" value="0" <?php echo ($membre["statut"] == 0) ? 'checked' : '' ?>>
                            <label for="membre">Membre</label>
                        </div>
                        <div class="form-check form-chek-inline">
                            <input class="form-check-input" type="radio" id="admin" name="newstatut" value="1" <?php echo ($membre["statut"] == 1) ? 'checked' : '' ?>>
                            <label for="admin">Admin</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i></button>
                    </form>
                <?php } else echo '<hr>'; ?>
            </td>
        </tr>
    <?php
    }
    ?>
</table>
<?php
require_once('../inc/footer.php');
