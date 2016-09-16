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
function getChild($latitude, $longitude, $idNodoPai, $arrayNodosVisitados){

	$sql = "SELECT *
			  FROM nodo_matriz 
			 WHERE id_nodo_1 = $idNodoPai
			    OR id_nodo_2 = $idNodoPai";

	$result = mysql_query($sql, conectaBD());
	trataResultBD($result);

	while ($row = mysql_fetch_assoc($result)) {
		if ($row["id_nodo_1"] != $idNodoPai) {
	    	$arrayChild[] = $row["id_nodo_1"];
		}
		if ($row["id_nodo_2"] != $idNodoPai) {
	    	$arrayChild[] = $row["id_nodo_2"];
		}
		if(in_array($row["id_nodo_1"], $arrayNodosVisitados)){
			echo "<br> Esta no array: ".$row["id_nodo_1"];
		}
		if(in_array($row["id_nodo_2"], $arrayNodosVisitados)){
			echo "<br> Esta no array: ".$row["id_nodo_2"];
		}
	}
	echoARray($arrayChild);
	return (count($arrayChild > 0)) ? $arrayChild : false;

}

/*
* Função que retorna o ID de um nodo através das suas coordenadas
*/
function getIdNodo($latitude, $longitude){

	$sql = " SELECT id
			   FROM nodo
			  WHERE latitude LIKE '$latitude'
			    AND longitude LIKE '$longitude'";

    $result = mysql_query($sql, conectaBD());
	trataResultBD($result);

	$row = mysql_fetch_assoc($result);
	return $row["id"];	
	    
}

/*
* Função que retorna as coordenadas de um nodo pelo seu ID
*/
function getCoordenadas($idNodo){

	$arrayRetorno = array();
	
	$sql = " SELECT *
			   FROM nodo
			  WHERE id = $idNodo";

    $result = mysql_query($sql, conectaBD());
	trataResultBD($result);

	$row = mysql_fetch_assoc($result);
	$arrayRetorno['latitude'] = $row["latitude"];
	$arrayRetorno['longitude'] = $row["longitude"];
	
	return (count($arrayRetorno) > 0) ? $arrayRetorno : false;

}

/*
* Função percorre os filhos de um nodo em busca da melhor escolha.
*/
function getMenorFilho($origemLatitude, $origemLongitude, $destinoLatitude, $destinoLongitude, $idNodoDestino, $arrayNodosVisitados){

	$menorDistancia = 0;
	$idNodoPai = getIdNodo($origemLatitude, $origemLongitude);
	$arrayNodosVisitados[$idNodoPai] = $idNodoPai;
	$arrayRetornoPai = getChild($origemLatitude, $origemLongitude, $idNodoPai, $arrayNodosVisitados);

	if (is_array($arrayRetornoPai)) {

		echo "<br/><b>Nodo Pai:</b> $idNodoPai";
		foreach ($arrayRetornoPai as $idNodoFilho) {
			$coordenadasFilho = getCoordenadas($idNodoFilho);

			if ($idNodoDestino == $menorNodo) {
				$fim = true;
			}else{

				$distancia = getDistance($coordenadasFilho['latitude'], $coordenadasFilho['longitude'], $destinoLatitude, $destinoLongitude);
				$acidentes = getQuantidadeAcidentesPorRaio($coordenadasFilho['latitude'], $coordenadasFilho['longitude'], 0.05);
				//buscar os acidentes próximos ao filho em um raio de 50 metros

				//comparar quantidades de acidentes é maior que de outros filhos

				if ($distancia < $menorDistancia || $menorDistancia == 0) {

					$menorDistancia = $distancia;
					$menorNodo = $idNodoFilho;
				}

				echo "<br/><b>Filho:</b> $idNodoFilho - Distancia: $distancia - Acidentes: $acidentes";
			}

		}

		echo "<br><b>MENOR NODO: $menorNodo - $menorDistancia</b><br/>";

		$coordenadasMenorFilho = getCoordenadas($menorNodo);
		$arrayNodosVisitados[$menorNodo] = $menorNodo;

		if (!$fim) {
			getMenorFilho($coordenadasMenorFilho['latitude'],$coordenadasMenorFilho['longitude'], $destinoLatitude, $destinoLongitude, $idNodoDestino, $arrayNodosVisitados);
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

	$sql = "SELECT *
	  		  FROM acidentes 
	 		 WHERE (latitude > $minLat AND latitude < $maxLat)
	   		   AND (longitude > $minLon AND longitude < $maxLon) ";
	if ($tipoLocalidade) {
		$sql .= "AND tipo_localidade = '$tipoLocalidade' ";
	}

	$result = mysql_query($sql, conectaBD());
	trataResultBD($result);

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

/*
* Pega o total de acidentes em radio de uma determinada coordenada
*/
function getQuantidadeAcidentesPorRaio($lat, $lon, $raio){

	$sql = "   SELECT ((ACOS(SIN($lat * PI() / 180) * SIN(latitude * PI() / 180) + 
			            COS($lat * PI() / 180) * COS(latitude * PI() / 180) * COS(($lon - longitude) * 
			            PI() / 180)) * 180 / PI()) * 99 * 1.1515) AS distance 
			    FROM acidentes 
			  HAVING distance <= $raio
			ORDER BY distance ASC";

	$result = mysql_query($sql, conectaBD());
	trataResultBD($result);

	$counter = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$counter++;
	}

	return $counter;

}

/*
* Conecta neste banco de dados
*/
function conectaBD(){
	$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
	mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error());
	return $conecta;
}

/*
* trata o retorno do banco
*/
function trataResultBD($result){
	if (!$result) {
	    echo "Não foi possível executar a consulta ($sql) no banco de dados: " . mysql_error();
	    exit;
	}
}