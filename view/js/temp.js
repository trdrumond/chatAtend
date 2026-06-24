
        var intervalo;
        var s = 0;
        var m = 0;
        var h = 0;

        function tempo(op) {

            var s = 0;
            var m = 0;
            var h = 0;
            intervalo = window.setInterval(function() {
                if (s == 60) { m++; s = 0; }
                if (m == 60) { h++; s = 0; m = 0; }
                if (h < 10) document.getElementById("hora").innerHTML = "0" + h + ":"; else document.getElementById("hora").innerHTML = h + ":";
                if (s < 10) document.getElementById("segundo").innerHTML = "0" + s + ""; else document.getElementById("segundo").innerHTML = s + "";
                if (m < 10) document.getElementById("minuto").innerHTML = "0" + m + ":"; else document.getElementById("minuto").innerHTML = m + ":";
                s++;
            },1000);
        }

        window.onload=tempo;