</main>
<footer class="container-fluid">
    <div class="row">
        <div class="col bg-dark text-light text-center py-3 w-100">
            . <a href="mentions_legales.php">Mentions légales</a> . <a href="cgv.php">Conditions générales de ventes</a> . <a href="contact.php">Contact</a> .
            <br>
            &copy; <?php echo date('Y') ?> - Room - Tous droits réservés
        </div>
    </div>
</footer>
<?php if (!isset($_COOKIE["acceptcookies"])) : ?>
    <!-- justify-content-center nécessite d-flex au préalable, mm chose pr align-items-center -->
    <div id="bandeaucookies" class="bg-primary w-100 text-light d-flex align-items-center justify-content-center py-3">
        Ce site utilise des cookies afin d'améliorer votre confort de navigation sur notre site.
        Consultez notre politique de confidentialité.
        <a href="" id="confirmcookies" class="btn btn-outline-light ml-3">J'ai compris</a>
    </div>
<?php endif; ?>
</body>

</html>