document.addEventListener('DOMContentLoaded', function () {

    if (document.getElementById('ajax')) {

        let links = document.querySelectorAll('.categorie');
        let selects = document.querySelectorAll('select');
        let capacite = document.getElementById('capacite');
        let prix = document.getElementById('prix');
        let date_arrivee = document.getElementById('date_arrivee');
        let date_depart = document.getElementById('date_depart');

        for (let i = 0; i < links.length; i++) {
            links[i].addEventListener('click', function (e) {
                e.preventDefault();
                link = links[i]['href'].split('?');
                callAjax(link[1]);
            });
        }

        for (let i = 0; i < selects.length; i++) {
            selects[i].addEventListener('change', function (e) {
                e.preventDefault();
                callAjax('ville=' + selects[i].value);
            });
        }

        capacite.addEventListener('change', function (e) {
            e.preventDefault();
            callAjax('capacite=' + capacite.value);
        });

        prix.addEventListener('input', function (e) {
            e.preventDefault();
            callAjax('prix=' + prix.value);
        });

        date_arrivee.addEventListener('blur', function (e) {
            e.preventDefault();
            function wait() {
                callAjax('date_arrivee=' + dayjs(date_arrivee.value, 'MM/DD/YYYY HH:mm').format('YYYY/MM/DD HH:mm:ss'));
            }
            setTimeout(wait, 250);
        });

        date_depart.addEventListener('blur', function (e) {
            e.preventDefault();
            function wait() {
                callAjax('date_depart=' + dayjs(date_depart.value, 'MM/DD/YYYY HH:mm').format('YYYY/MM/DD HH:mm:ss'));
            }
            setTimeout(wait, 250);
        });
    }

    let params = ['inc/ajax.php'];

    function callAjax(param) {

        for (let i = params.length - 1; i >= 0; i--) {

            if (params == 'inc/ajax.php') {
                params.push('?' + param);
                break;
            } else if (params[i].includes(param.split('=')[0])) {
                if (params[i].includes('?')) params.splice(i, 1, '?' + param);
                else if (params[i].includes('&')) params.splice(i, 1, '&' + param);
                break;
            } else if (params[i].includes('?')) {
                params.push('&' + param);
                break;
            }
        }

        let url = '';
        for (let i = 0; i < params.length; i++) url += params[i];
        ajax(url);
    }

    function ajax(filtre) {

        if (window.XMLHttpRequest) var xhr = new XMLHttpRequest();
        else var xhr = new ActiveXObject("Microsoft.XMLHTTP");

        xhr.open("GET", filtre, true);
        xhr.setRequestHeader('Content-Type', "application/x-www-form-urlencoded");
        xhr.send();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) document.getElementById('resultat').innerHTML = xhr.responseText;
        }
    }
});