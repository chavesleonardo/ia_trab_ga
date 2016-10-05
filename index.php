<?php

ob_start();
session_start();

require_once('novo.php');

$arrSelectNodos = listarNodos();

$markerAcidentes = getNodosAcidente();

if (!empty($_POST)) {

    if ($_POST['origem'] == $_POST['destino']) {
        $_SESSION['alerta'][0] = 'erro';
        $_SESSION['alerta'][1] = 'Saia do lugar!';
        header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
        exit;
    }

    if (isset($_POST['acidente'])) {
        
        $idNodoInicial = $_POST['origem'];
        $idNodoFinal = $_POST['destino'];
        
        $retornoAstar = a_star($idNodoInicial, $idNodoFinal);
        $retorno = shortest_way($idNodoInicial, $idNodoFinal);

    }

    if (isset($_POST['aleatorio'])) {
        $rotaAleatoria = getRotaAleatoria();

        $_POST['origem'] = $idNodoInicial = $rotaAleatoria[0];
        $_POST['destino'] = $idNodoFinal = $rotaAleatoria[1];
        
        $retornoAstar = a_star($idNodoInicial, $idNodoFinal);
        $retorno = shortest_way($idNodoInicial, $idNodoFinal);

    }

}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Trabalho I.A.</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #map{
            height: 100%;
        }
        #legend {
            font-family: Arial, sans-serif;
            background: #ccc;
            padding: 10px;
            margin: 10px;
            border: 3px solid #000;
        }
    </style>
</head>
<body>
	<div class="container" id="AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA">
		<form action="" method="post" accept-charset="utf-8">
			<div style="width: 100%; height: 50px;">
				<p style="text-align: center;">
                    <select id="selectorigem" name="origem" required="" style="background-color: #FDF669; height: 35px; border-radius: 5px; outline: none; width: 100px;">
                        <option value="">Origem</option>
                        <?php 
                            if ($arrSelectNodos) {
                                foreach ($arrSelectNodos as $idNodo => $dadosNodo) {
                                    echo '<option '; 
                                    if (isset($_POST['origem']) && $idNodo == $_POST['origem']) {
                                        echo 'selected ';
                                    }
                                    echo 'value="'.$idNodo.'">'.$idNodo.'</option>';
                                }
                            }
                        ?>
                    </select>
                    <select id="selectdestino" name="destino" required="" style="background-color: #6E99FF; height: 35px; border-radius: 5px; outline: none;  width: 100px;">
                        <option value="">Destino</option>
                        <?php 
                            if ($arrSelectNodos) {
                                foreach ($arrSelectNodos as $idNodo => $dadosNodo) {
                                    echo '<option '; 
                                    if (isset($_POST['destino']) && $idNodo == $_POST['destino']) {
                                        echo 'selected ';
                                    }
                                    echo 'value="'.$idNodo.'">'.$idNodo.'</option>';
                                }
                            }
                        ?>
                    </select>

                   <button type="submit" name="acidente" style="height: 35px; border-radius: 5px; outline: none; background-color: #77BF3B; width: 125px;">Ver Rotas</button>
				   <button type="submit" onclick="document.getElementById('selectdestino').value = '1'; document.getElementById('selectorigem').value = '2';" name="aleatorio" style="height: 35px; border-radius: 5px; outline: none; background-color: #749D7E; width: 125px;">Rota Aleatória</button>
                   <button type="button" onclick="window.location.href = window.location.pathname" name="aleatorio" style="height: 35px; border-radius: 5px; outline: none; background-color: #EEE; width: 125px;">Limpar</button>
                </p>
			</div>
		</form>
	</div>

    <div style="width: 100%; height: 90%;">
        <div id="map"></div>
    </div>
    <div id="legend"></div>

    <script>

        function initMap() {
            
            /*
            * EXIBIÇÃO DO MAPA
            */

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: {lat: -30.0517717, lng: -51.2236135}
            });

            var infowindow = new google.maps.InfoWindow();

            /*
            * RODA SEM ACIDENTES
            */
            var linha = [
            <?php
                if( isset($_SESSION['dados_rota_acidentes']) ){
                    echo $_SESSION['dados_rota_acidentes'];
                }
            ?>            
            ];
            var rotaAcidente = new google.maps.Polyline({
                path: linha,
                geodesic: true,
                strokeColor: '#0000FF',
                strokeOpacity: 0.6,
                strokeWeight: 7
            });
            rotaAcidente.setMap(map);

            /*
            * ROTA PADRAO
            */
            var linha2 = [
            <?php
                if( isset($_SESSION['dados_rota_padrao']) ){
                    echo $_SESSION['dados_rota_padrao'];
                }
            ?>            
            ];
            var rotaPadrao = new google.maps.Polyline({
                path: linha2,
                geodesic: true,
                strokeColor: '#EF292A',
                strokeOpacity: 0.6,
                strokeWeight: 7
            });
            rotaPadrao.setMap(map);

            /*
            * RODA SEM ACIDENTES
            */
            <?php 
                if (empty($_POST)) {
            ?>
            var linhaDelimitadorArea = [
                {lat: -30.048088, lng: -51.227633},
                {lat: -30.048554, lng: -51.221660},
                {lat: -30.060349, lng: -51.223304},
                {lat: -30.059440, lng: -51.230211},
                {lat: -30.048088, lng: -51.227633}
            ];
            var delimitadorArea = new google.maps.Polyline({
                path: linhaDelimitadorArea,
                geodesic: true,
                strokeColor: '#71B638',
                strokeOpacity: 0.7,
                strokeWeight: 7
            });
            delimitadorArea.setMap(map);
            <?php         
                }
            ?>

            /*
            * LEGENDA
            */
            var icons = {
              a: {
                name: '<p style="text-align: center; font-size: 12px; margin: 0; padding: 5px; background-color: #0000FF; color: #FFF;"><b>Rota Sem Acidentes</b></p>',
              },
              b: {
                name: '<p style="text-align: center;  font-size: 12px; margin: 3px 0 0 0; padding: 5px; background-color: #EF292A; color: #FFF;"><b>Rota Padrão</b></p>',
              },
              c: {
                name: '<p style="text-align: center;  font-size: 12px; margin: 3px 0 0 0; padding: 5px; background-color: #71B638; color: #FFF;"><b>Área Delimitada</b></p>',
              }
            };

            var legend = document.getElementById('legend');
            for (var key in icons) {
              var type = icons[key];
              var name = type.name;
              var div = document.createElement('div');
              div.innerHTML = name;
              legend.appendChild(div);
            }

            map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);

            /*
            * Marcadores de Acidente
            */
            var locations = [
                 <?php echo $markerAcidentes;?>
            ];

            var marker, i;

            for (i = 0; i < locations.length; i++) {  
                marker = new google.maps.Marker({
                     icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                     position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                     map: map
                });

                google.maps.event.addListener(marker, 'click', (function(marker, i) {
                     return function() {
                         infowindow.setContent(locations[i][0]);
                         infowindow.open(map, marker);
                     }
                })(marker, i));
            }

            /*
            * MARCADORES ORIGEM E FIM
            */
            markerOrigem = new google.maps.Marker({
                        icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                        position: new google.maps.LatLng(<?php if(isset($_POST['coordenadasOrigem'])) { echo $_POST['coordenadasOrigem'];} ?>),
                        map: map
                    });

            markerDestino = new google.maps.Marker({
                        icon: 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
                        position: new google.maps.LatLng(<?php if(isset($_POST['coordenadasDestino'])) { echo $_POST['coordenadasDestino'];} ?>),
                        map: map
                    });

            google.maps.event.addListener(markerOrigem, 'click', (function(marker) {
                return function() {
                    infowindow.setContent('<b>Origem:</b> '+'<?php echo $_POST['origem']; ?>');
                    infowindow.open(map, markerOrigem);
                }
            })(markerOrigem));

            google.maps.event.addListener(markerDestino, 'click', (function(marker) {
                return function() {
                    infowindow.setContent('<b>Destino:</b> '+'<?php echo $_POST['destino']; ?>');
                    infowindow.open(map, markerDestino);
                }
            })(markerDestino));

        }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA&signed_in=true&callback=initMap" async defer></script>
    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script>
    <?php 
        if (isset($_SESSION['alerta']) && $_SESSION['alerta'][0] == 'erro') {
            echo "alert('".$_SESSION['alerta'][1]."')";
            unset($_SESSION['alerta']);
        }
    ?>
        
    </script>

</body>
</html>

<?php 
    unset($_SESSION['dados_rota_padrao']);
    unset($_SESSION['dados_rota_acidentes']);

    if ($retornoAstar){
        echoArray($retornoAstar);
    }

?>