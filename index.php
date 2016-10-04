<?php

#bom exemplo do 63 ao 20

ob_start();
session_start();

require_once('novo.php');

$arrSelectNodos = listarNodos();

if (!empty($_POST)) {

    $idNodoInicial = $_POST['origem'];
    $idNodoFinal = $_POST['destino'];

    if (isset($_POST['acidente'])) {
        $retorno = a_star($idNodoInicial, $idNodoFinal);
        echoArray($retorno, true);
    }

    if (isset($_POST['curta'])) {
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

    </style>
</head>
<body>
	<div class="container" id="AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA">
		<form action="" method="post" accept-charset="utf-8">
			<div style="width: 100%; height: 50px;">
				<p style="text-align: center;">
                    <select name="origem" required="" style="height: 35px; border-radius: 5px; outline: none; width: 100px;">
                        <option value="">Origem</option>
                        <?php 
                            if ($arrSelectNodos) {
                                foreach ($arrSelectNodos as $idNodo => $dadosNodo) {
                                    echo '<option value="'.$idNodo.'">'.$idNodo.'</option>';
                                }
                            }
                        ?>
                    </select>
                    <select name="destino" required="" style="height: 35px; border-radius: 5px; outline: none;  width: 100px;">
                        <option value="">Destino</option>
                        <?php 
                            if ($arrSelectNodos) {
                                foreach ($arrSelectNodos as $idNodo => $dadosNodo) {
                                    echo '<option value="'.$idNodo.'">'.$idNodo.'</option>';
                                }
                            }
                        ?>
                    </select>

				   <button class="btn btn-warning" type="submit" name="curta" style="height: 35px; border-radius: 5px; outline: none; background-color: #749BFF; width: 150px;">Rota Mais Curta</button>
                   <button class="btn btn-warning" type="submit" name="acidente" style="height: 35px; border-radius: 5px; outline: none; background-color: #77BF3B; width: 150px;">Rota Sem Acidentes</button>

                </p>
			</div>
		</form>
	</div>

    <div style="width: 100%; height: 90%;">
        <div id="map"></div>
    </div>

    <script>

        function initMap() {

            var infowindow = new google.maps.InfoWindow();

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: {lat: -30.0517717, lng: -51.2236135}
            });

            marker = new google.maps.Marker({
                        position: new google.maps.LatLng(<?php if(isset($_POST['coordenadasOrigem'])) { echo $_POST['coordenadasOrigem'];} ?>),
                        map: map
                    });

            marker2 = new google.maps.Marker({
                        position: new google.maps.LatLng(<?php if(isset($_POST['coordenadasDestino'])) { echo $_POST['coordenadasDestino'];} ?>),
                        map: map
                    });

            google.maps.event.addListener(marker, 'click', (function(marker) {
                return function() {
                    infowindow.setContent('<b>Origem:</b> '+'<?php echo $_POST['origem']; ?>');
                    infowindow.open(map, marker);
                }
            })(marker));

            google.maps.event.addListener(marker2, 'click', (function(marker) {
                return function() {
                    infowindow.setContent('<b>Destino:</b> '+'<?php echo $_POST['destino']; ?>');
                    infowindow.open(map, marker2);
                }
            })(marker));

            var linha = [
            <?php
                if( isset($_SESSION['dados_mapa_2']) ){
                    echo $_SESSION['dados_mapa_2'];
                }
            ?>            
            ];
            var flightPath = new google.maps.Polyline({
                path: linha,
                geodesic: true,
                strokeColor: '#0000FF',
                strokeOpacity: 0.5,
                strokeWeight: 5
            });
                flightPath.setMap(map);
        }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA&signed_in=true&callback=initMap" async defer></script>
    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>

</body>
</html>

<?php unset($_SESSION['dados_mapa_2']); ?>