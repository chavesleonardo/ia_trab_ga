<?php

/*
* Função criada por terceiros que calcula a distância entre duas coordenadas 
* levando em consideração a curvatura da terra
*/
function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {
	$earth_radius = 6371;

	$dLat = deg2rad($latitude2 - $latitude1);
	$dLon = deg2rad($longitude2 - $longitude1);

	$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
	$c = 2 * asin(sqrt($a));
	$d = $earth_radius * $c;

	return $d;
}

/*
*
* Função que retorna um array de ID de nodos filhos a partir de coordenadas de um determinado nodo.
* Com a possibilidade de excluir da lista IDs passados previamente com a finalidade de excluir os 
* nodos já visitados.
*
*/
function getChild($latitude, $longitude, $arrayIdExcluidos = false){

	$arrayChild = array();
	$idNodo = getIdNodo($latitude, $longitude);

	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
	
	$sql = "SELECT *
			  FROM nodo_matriz 
			 WHERE id_nodo_1 = $idNodo
			    OR id_nodo_2 = $idNodo";
	
	$result = mysql_query($sql, $conecta);

	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}

	if (mysql_num_rows($result) == 0) {
	    echo "Nenhum registro encontrado em getChild($latitude, $longitude, $arrayIdExcluidos = false) - ($sql)";
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

/*
* Função que retorna o ID de um nodo através das suas coordenadas
*/
function getIdNodo($latitude, $longitude){

	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 

	$sql = " SELECT id
			   FROM nodo
			  WHERE latitude LIKE '$latitude'
			    AND longitude LIKE '$longitude'";

    $result = mysql_query($sql, $conecta);

	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}

	if (mysql_num_rows($result) == 0) {
	    echo "Nenhum registro encontrado em getIdNodo($latitude, $longitude) - ($sql)";
	    exit;
	}else{
		$row = mysql_fetch_assoc($result);
		return $row["id"];	
	}
	    
}

/*
* Função que retorna as coordenadas de um nodo pelo seu ID
*/
function getCoordenadas($idNodo){

	$arrayRetorno = array();
	
	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
	
	$sql = " SELECT *
			   FROM nodo
			  WHERE id = $idNodo";

    $result = mysql_query($sql, $conecta);
	
	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}

	if (mysql_num_rows($result) == 0) {
	    echo "Nenhum registro encontrado em getCoordenadas($idNodo) - ($sql)";
	    exit;
	}else{
		$row = mysql_fetch_assoc($result);
		$arrayRetorno['latitude'] = $row["latitude"];
		$arrayRetorno['longitude'] = $row["longitude"];
	}
	
	return (count($arrayRetorno) > 0) ? $arrayRetorno : false;

}

/*
* Função percorre os filhos de um nodo em busca da melhor escolha.
*/
function getMenorFilho($origemLatitude, $origemLongitude, $destinoLatitude, $destinoLongitude, $idNodoDestino){

	$menorDistancia = 0;
	$arrayRetornoPai = getChild($origemLatitude, $origemLongitude, $destinoLatitude, $destinoLongitude);
	$idNodoPai = getIdNodo($origemLatitude, $origemLongitude);

	if (is_array($arrayRetornoPai)) {

		echo "<br/><b>Nodo Pai:</b> $idNodoPai";
		foreach ($arrayRetornoPai as $idNodoFilho) {
			$coordenadasFilho = getCoordenadas($idNodoFilho);

			if ($idNodoDestino == $menorNodo) {
				$fim = true;
			}else{

				$distancia = getDistance($coordenadasFilho['latitude'], $coordenadasFilho['longitude'], $destinoLatitude, $destinoLongitude);

				//buscar os acidentes próximos ao filho em um raio de 50 metros

				//comparar quantidades de acidentes é maior que de outros filhos

				if ($distancia < $menorDistancia || $menorDistancia == 0) {

					$menorDistancia = $distancia;
					$menorNodo = $idNodoFilho;
				}

				echo "<br/><b>Filho:</b> $idNodoFilho - Distancia: $distancia";
			}

		}

		echo "<br><b>MENOR NODO: $menorNodo - $menorDistancia</b><br/>";

		$coordenadasMenorFilho = getCoordenadas($menorNodo);

		if (!$fim) {
			getMenorFilho($coordenadasMenorFilho['latitude'],$coordenadasMenorFilho['longitude'], $destinoLatitude, $destinoLongitude, $idNodoDestino);		
		}else{
			echo '<br/>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br/><b>!!!!!!!!!! ENCONTROU !!!!!!!!!!</b>';
		}

	}

}

/*
* Função que imprime um array entre tags <pre> para melhor visualização do conteúdo
*/
function echoArray($array, $exit = false){
	echo "<pre>";
	print_r($array);
	echo "</pre>";
	if ($exit) {
		exit;
	}
}

/*
* Função que coleta todos os acidentes de uma região e conta quantidade em coordenadas iguais
* retornando um array com as coordenadas e quantidades
*/
function getAcidentesPorRegiaoComFiltros($minLat, $maxLat, $minLon, $maxLon, $tipoLocalidade = false){

	$arrayRetorno = array();

	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 

	$sql = "SELECT *
	  		  FROM acidentes 
	 		 WHERE (latitude > $minLat AND latitude < $maxLat)
	   		   AND (longitude > $minLon AND longitude < $maxLon) ";
	if ($tipoLocalidade) {
		$sql .= "AND tipo_localidade = '$tipoLocalidade' ";
	}

	$result = mysql_query($sql, $conecta);

	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}

	if (mysql_num_rows($result) == 0) {
	    echo "Nenhum registro encontrado em getAcidentesPorRegiaoComFiltros($minLat, $maxLat, $minLon, $maxLon, $tipoLocalidade = false) - ($sql)";
	    exit;
	}

	while ($row = mysql_fetch_assoc($result)) {
	
		if (array_key_exists($row["latitude"].",".$row["longitude"], $arrayRetorno)) {
			$arrayRetorno[$row["latitude"].",".$row["longitude"]]['total'] += 1;
		}

		$arrayRetorno[$row["latitude"].",".$row["longitude"]]['latitude'] = $row["latitude"];
		$arrayRetorno[$row["latitude"].",".$row["longitude"]]['longitude'] = $row["longitude"];
	
	}

	echoArray($arrayRetorno);

	return (count($arrayRetorno > 0)) ? $arrayRetorno : false;

}

function getAcidentesAoRedor($lat, $lon){
	$sql = "SELECT
				    ((ACOS(SIN($lat * PI() / 180) * SIN(latitude * PI() / 180) + COS($lon * PI() / 180) * COS(latitude * PI() / 180) * COS((- 51.224189 - longitude) * PI() / 180)) * 100 / PI()) * 60 * 1.1515) AS distancia,
					id,
				    latitude, 
				    longitude
			  FROM
				    acidentes
	        HAVING distancia <= 0.05
	      ORDER BY distancia ASC"
}