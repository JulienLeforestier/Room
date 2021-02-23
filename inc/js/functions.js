document.addEventListener('DOMContentLoaded', function () {
    // contrôle d'existence
    if (document.getElementById('photo')) {
        $('#photo').on('change', function (e) {
            // équivalent JS = 
            // document.getElementById('photo').addEventListener('change', function (e) {
            let fichier = e.target.files;
            //console.log(fichier);
            let reader = new FileReader();
            reader.readAsDataURL(fichier[0]);
            reader.onload = function (event) {
                // document.getElementById('preview').innerHTML = '<img src="' + event.target.result + '" alt="' + fichier[0].name + '" class="img-fluid vignette" id="placeholder">';
                // $('#placeholder').on('drop', updatePhoto);
                // OU on ne touche pas au HTML et du coup nous n'avons pas besoin de rappeler la fonction updatePhoto
                document.getElementById('placeholder').setAttribute('src', event.target.result);
                document.getElementById('placeholder').setAttribute('alt', fichier[0].name);
            }
        });
    }

    let confirmations = document.querySelectorAll('.confirm');
    for (let i = 0; i < confirmations.length; i++) {
        confirmations[i].onclick = function () {
            return (confirm('Êtes-vous sûr(e) de vouloir supprimer ce produit ?'))
        }
    }

    if (document.getElementById('modalConfirm')) {
        $('#modalConfirm').modal('show');
    }

    let lignes = document.querySelectorAll('#tabcommandes tr[data-idcmd]');
    const URL = "http://localhost/workspacevsc/room/";
    for (let i = 0; i < lignes.length; i++) {
        //console.log(lignes[i].dataset);
        lignes[i].style.cursor = 'pointer';
        lignes[i].addEventListener('click', function () {
            //redirection JS
            window.location.href = URL + 'admin/gestion_commandes.php?action=details&id_commande=' + this.dataset.idcmd;
        });
    }

    let selectetats = document.querySelectorAll('#tabcommandes tr[data-idcmd] td select');

    for (let i = 0; i < selectetats.length; i++) {
        selectetats[i].addEventListener('click', function (e) {
            // ne propage pas l'évènement click sur le parent <tr> de cette cellule
            e.stopPropagation();
        })
    }

    if ($('#bandeaucookies').length > 0) {
        $('#bandeaucookies').animate({
            bottom: 0
        }, 1000);
    }

    if ($('#confirmcookies').length > 0) {
        $('#confirmcookies').on('click', function (e) {
            e.preventDefault();
            let expiration = new Date(new Date().getTime() + 30 * 24 * 60 * 60 * 1000); // temps en millisecondes = 30jours
            document.cookie = "acceptcookies=true; expires=" + expiration.toGMTString() + "; path=/";

            $('#bandeaucookies').animate({
                bottom: '-70px'
            }, 1000);
        });
    }

    if ($('#placeholder').length > 0) {
        $('html')
            .on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $('#placeholder').css('border', '5px dashed orange');
            })
            .on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
            })
            .on('dragleave', function (e) {
                if (e.originalEvent.pageX == 0 || e.originalEvent.pageY == 0) {
                    $('#placeholder').css('border', '');
                }
            });
        $('#placeholder').on('drop', updatePhoto);
    }

    function updatePhoto(e) {
        $('#placeholder').css('border', '');
        // fichier va récupérer un tableau correspondant au fichier déposé
        let fichier = e.originalEvent.dataTransfer.files;
        // défini la propriété files de mon input dont l'id est photo (index dans le DOM = 0)
        $('#photo')[0].files = fichier;
        // déclenche manuellement l'évènement change
        $('#photo').trigger('change');
        // équivalent JS =
        // let evenement = new Event('change');
        // document.getElementById('photo').dispatchEvent(evenement); // déclenche l'addEventListener('change') déclaré plus haut
    }
});