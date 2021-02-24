<?php

function isConnected()
{
    // existence d'un index 'membre' dans le tableau $_SESSION indiquera que la phase de connexion s'est bien passée
    return isset($_SESSION['membre']);
}

function isAdmin()
{
    return (isConnected() && $_SESSION['membre']['statut'] == 2);
}

function execRequete($requete, $params = array())
{
    global $pdo; // je rend accessible la variable de l'espace global de PHP
    $r = $pdo->prepare($requete);
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $params[$key] = htmlspecialchars($value, ENT_QUOTES);
            $r->bindValue($key, $params[$key], PDO::PARAM_STR);
        }
    }
    $r->execute();
    // on vérifie que l'exécution de la requête préparée ne renvoie pas d'erreur
    if (!empty($r->errorInfo()[2])) die("Erreur rencontrée, merci de contacter l'administrateur.");
    return $r;
}

// contrôler l'existance d'un pseudo, le cas échéant retourner toutes les infos de ce membre
function getMembreByPseudo($pseudo)
{
    $resultat = execRequete("SELECT * FROM membre WHERE pseudo=:pseudo", array('pseudo' => $pseudo));
    if ($resultat->rowCount() > 0) return $resultat;
    else return false;
}
