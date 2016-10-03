<?php

ob_start();
session_start();

require_once('funcoes.php');

//require_once('teste.php');

function listarNodos(){

    $conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
    mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error());

    $sql = "SELECT * FROM nodo ";

    $result = mysql_query($sql, $conecta);
    if (!$result) { return false; }

    while ($row = mysql_fetch_assoc($result)) {
        foreach ($row as $campo => $valor) {
            $arrayRetorno[$row['id']][$campo] = $valor;
        }
    }

    return (count($arrayRetorno > 0)) ? $arrayRetorno : false;
}

$arrSelectNodos = listarNodos();

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #map1 {
            height: 40%;
            margin-bottom: 10px;
        }
        #map2{
            height: 40%;
        }

    </style>
</head>
<body>
	<div class="container">
		<form action="" method="post" accept-charset="utf-8">
			<div class="col-lg-12">
				<div clas="row">
					<p>
						Origem:
                        <select name="origem" required="">
                            <option value="">Selectione</option>
                            <?php 
                                if ($arrSelectNodos) {
                                    foreach ($arrSelectNodos as $idNodo => $dadosNodo) {
                                        echo '<option value="'.$dadosNodo['latitude'].','.$dadosNodo['longitude'].'">'.$idNodo.'</option>';
                                    }
                                }
                            ?>
                        </select>
						Destino: 
                        <select name="destino" required="">
                            <option value="">Selectione</option>
                            <?php 
                                if ($arrSelectNodos) {
                                    foreach ($arrSelectNodos as $idNodo => $dadosNodo) {
                                        echo '<option value="'.$dadosNodo['latitude'].','.$dadosNodo['longitude'].'">'.$idNodo.'</option>';
                                    }
                                }
                            ?>
                        </select>
					<button class="btn btn-warning" type="submit">Calcular</button>
                    </p>
                </div>
			</div>
		</form>
	</div>
    
    <div id="map1"></div>
    <div id="map2"></div>

    <script>

        function initMap1() {

            var directionsService = new google.maps.DirectionsService;
            var directionsDisplay = new google.maps.DirectionsRenderer;
            var map1 = new google.maps.Map(document.getElementById('map1'), {
                zoom: 14,
                center: {lat: -30.035039, lng: -51.220972}
            });

            directionsDisplay.setMap(map1);

        <?php
            if ( isset($_POST['destino']) && isset($_POST['origem']) ) {
        ?>            
                calculateAndDisplayRoute(directionsService, directionsDisplay);
        <?php
            }
        ?>

        }

        function calculateAndDisplayRoute(directionsService, directionsDisplay) {
            directionsService.route({
            origin: '<?php echo $_POST['origem']; ?>',
            destination: '<?php echo $_POST['destino']; ?>',
            travelMode: google.maps.TravelMode.DRIVING
        }, function(response, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
            } else {
                window.alert('Directions request failed due to ' + status);
            }
        });
        }

        function initMap2() {

            var infowindow = new google.maps.InfoWindow();

            var map2 = new google.maps.Map(document.getElementById('map2'), {
                zoom: 15,
                center: {lat: -30.0517717, lng: -51.2236135}
            });

            marker = new google.maps.Marker({
                        position: new google.maps.LatLng(<?php if(isset($_POST['origem'])) { echo $_POST['origem'];} ?>),
                        map: map2
                    });

            marker2 = new google.maps.Marker({
                        position: new google.maps.LatLng(<?php if(isset($_POST['destino'])) { echo $_POST['destino'];} ?>),
                        map: map2
                    });

            google.maps.event.addListener(marker, 'click', (function(marker) {
                return function() {
                    infowindow.setContent('<b>Origem:</b> '+'<?php echo $_POST['origem']; ?>');
                    infowindow.open(map2, marker);
                }
            })(marker));

            google.maps.event.addListener(marker2, 'click', (function(marker) {
                return function() {
                    infowindow.setContent('<b>Destino:</b> '+'<?php echo $_POST['destino']; ?>');
                    infowindow.open(map2, marker2);
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
                flightPath.setMap(map2);
            }

            function xablau(){
                initMap1();
                initMap2();
            }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA&signed_in=true&callback=xablau" async defer></script>
    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>

</body>
</html>