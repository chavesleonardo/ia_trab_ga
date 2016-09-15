<?php

# O que falta implementar:
# 1 - Marcar nodos visitados
# 2 - Comparar nodos com acidentes
# 3 - Exibir polyline com conjuto de coordenadas finais

#36
$origem['latitude'] = '-30.054186';
$origem['longitude'] = '-51.224868';
#5
$destino['latitude'] = '-30.048554';
$destino['longitude'] = '-51.221660';

function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {
	$earth_radius = 6371;

	$dLat = deg2rad($latitude2 - $latitude1);
	$dLon = deg2rad($longitude2 - $longitude1);

	$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
	$c = 2 * asin(sqrt($a));
	$d = $earth_radius * $c;

	return $d;
}

function getChild($latitude, $longitude){

	$arrayChild = array();

	#pega o id do nodo
	$idNodo = getIdNodo($latitude, $longitude);

	//conecta ao banco
	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	
	//abre a base de dados
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
	
	//cria o comando SQL
	$sql = "SELECT *
			  FROM nodo_matriz 
			 WHERE id_nodo_1 = $idNodo
			    OR id_nodo_2 = $idNodo";
	
	//executa o comando SQL
	$result = mysql_query($sql, $conecta);

	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}

	if (mysql_num_rows($result) == 0) {
	    echo "Não foram encontradas linhas, nada para mostrar, assim eu estou saindo";
	    exit;
	}

	while ($row = mysql_fetch_assoc($result)) {
		if ($row["id_nodo_1"] != $idNodo) {
	    	$arrayChild[] = $row["id_nodo_1"];
		}
		if ($row["id_nodo_2"] != $idNodo) {
	    	$arrayChild[] = $row["id_nodo_2"];
		}
	}

	return (count($arrayChild > 0)) ? $arrayChild : false;

}

function getIdNodo($latitude, $longitude){

	//conecta ao banco
	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	
	//abre a base de dados
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
	
	//cria o comando SQL
	$sql = " SELECT id
			   FROM nodo
			  WHERE latitude LIKE '$latitude'
			    AND longitude LIKE '$longitude'";

    $result = mysql_query($sql, $conecta);
	
	//executa o comando SQL
	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}

	if (mysql_num_rows($result) == 0) {
	    echo "Não foram encontradas linhas, nada para mostrar, assim eu estou saindo";
	    exit;
	}

	if ($row = mysql_fetch_assoc($result)) {
	    return $row["id"];
	}	    
}

function getCoordenadas($idNodo){

	//conecta ao banco
	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	
	//abre a base de dados
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
	
	//cria o comando SQL
	$sql = " SELECT *
			   FROM nodo
			  WHERE id = $idNodo";

    $result = mysql_query($sql, $conecta);
	
	//executa o comando SQL
	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}

	if (mysql_num_rows($result) == 0) {
	    echo "Não foram encontradas linhas, nada para mostrar, assim eu estou saindo";
	    exit;
	}

	$arrayRetorno = array();
	if ($row = mysql_fetch_assoc($result)) {
		$arrayRetorno['latitude'] = $row["latitude"];
		$arrayRetorno['longitude'] = $row["longitude"];
	}
	
	if (count($arrayRetorno) > 0) {
		return $arrayRetorno;
	}else{
		return false;
	}
}






function getMenorFilho($origemLatitude, $origemLongitude, $destinoLatitude, $destinoLongitude, $idNodoDestino){

	$arrayRetornoPai = getChild($origemLatitude, $origemLongitude, $destinoLatitude, $destinoLongitude);

	echo "<pre>";
	print_r($arrayRetornoPai);
	echo "</pre>";

	$menorDistancia = 0;

	if (is_array($arrayRetornoPai)) {

		foreach ($arrayRetornoPai as $idNodoFilho) {
			$coordenadasFilho = getCoordenadas($idNodoFilho);

			if ($idNodoDestino == $menorNodo) {
				$fim = true;
			}else{
				$distancia = getDistance($coordenadasFilho['latitude'], $coordenadasFilho['longitude'], $destinoLatitude, $destinoLongitude);
				
				if ($distancia < $menorDistancia || $menorDistancia == 0) {
					$menorDistancia = $distancia;
					$menorNodo = $idNodoFilho;
				}

				echo "<b>ID: $idNodoFilho</b> - $distancia<br/>";
			}

		}

		echo "<br><b>MENOR DISTANCIA: $menorDistancia</b>";
		echo "<br><b>MENOR NODO: $menorNodo</b>";

		$coordenadasMenorFilho = getCoordenadas($menorNodo);

		if (!$fim) {
			getMenorFilho($coordenadasMenorFilho['latitude'],$coordenadasMenorFilho['longitude'], $destinoLatitude, $destinoLongitude, $idNodoDestino);		
		}else{
			exit('<br/>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br/><b>!!!!!!!!!! ENCONTROU !!!!!!!!!!</b>');
		}

	}

}

#Pega id nodo destino
$idNodoDestino = getIdNodo($destino['latitude'],$destino['longitude']);

#primeira chamada
getMenorFilho($origem['latitude'],$origem['longitude'], $destino['latitude'],$destino['longitude'], $idNodoDestino);
