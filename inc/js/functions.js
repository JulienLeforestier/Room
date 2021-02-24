document.addEventListener('DOMContentLoaded', function () {

    if (document.getElementById('photo')) {
        $('#photo').on('change', function (e) {
            let fichier = e.target.files;
            let reader = new FileReader();
            reader.readAsDataURL(fichier[0]);
            reader.onload = function (event) {
                document.getElementById('placeholder').setAttribute('src', event.target.result);
                document.getElementById('placeholder').setAttribute('alt', fichier[0].name);
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

    if (document.getElementById('tabs')) {
        $(function () {
            $('#tabs').tabs({
                collapsible: true,
                event: "click"
            });
        });
    }

    if (document.getElementById('date_arrivee')) {
        // datetimepicker from to
        var startDateTextBox = $('#date_arrivee');
        var endDateTextBox = $('#date_depart');
        $('#date_depart').prop('disabled', true);

        startDateTextBox.datetimepicker({
            minDate: 0,
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 2,
            closeText: 'Fermer',
            prevText: 'Précédent',
            nextText: 'Suivant',
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            weekHeader: 'Sem.',
            firstDay: 1,
            onClose: function (dateText, inst) {
                if (endDateTextBox.val() != '') {
                    var testStartDate = startDateTextBox.datetimepicker('getDate');
                    var testEndDate = endDateTextBox.datetimepicker('getDate');
                    if (testStartDate > testEndDate)
                        endDateTextBox.datetimepicker('setDate', testStartDate);
                }
                else {
                    endDateTextBox.val(dateText);
                }
            },
            onSelect: function (selectedDateTime) {
                endDateTextBox.datetimepicker('option', 'minDate', startDateTextBox.datetimepicker('getDate'));
                $('#date_depart').prop('disabled', false);
            }
        });
        endDateTextBox.datetimepicker({
            minDate: 0,
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 2,
            closeText: 'Fermer',
            prevText: 'Précédent',
            nextText: 'Suivant',
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            weekHeader: 'Sem.',
            firstDay: 1,
            onClose: function (dateText, inst) {
                if (startDateTextBox.val() != '') {
                    var testStartDate = startDateTextBox.datetimepicker('getDate');
                    var testEndDate = endDateTextBox.datetimepicker('getDate');
                    if (testStartDate > testEndDate)
                        startDateTextBox.datetimepicker('setDate', testEndDate);
                }
                else {
                    startDateTextBox.val(dateText);
                }
            },
            onSelect: function (selectedDateTime) {
                startDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate'));
            }
        });
    }
});