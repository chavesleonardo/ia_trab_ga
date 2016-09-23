<?php

ini_set('display_errors', 1);

$idNodoInicial = 36;
$idNodoFinal = 5;

$retorno = a_star($idNodoInicial, $idNodoFinal);
echoArray($retorno, true);


function a_star($idNodoInicial, $idNodoFinal){

	$chegouAoFim = false;

	$listaNodosDisponiveis = array();
	$listaNodosVisualizados = array();
	$listaCaminhoPercorrido = array();

	# adiciona o ponto de partida na lista $listaNodosVisualizados
	array_push($listaNodosDisponiveis, $idNodoInicial);

	
	array_push($listaCaminhoPercorrido, $idNodoInicial);

	$idMelhorNodo = $idNodoInicial;

	while (!$chegouAoFim) {

		# busca o nodo com menos custo em $listaNodosDisponiveis
	 	$idMelhorNodo = buscarNodoComMelhorCusto($listaNodosDisponiveis, $idNodoFinal);

	 	# remove o $idMelhorNodo de $listaNodosDisponiveis
	 	unset($listaNodosDisponiveis[$idMelhorNodo]);

	 	# adiciona $idMelhorNodo a $listaNodosVisualizados
	 	array_push($listaNodosVisualizados, $idMelhorNodo);

	 	# verifica se o melhor nodo é o destino
	 	if ($idMelhorNodo == $idNodoFinal) {
	 		array_push($listaCaminhoPercorrido, $idMelhorNodo);
	 		$chegouAoFim = true;
	 		break;
	 	}

	 	#lista os filhos de $idMelhorNodo
	 	$listaFilhosMelhorNodo = listarFilhosPorIdNodo($idMelhorNodo, $listaCaminhoPercorrido);
	 	$idMelhorEscolhaDosFilhos = buscarNodoComMelhorCusto($listaFilhosMelhorNodo, $idNodoFinal);
	 	// echo "<br>filhos do melhor nodo: $idMelhorNodo";
	 	// echo "<br>Melhor escolha: $idMelhorEscolhaDosFilhos";
	 	// echoArray($listaFilhosMelhorNodo);
	 	
	 	//if ($listaFilhosMelhorNodo) {

		 	foreach ($listaFilhosMelhorNodo as $idFilhoMelhorNodo) {

		 		$temFilhos = listarFilhosPorIdNodo($idFilhoMelhorNodo, $listaCaminhoPercorrido);
		 
		 		# *SE* $idFilhoMelhorNodo está em $listaNodosVisualizados
		 		# ou $idFilhoMelhorNodo nao possui filhos *ENTAO* pula para o próximo filho
		 		if (!$temFilhos || in_array($idFilhoMelhorNodo, $listaNodosVisualizados)) {
		 			# pula pro proximo...

		 		}else{

		 			# *SE* $idFilhoMelhorNodo tem o menor custo *E* idFilhoMelhorNodo
		 			# não está em $listaNodosDisponiveis
		 			if ( $idFilhoMelhorNodo == $idMelhorEscolhaDosFilhos) {

		 				# set f_cost of neighbour ????
		 				# ????????????????????????????

		 				#  set parent of neighbour to current
		 				$idkey = array_search($idMelhorNodo, $listaNodosDisponiveis);
		 				unset($listaNodosDisponiveis[$idkey]);

		 				$idMelhorNodo = $idFilhoMelhorNodo;
		 				// echo "<br>Melhor Nodo virou: ".$idMelhorNodo;
						
		 				# *SE* $idFilhoMelhorNodo não está em $listaNodosDisponiveis 
		 				# *ENTAO* adiciona em $listaNodosDisponiveis
		 				if ( !in_array($idFilhoMelhorNodo, $listaNodosDisponiveis) ) {
		                	array_push($listaNodosDisponiveis, $idFilhoMelhorNodo);
		 				}

		                # salva o melhor nodo na lista de caminhos percorridos
		                array_push($listaCaminhoPercorrido, $idFilhoMelhorNodo);

		 			} //end last if
		 			
		 		}
		 		/*
		 		echo "<br>Disponiveis:";
		 		echoArray($listaNodosDisponiveis);
		 		echo "Visualizados:";
		 		echoArray($listaNodosVisualizados);
		 		echo "Pecorrido:";
		 		echoArray($listaCaminhoPercorrido);
				*/
		 	} //end foreach

	 	//}//if pre foreach

	}//end while

	$arrayRetorno['listaNodosDisponiveis'] = $listaNodosDisponiveis;
	$arrayRetorno['listaNodosVisualizados'] = $listaNodosVisualizados;
	$arrayRetorno['listaCaminhoPercorrido'] = $listaCaminhoPercorrido;

	return $arrayRetorno;
	//echoArray($arrayRetorno, true);

}//end function










/*
* Retorna um ID de nodo considerado com menor custo dentre uma 
* lista de nodos que foi passada
*/
function buscarNodoComMelhorCusto($listaNodosDisponiveis, $idNodoDestino){

	apagaTabelaTemp();

	$arrayInfo = array();
	$listaIdNodoConsulta = '';
	$separadorListaIdNodoConsulta = '';

	if (!is_array($listaNodosDisponiveis) || count($listaNodosDisponiveis) <= 0) {
		return false;
	}

	foreach ($listaNodosDisponiveis as $idNodoDisponivel) {

		# calcula distancia reta ao nodo destino
		$arrayCoordenadasNodoDisponivel = getCoordenadasPorIdNodo($idNodoDisponivel);
		$arrayCoordenadasNodoDestino = getCoordenadasPorIdNodo($idNodoDestino);


		$distancia = getDistance($arrayCoordenadasNodoDisponivel['latitude'], 
											   					 $arrayCoordenadasNodoDisponivel['longitude'], 
											   					 $arrayCoordenadasNodoDestino['latitude'],
											   					 $arrayCoordenadasNodoDestino['longitude']);

		#calcula numero de acidentes em raio de 50 metros
		$acidentes = getQuantidadeAcidentesPorRaio($arrayCoordenadasNodoDisponivel['latitude'], $arrayCoordenadasNodoDisponivel['longitude'], 0.05);

		#salva informação em uma tabela temporária
		inserirTemp($idNodoDisponivel, $idNodoDestino, $distancia, $acidentes);

		#cria lista de consulta
		$listaIdNodoConsulta .= $separadorListaIdNodoConsulta.$idNodoDisponivel;
		$separadorListaIdNodoConsulta = ',';

	} //end foreach

	$idNodoRetorno = getMelhorNodo($listaIdNodoConsulta, $idNodoDestino);
	apagaTabelaTemp();

	return $idNodoRetorno;

}

/*
* Retorna um array com nodos filhos de um determinado nodo
*/
function listarFilhosPorIdNodo($idNodo, $listaCaminhoPercorrido){

	$lista = '';
	$separa = '';
	if (count($listaCaminhoPercorrido) > 0) {
		foreach ($listaCaminhoPercorrido as $idk) {
			$lista .= $separa.$idk;
			$separa = ',';
		}
	}

	$sql = "SELECT *
			  FROM nodo_matriz 
			 WHERE ( id_nodo_1 = $idNodo
			    OR id_nodo_2 = $idNodo ) ";

	if($lista){ $sql .= " AND (id_nodo_1 NOT IN ($lista) OR id_nodo_2 NOT IN ($lista) ) "; }

	$result = mysql_query($sql, conectaBD());
	if (!$result) { return false; }

	$arrayRetorno = array();

	while ($row = mysql_fetch_assoc($result)) {
		if ($row["id_nodo_1"] != $idNodo) {
	    	array_push($arrayRetorno, $row["id_nodo_1"]);
		}
		if ($row["id_nodo_2"] != $idNodo) {
			array_push($arrayRetorno, $row["id_nodo_2"]);
		}
	}

	return (count($arrayRetorno > 0)) ? $arrayRetorno : false;

}















/*
* Salva informações em uma tabela temporária
*/
function inserirTemp($idNodoOrigem, $idNodoDestino, $distancia, $acidentes){

	$sql = "INSERT INTO temp (id_nodo_origem, id_nodo_destino, distancia, acidentes)
			   	 VALUES ($idNodoOrigem, $idNodoDestino, $distancia, $acidentes)";

	mysql_query($sql, conectaBD());
}

/*
* Zera a tabela temp
*/
function apagaTabelaTemp(){
	$sql = "DELETE FROM temp";

	mysql_query($sql, conectaBD());	
}

/*
* Pega o melhor resultado da tabela temporária
*/
function getMelhorNodo($listaNodosOrigem, $idNodoDestino){

	$sql = "SELECT *
	          FROM temp
			 WHERE id_nodo_origem IN ($listaNodosOrigem)
			   AND id_nodo_destino = $idNodoDestino
		  ORDER BY acidentes ASC, distancia ASC
		  	 LIMIT 1";

	$result = mysql_query($sql, conectaBD());
	if (!$result) { return false; }

	$row = mysql_fetch_assoc($result);
	return $row["id_nodo_origem"];	

}

/*
* Função que retorna o ID de um nodo através das suas coordenadas
*/
function getIdNodoPorCoordenadas($latitude, $longitude){

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
function getCoordenadasPorIdNodo($idNodo){

	$sql = " SELECT *
			   FROM nodo
			  WHERE id = $idNodo";

    $result = mysql_query($sql, conectaBD());
	if (!$result) { return false; }

	$row = mysql_fetch_assoc($result);
	
	$arrayRetorno = array();
	$arrayRetorno['latitude'] = $row["latitude"];
	$arrayRetorno['longitude'] = $row["longitude"];
	
	return (count($arrayRetorno) > 0) ? $arrayRetorno : false;

}

/*
* Pega o total de acidentes em radio de uma determinada coordenada
*/
function getQuantidadeAcidentesPorRaio($lat, $lon, $raio){

	//echo "LAT: $lat, LON: $lon, RAIO: $raio, ";
	//echo "ID: ".getIdNodoPorCoordenadas($lat, $lon)."<br>";

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
	    echo "Não foi possível executar a consulta no banco de dados: " . mysql_error();
	    exit;
	}
}