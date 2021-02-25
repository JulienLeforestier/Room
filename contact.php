<?php

require_once('inc/init.php');

$title = 'Contact';

require_once('inc/header.php');

// corps de la page
?>

<h1 class="mt-2">Contact</h1>
<hr>

<h2 class="aideForm">Comment pouvons-nous vous aider?</h2>
<div class="container-fluid justify-content-center">
    <form method="post">
        <div class="form-group">
            <label for="emailContact" class="form-label">Email</label>
            <input type="email" class="form-control" id="emailContact" pattern="^[^\W][a-zA-Z0-9]+(.[a-zA-Z0-9]+)@[a-zA-Z0-9]+(.[a-zA-Z0-9]+).[a-zA-Z]{2,4}$" required>
        </div>
        <div class="form-group">
            <label for="messageContact" class="form-label">Message</label>
            <textarea class="form-control" id="messageContact" placeholder="Laissez nous votre message" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
</div>
<div class="container mt-4">
    <div class="col-sm-12 text-center">
        <h2>Adresse et Téléphone</h2>
        <h3>Room</h3>
        <p>300 Boulevard de Sébastopol, 75003 Paris, France</p>
        <p>+33 (0)1 75 00 00 00 </p>
        <p><a href="">contact@room.com</a></p>
    </div>
</div>

<?php
require_once('inc/footer.php');
