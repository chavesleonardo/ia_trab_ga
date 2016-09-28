<?php

$listaNodosVisitados = '';
$separadorListaNodosVisitados = '';

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
	}
	
	if (count($arrayChild > 0)) {
		foreach ($arrayChild as $key => $value) {
			if (in_array($value, $arrayNodosVisitados)) {
				unset($arrayChild[$key]);
			}
		}
	}

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
	//$menorAcidente = 2147483648;
	$jaPegouSegundoMenor = false;
	$idNodoPai = getIdNodo($origemLatitude, $origemLongitude);
	//$arrayNodosVisitados[$idNodoPai] = $idNodoPai;
	$arrayRetornoPai = getChild($origemLatitude, $origemLongitude, $idNodoPai, $arrayNodosVisitados);	

	array_push($_SESSION['listaCoordenadas'], getCoordenadas($idNodoPai));

	if (is_array($arrayRetornoPai)) {

		$listaNodosFilhos = '';
		$separadorListaNodosFilhos = '';
		
		echo "<br/><b>Nodo Pai:</b> $idNodoPai";

		foreach ($arrayRetornoPai as $idNodoFilho) {

			$coordenadasFilho = getCoordenadas($idNodoFilho);
			$listaNodosFilhos .= $separadorListaNodosFilhos.$idNodoFilho;
			$separadorListaNodosFilhos = ',';

			//buscar os acidentes próximos ao filho em um raio de 50 metros
			$distancia = getDistance($coordenadasFilho['latitude'], $coordenadasFilho['longitude'], $destinoLatitude, $destinoLongitude);
			$acidentes = getQuantidadeAcidentesPorRaio($coordenadasFilho['latitude'], $coordenadasFilho['longitude'], 0.05);
			echo "<br/><b>Nodo Filho:</b> $idNodoFilho - Acidentes: ".$acidentes." - Distância: ".$distancia;
			
			//inserir na tabela idNodoFilho, distancia, acidentes
			inserirTemp($idNodoFilho, $distancia, $acidentes);
			inserirTemp2($idNodoFilho, $idNodoPai, $distancia, $acidentes);
			
			$coordenadasMenorFilho = getCoordenadas($idNodoFilho);
			$arrayNodosVisitados[$idNodoPai] = $idNodoPai;
			$listaNodosVisitados .= $separadorListaNodosVisitados.$arrayNodosVisitados[$idNodoPai];
			$separadorListaNodosVisitados = ',';
			
			echoArray($arrayNodosVisitados,false);
			
			if ($idNodoFilho != $idNodoDestino) {
				getMenorFilho($coordenadasMenorFilho['latitude'],$coordenadasMenorFilho['longitude'], $destinoLatitude, $destinoLongitude, $idNodoDestino, $arrayNodosVisitados);
			}else{
				//array_push($_SESSION['listaCoordenadas'], getCoordenadas($idNodoDestino));
				echo "<br/><h1>SUCESSSSSSSSSSSSSSSSSOOO!</h1>";
				echoArray($listaNodosVisitados,false);
				Calcula($menorAcidente = getTotalAcidentes($listaNodosVisitados));
			}
		}

//		$arrayMelhorOpcao = getMelhorNodoFilho($listaNodosFilhos);
		//$coordenadasMenorFilho = getCoordenadas($idNodoFilho);
		//$arrayNodosVisitados[$idNodoPai] = $idNodoPai;

/*
		if ($idNodoFilho != $idNodoDestino) {
			getMenorFilho($coordenadasMenorFilho['latitude'],$coordenadasMenorFilho['longitude'], $destinoLatitude, $destinoLongitude, $idNodoDestino, $arrayNodosVisitados);
		}else{
			array_push($_SESSION['listaCoordenadas'], getCoordenadas($idNodoDestino));
		}
*/	
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

function inserirTemp($idNodoFilho, $distancia, $acidentes){
	$sql = "INSERT INTO temp (id_nodo_origem, distancia, acidentes)
			   	 VALUES ($idNodoFilho, $distancia, $acidentes)";

	$result = mysql_query($sql, conectaBD());
}

function inserirTemp2($idNodoFilho, $idNodoPai, $distancia, $acidentes){
	$sql = "INSERT INTO temp2 (id_nodo_filho, id_nodo_pai, distancia, acidentes)
			   	 VALUES ($idNodoFilho, $idNodoPai, $distancia, $acidentes)";

	$result = mysql_query($sql, conectaBD());
}


function apagaTabelaTemp(){
	$sql = "DELETE FROM temp";

	mysql_query($sql, conectaBD());	
}

function apagaTabelaTemp2(){
	$sql = "DELETE FROM temp2";

	mysql_query($sql, conectaBD());	
}


function getMelhorNodoFilho($listaNodosFilhos){

	$sql = "SELECT * 
	          FROM temp
			 WHERE id_nodo_origem IN ($listaNodosFilhos)
		  ORDER BY acidentes ASC, distancia ASC
		  	 LIMIT 1";

	$result = mysql_query($sql, conectaBD());
	trataResultBD($result);

	while ($row = mysql_fetch_assoc($result)) {
		foreach ($row as $campo => $valor) {
			$arrayRetorno[$campo] = $valor;
		}
	}

	return (count($arrayRetorno > 0)) ? $arrayRetorno : false;

}

function getTotalAcidentes($arrayNodosVisitados){

	$sql = "SELECT SUM(acidentes) AS total_acidentes
	          FROM temp2
			 WHERE id_nodo_pai IN ($arrayNodosVisitados)";

	$result = mysql_query($sql, conectaBD());
	trataResultBD($result);

	$row = mysql_fetch_assoc($result);
	return $row["total_acidentes"];

}

function Calcula($menorAcidente){
	$menor = 100000;
	//echo "<br/> $menorAcidente";
	
	if ($menorAcidente < $menor) {
		$menor = $menorAcidente;
//		echo "<br/> $menor";
	}

}