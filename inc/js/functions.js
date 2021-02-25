document.addEventListener('DOMContentLoaded', function () {

    if ($('#photo')) {
        $('#photo').on('change', function (e) {
            let fichier = e.target.files;
            let reader = new FileReader();
            reader.readAsDataURL(fichier[0]);
            reader.onload = function (event) {
                $('#placeholder').setAttribute('src', event.target.result);
                $('#placeholder').setAttribute('alt', fichier[0].name);
            }
        });
    }

    function updatePhoto(e) {
        $('#placeholder').css('border', '');
        // fichier va récupérer un tableau correspondant au fichier déposé
        let fichier = e.originalEvent.dataTransfer.files;
        // défini la propriété files de mon input dont l'id est photo (index dans le DOM = 0)
        $('#photo')[0].files = fichier;
        // déclenche manuellement l'évènement change
        $('#photo').trigger('change');
    }

    let confirmations = document.querySelectorAll('.confirm');
    for (let i = 0; i < confirmations.length; i++) {
        confirmations[i].onclick = function () {
            return (confirm('Êtes-vous sûr(e) de vouloir appliquer cette suppression ?'))
        }
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

    if ($('#tabs')) {
        $(function () {
            $('#tabs').tabs({
                collapsible: true,
                event: "click"
            });
        });
    }

    if ($('#prix')) {
        $('.range').next().text('10000€');
        $('.range').on('input', function () {
            var $set = $(this).val();
            $(this).next().text($set + '€');
        });
    }
});