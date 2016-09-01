<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #map {
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
						<input type="text" name="origem" value="<?php echo $_POST['origem']; ?>">
					</p>
					
				</div>
				<div clas="row">
					<p>
						Destino: 
						<input type="text" name="destino" value="<?php echo $_POST['destino']; ?>">
					</p>
				</div>
				<div class="row">
					<button class="btn btn-warning" type="submit">Calcular</button>
				</div>
			</div>
		</form>
	</div>
    
    <div id="map"></div>

    <script>

        function initMap() {

            var directionsService = new google.maps.DirectionsService;
            var directionsDisplay = new google.maps.DirectionsRenderer;
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {lat: -30.035039, lng: -51.220972}
            });

            directionsDisplay.setMap(map);

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

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCl8qjiJLd7KBxiKB0-2lRej5o96NYigIA&signed_in=true&callback=initMap" async defer></script>
    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>

</body>

<script src="bootstrap/js/bootstrap.min.js"></script>

</html>