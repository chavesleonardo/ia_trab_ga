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
				
				if ($distancia < $menorDistancia || $menorDistancia == 0) {
					$menorDistancia = $distancia;
					$menorNodo = $idNodoFilho;
				}

				echo "<br/><b>Filho:</b> $idNodoFilho - Distancia: $distancia";
			}

		}

		echo "<br><b>MENOR NODO: $menorNodo - MENOR DISTANCIA: $menorDistancia</b><br/>";

		$coordenadasMenorFilho = getCoordenadas($menorNodo);

		if (!$fim) {
			getMenorFilho($coordenadasMenorFilho['latitude'],$coordenadasMenorFilho['longitude'], $destinoLatitude, $destinoLongitude, $idNodoDestino);		
		}else{
			exit('<br/>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br/><b>!!!!!!!!!! ENCONTROU !!!!!!!!!!</b>');
		}

	}

}

/*
* Função que imprime um array entre tags <pre> para melhor visualização do conteúdo
*/
function echoArray($array){
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

/* RECUPERAR COLISÕES NA REGIAO
SELECT *
  FROM acidentes 
 WHERE (latitude > -30.0611 AND latitude < -30.0477)
   AND (longitude > -51.2305 AND longitude < -51.2212)
-- AND tipo_localidade = 'cruzamento';
*/