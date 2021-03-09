<?php

// définition du fuseau horaire
date_default_timezone_set('Europe/Paris');

// ouverture de session
session_start();

// connexion à la BDD
try {
    if (preg_match('#^localhost$#', $_SERVER['HTTP_HOST'])) {
        $pdo = new PDO(
            'mysql:host=localhost;charset=utf8;dbname=psl75351', // dsn
            'root', // login
            '', // mdp
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // règle par défaut les fetch sur fetch_assoc
            )
        );
        // constante de site
        define('URL', '/workspacevsc/room/');
    } else {
        $pdo = new PDO(
            'mysql:host=cl1-sql11;charset=utf8;dbname=psl75351', // dsn
            'psl75351', // login
            '150m!AFTH2', // mdp
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // règle par défaut les fetch sur fetch_assoc
            )
        );
        // constante de site
        define('URL', 'https://www.jleforestier.fr/Room/');
    }
} catch (PDOException $e) {
    echo $e->getMessage() . '<br>Fichier : ' . $e->getFile() . '<br>Ligne : ' . $e->getLine() . '<br>';
    die("Site indisponible. Contactez l'administrateur.");
}

// inclusion du fichier de functions
require_once('functions.php');
