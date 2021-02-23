<?php

function isConnected()
{
    // existence d'un index 'membre' dans le tableau $_SESSION indiquera que la phase de connexion s'est bien passée
    return isset($_SESSION['membre']);
}

function isAdmin()
{
    return (isConnected() && $_SESSION['membre']['statut'] == 1);
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

// fonctions liées au panier
function creerPanier()
{
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = array('id_produit' => array(), 'quantite' => array(), 'prix' => array());
}

function ajoutPanier($id_produit, $quantite, $prix)
{
    creerPanier();
    $position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']);
    if ($position_produit === false) {
        // nouveau produit dans le panier
        $_SESSION['panier']['id_produit'][] = $id_produit;
        $_SESSION['panier']['quantite'][] = $quantite;
        $_SESSION['panier']['prix'][] = $prix;
    } else {
        // produit présent dont on doit mettre à jour la quantité
        $_SESSION['panier']['quantite'][$position_produit] += $quantite;
    }
}

function retirerPanier($id_produit, $quantite)
{
    $position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']);
    if ($position_produit !== false) {
        if ($quantite == $_SESSION['panier']['quantite'][$position_produit]) {
            // retrait complet de la ligne du panier
            array_splice($_SESSION['panier']['id_produit'], $position_produit, 1);
            array_splice($_SESSION['panier']['quantite'], $position_produit, 1);
            array_splice($_SESSION['panier']['prix'], $position_produit, 1);
        } else {
            // mise à jour de la quantité
            $_SESSION['panier']['quantite'][$position_produit] -= $quantite;
        }
    }
}

function montantPanier()
{
    $total = 0;
    for ($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) {
        $total += $_SESSION['panier']['prix'][$i] * $_SESSION['panier']['quantite'][$i];
    }
    return $total;
}

function nbArticles()
{
    $nb = 0;
    if (!empty($_SESSION['panier']['id_produit'])) {
        $nb = '<span class="badge badge-primary">' . array_sum($_SESSION['panier']['quantite']) . '</span>';
    }
    return $nb;
}

function viderPanier()
{
    unset($_SESSION['panier']);
}
