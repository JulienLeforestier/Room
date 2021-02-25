document.addEventListener('DOMContentLoaded', function () {

    if (document.getElementById('ajax')) {

        let links = document.querySelectorAll('.categorie');
        let selects = document.querySelectorAll('select');
        let capacite = document.getElementById('capacite');
        let prix = document.getElementById('prix');
        let dates = document.querySelectorAll('.date');

        for (let i = 0; i < links.length; i++) {
            links[i].style.cursor = 'pointer';
            links[i].addEventListener('click', function (e) {
                e.preventDefault();
                link = links[i]['href'].split('?');
                callAjax(link[1]);
            });
        }

        for (let i = 0; i < selects.length; i++) {
            selects[i].style.cursor = 'pointer';
            selects[i].addEventListener('change', function (e) {
                e.preventDefault();
                callAjax('ville=' + selects[i].value);
            });
        }

        capacite.style.cursor = 'pointer';
        capacite.addEventListener('change', function (e) {
            e.preventDefault();
            callAjax('capacite=' + capacite.value);
        });

        prix.style.cursor = 'pointer';
        prix.addEventListener('change', function (e) {
            e.preventDefault();
            callAjax('prix=' + prix.value);
        });
    }

    let params = ['inc/ajax.php'];

    function callAjax(param) {

        for (let i = 0; i < params.length; i++) {

            if (params == 'inc/ajax.php') {
                params.push('?' + param);
                break;
            } else if (params[i].includes(param.split('=')[0])) {
                params.splice(i, 1, '?' + param);
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