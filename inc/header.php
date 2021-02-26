<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room | <?php echo $title ?></title>
    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <!-- jquery ui css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" integrity="sha512-aOG0c6nPNzGk+5zjwyJaoRUgCdOrfSDhmMID2u4+OIslr0GjpLKo7Xm0Ao3xmpM4T8AmIouRkqwj1nrdVsLKEQ==" crossorigin="anonymous" />
    <!-- timepicker css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css" integrity="sha512-LT9fy1J8pE4Cy6ijbg96UkExgOjCqcxAC7xsnv+mLJxSvftGVmmc236jlPTZXPcBRQcVOWoK1IJhb1dAjtb4lQ==" crossorigin="anonymous" />
    <!-- css font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <!-- style css perso -->
    <link rel="stylesheet" href="<?php echo URL ?>inc/css/style.css">
    <!-- scripts jquery & bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <!-- script jquery ui -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous"></script>
    <!-- script timepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js" integrity="sha512-s5u/JBtkPg+Ff2WEr49/cJsod95UgLHbC00N/GglqdQuLnYhALncz8ZHiW/LxDRGduijLKzeYb7Aal9h3codZA==" crossorigin="anonymous"></script>
    <!-- script dayjs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.10.4/dayjs.min.js" integrity="sha512-0fcCRl828lBlrSCa8QJY51mtNqTcHxabaXVLPgw/jPA5Nutujh6CbTdDgRzl9aSPYW/uuE7c4SffFUQFBAy6lg==" crossorigin="anonymous"></script>
    <!-- script perso -->
    <script src="<?php echo URL ?>inc/js/functions.js"></script>
    <!-- script datetimepicker -->
    <script src="<?php echo URL ?>inc/js/datetimepicker.js"></script>
    <!-- script ajax -->
    <script src="<?php echo URL ?>inc/js/ajax.js"></script>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
            <a class="navbar-brand" href="<?php echo URL ?>">Room</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto ml-auto">
                    <li class="nav-item <?php echo ($title == 'Accueil') ? 'active' : '' ?>">
                        <a class="nav-link" href="<?php echo URL ?>">Accueil<span class="sr-only">(current)</span></a>
                    </li>
                    <!-- Membre non connecté -->
                    <?php if (!isConnected()) : ?>
                        <li class="nav-item <?php echo ($title == 'Inscription') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>inscription.php">Inscription</a>
                        </li>
                        <li class="nav-item <?php echo ($title == 'Connexion') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>connexion.php">Connexion</a>
                        </li>
                    <?php endif; ?>
                    <!-- Membre connecté -->
                    <?php if (isConnected()) : ?>
                        <li class="nav-item <?php echo ($title == 'Mon compte') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>compte.php">Mon compte</a>
                        </li>
                        <li class="nav-item <?php echo ($title == 'Mes commandes') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>commandes.php">Mes commandes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL ?>connexion.php?action=deconnexion">Se déconnecter</a>
                        </li>
                    <?php endif; ?>
                    <!-- Administrateur -->
                    <?php if (isAdmin()) : ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="menuadmin" data-toggle="dropdown">Admin</a>
                            <div class="dropdown-menu" aria-labelledby="menudamin">
                                <a class="dropdown-item" href="<?php echo URL ?>admin/gestion_salles.php">Gestion des salles</a>
                                <a class="dropdown-item" href="<?php echo URL ?>admin/gestion_produits.php">Gestion des produits</a>
                                <a class="dropdown-item" href="<?php echo URL ?>admin/gestion_membres.php">Gestion des membres</a>
                                <a class="dropdown-item" href="<?php echo URL ?>admin/gestion_avis.php">Gestion des avis</a>
                                <a class="dropdown-item" href="<?php echo URL ?>admin/gestion_commandes.php">Gestion des commandes</a>
                                <a class="dropdown-item" href="<?php echo URL ?>admin/statistiques.php">Statistiques</a>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
                <form class="form-inline mt-2 mt-md-0" method="post" action="<?php echo URL ?>recherche.php">
                    <input class="form-control mr-sm-2" type="text" placeholder="Produit, catégorie..." aria-label="Search" name="critere">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Rechercher</button>
                </form>
            </div>
        </nav>
    </header>
    <main class="container">