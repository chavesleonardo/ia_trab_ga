<?php

function a_star($idNodoInicial, $idNodoFinal){

	$chegouAoFim = false;
	$aviso = '';
	$listaOpen = array();
	$listaClosed = array();
	$listaCaminhoPercorrido = array();
	$listaTodosNodos = listarNodos();

	# adiciona o ponto de partida na lista $listaOpen
	$listaOpen = adicionarLista($listaOpen, $idNodoInicial);

	# adiciona o ponto de partida na lista $listaCaminhoPercorrido
	$listaCaminhoPercorrido = adicionarLista($listaCaminhoPercorrido, $idNodoInicial);

	$idMelhorNodo = $idNodoInicial;

	while (!$chegouAoFim) {

		# só entrará aqui, se um nodo anterior não tinha filhos validos
		if (!$idMelhorNodo) {

			# se a lista está OPEN está vazia, não há mais o que percorrer. Encerra tudo.
	 		if ( empty($listaOpen) ) {
		 		$chegouAoFim = true;
		 		$alerta[0] = 'erro';
		 		$alerta[1] = 'Lista OPEN vazia! Não há rota possível';
		 		break;
	 		}
			
			# busca o nodo com menos custo em $listaOpen
	 		$idMelhorNodo = buscarNodoComMelhorCusto($listaOpen, $idNodoFinal);
			$listaCaminhoPercorrido = adicionarLista($listaCaminhoPercorrido, $idMelhorNodo);
		}

	 	# remove o $idMelhorNodo de $listaOpen
		$listaOpen = removerLista($listaOpen, $idMelhorNodo);

	 	# adiciona $idMelhorNodo a $listaClosed
	 	$listaClosed = adicionarLista($listaClosed, $idMelhorNodo);

	 	# verifica se o melhor nodo é o destino
	 	if ($idMelhorNodo == $idNodoFinal) {
	 		$listaCaminhoPercorrido = adicionarLista($listaCaminhoPercorrido, $idMelhorNodo);
	 		$chegouAoFim = true;
	 		$alerta[0] = 'sucesso';
	 		$alerta[1] = 'Chegou ao destino';
	 		break;
	 	}

	 	#lista os filhos de $idMelhorNodo
	 	$listaFilhosMelhorNodo = listarFilhosPorIdNodo($idMelhorNodo, $listaClosed);
	 	
	 	#calcula a melhor opção dos filhos
	 	$idMelhorEscolhaDosFilhos = buscarNodoComMelhorCusto($listaFilhosMelhorNodo, $idNodoFinal);
	 	
	 	#não achou filhos do nodo sem acidentes
 		if (!$listaFilhosMelhorNodo) {

 			#pega todos os filhos no nodo, incluindo os com acidentes, e salva como CLOSED
 			$listaFilhosMelhorNodoClosed = listarFilhosPorIdNodo($idMelhorNodo, $listaClosed, false);

			$listaOpen = removerLista($listaOpen, $idMelhorNodo);
			$listaCaminhoPercorrido = removerLista($listaCaminhoPercorrido, $idMelhorNodo);
 			
 			if ($listaFilhosMelhorNodoClosed) {
 				foreach ($listaFilhosMelhorNodoClosed as $ordem => $idNodoClosed) {
 					$listaClosed = adicionarLista($listaClosed, $idNodoClosed);
 				}
 			}

 			#como este nodo não tem filhos uteis, seta o ponteiro corrente para vazio
 			$idMelhorNodo = false;
 		}

		
	 	if ($listaFilhosMelhorNodo && $idMelhorNodo) {

		 	foreach ($listaFilhosMelhorNodo as $idFilhoMelhorNodo) {

		 		$temFilhos = listarFilhosPorIdNodo($idFilhoMelhorNodo, $listaClosed);

		 		# *SE* $idFilhoMelhorNodo está em $listaClosed
		 		# ou $idFilhoMelhorNodo nao possui filhos *ENTAO* pula para o próximo filho
		 		if (!$temFilhos || in_array($idFilhoMelhorNodo, $listaClosed)) {
		 			
		 			# salva como closed 
		 			if (!in_array($idFilhoMelhorNodo, $listaClosed)) {
		 				$listaClosed = adicionarLista($listaClosed, $idFilhoMelhorNodo);
		 			}

		 		}else{

		 			# *SE* $idFilhoMelhorNodo tem o menor custo *E* idFilhoMelhorNodo
		 			# não está em $listaOpen
		 			if ( $idFilhoMelhorNodo == $idMelhorEscolhaDosFilhos) {

		 				$listaOpen = removerLista($listaOpen, $idMelhorNodo);

		 				#  set parent of neighbour to current
		 				$idMelhorNodo = $idFilhoMelhorNodo;
						
		 				# *SE* $idFilhoMelhorNodo não está em $listaOpen 
		 				# *ENTAO* adiciona em $listaOpen
		 				$listaOpen = adicionarLista($listaOpen, $idFilhoMelhorNodo);

		                # salva o melhor nodo na lista de caminhos percorridos
		                $listaCaminhoPercorrido = adicionarLista($listaCaminhoPercorrido, $idFilhoMelhorNodo);

		 			}else{
		 				$listaOpen = adicionarLista($listaOpen, $idFilhoMelhorNodo);

		 			} //end last if
		 			
		 		}

		 	} //end foreach

	 	}//if pre foreach

	}//end while

	if ($alerta[0] == 'sucesso') {

		$arrayRetorno['listaOpen'] = $listaOpen;
		$arrayRetorno['listaClosed'] = $listaClosed;
		$arrayRetorno['listaCaminhoPercorrido'] = $listaCaminhoPercorrido;

		$arrayCaminho = reconstruct_path($listaCaminhoPercorrido);

		if (!empty($arrayCaminho)) {
			foreach ($arrayCaminho as $nodoCoord) {

				$dadosNodo = getCoordenadasPorIdNodo($nodoCoord);
				$ultimo .= "{lat: ".$dadosNodo['latitude'].", lng: ".$dadosNodo['longitude']."}, ";
			}

			if ($ultimo) {
				$_SESSION['dados_rota_acidentes'] = $ultimo;
				$coordenadasOrigem = getCoordenadasPorIdNodo($idNodoInicial);
				$coordenadasDestino = getCoordenadasPorIdNodo($idNodoFinal);
				$_POST['coordenadasOrigem'] = $coordenadasOrigem['latitude'].','.$coordenadasOrigem['longitude'];
				$_POST['coordenadasDestino'] = $coordenadasDestino['latitude'].','.$coordenadasDestino['longitude'];
			}
		}
	}else{
		$arrayRetornoErro['listaOpen'] = $listaOpen;
		$arrayRetornoErro['listaClosed'] = $listaClosed;
		$arrayRetornoErro['listaCaminhoPercorrido'] = $listaCaminhoPercorrido;		
	}

	$_SESSION['alerta'] = $alerta;

	return ($arrayRetornoErro) ? $arrayRetornoErro : false ;

}//end function a_star

function shortest_way($idNodoOrigem, $idNodoDestino){

	$coordenadasOrigem = getCoordenadasPorIdNodo($idNodoOrigem);
	$coordenadasDestino = getCoordenadasPorIdNodo($idNodoDestino);

	$_POST['coordenadasOrigem'] = $coordenadasOrigem['latitude'].','.$coordenadasOrigem['longitude'];
	$_POST['coordenadasDestino'] = $coordenadasDestino['latitude'].','.$coordenadasDestino['longitude'];

	$chegouAoFim = false;
	$arrayCaminho = array();
	$current = $idNodoOrigem;
	$count = 0;

	while (!$chegouAoFim) {

		$arrayCaminho = adicionarLista($arrayCaminho, $current);

		if ($current == $idNodoDestino) {
			break;
		}

		//echo "Caminho:";print_r($arrayCaminho);

		#lista os filhos
		$filhosCurrent = listarFilhosPorIdNodo($current, $arrayCaminho, false);

		//echoArray($filhosCurrent);

		#busca a melhor opção entre os filhos
		$current = getMelhorNodoFilho($filhosCurrent, $idNodoDestino);

		//echo "Opção: $current<br/>";

	}

	if (!empty($arrayCaminho)) {
		foreach ($arrayCaminho as $ordem => $idNodo) {

			$dadosNodo = getCoordenadasPorIdNodo($idNodo);
			$ultimo .= "{lat: ".$dadosNodo['latitude'].", lng: ".$dadosNodo['longitude']."}, ";
		}
	}

	$_SESSION['dados_rota_padrao'] = $ultimo;
	
}

function adicionarLista($lista, $item){
	if (!in_array($item, $lista)) {
		array_push($lista, $item);
	}
	return $lista;
}

function removerLista($lista, $item){
	$ordemItem = array_search($item, $lista);
	if (is_numeric($ordemItem)) {
		unset($lista[$ordemItem]);
	}
	return $lista;
}

function listarNodos(){

	$arrayRetorno = array(	1  => array( 'id' => 1 , 'latitude' => '-30.048088', 'longitude' => '-51.227633', 'acidentes' =>	12	),
							2  => array( 'id' => 2 , 'latitude' => '-30.048237', 'longitude' => '-51.225906', 'acidentes' =>	0	),
							3  => array( 'id' => 3 , 'latitude' => '-30.048372', 'longitude' => '-51.224006', 'acidentes' =>	6	),
							4  => array( 'id' => 4 , 'latitude' => '-30.048463', 'longitude' => '-51.222755', 'acidentes' =>	0	),
							5  => array( 'id' => 5 , 'latitude' => '-30.048554', 'longitude' => '-51.221660', 'acidentes' =>	4	),
							6  => array( 'id' => 6 , 'latitude' => '-30.049112', 'longitude' => '-51.227814', 'acidentes' =>	7	),
							7  => array( 'id' => 7 , 'latitude' => '-30.049196', 'longitude' => '-51.226516', 'acidentes' =>	0	),
							8  => array( 'id' => 8 , 'latitude' => '-30.049317', 'longitude' => '-51.225357', 'acidentes' =>	0	),
							9  => array( 'id' => 9 , 'latitude' => '-30.049452', 'longitude' => '-51.224193', 'acidentes' =>	15	),
							10 => array( 'id' => 10, 'latitude' => '-30.049707', 'longitude' => '-51.221817', 'acidentes' =>	0	),
							11 => array( 'id' => 11, 'latitude' => '-30.050842', 'longitude' => '-51.221991', 'acidentes' =>	8	),
							12 => array( 'id' => 12, 'latitude' => '-30.050593', 'longitude' => '-51.224404', 'acidentes' =>	0	),
							13 => array( 'id' => 13, 'latitude' => '-30.050474', 'longitude' => '-51.225502', 'acidentes' =>	0	),
							14 => array( 'id' => 14, 'latitude' => '-30.050274', 'longitude' => '-51.227984', 'acidentes' =>	7	),
							15 => array( 'id' => 15, 'latitude' => '-30.051318', 'longitude' => '-51.228180', 'acidentes' =>	0	),
							16 => array( 'id' => 16, 'latitude' => '-30.051472', 'longitude' => '-51.226676', 'acidentes' =>	0	),
							17 => array( 'id' => 17, 'latitude' => '-30.051570', 'longitude' => '-51.225700', 'acidentes' =>	0	),
							18 => array( 'id' => 18, 'latitude' => '-30.051663', 'longitude' => '-51.225571', 'acidentes' =>	0	),
							19 => array( 'id' => 19, 'latitude' => '-30.051714', 'longitude' => '-51.224568', 'acidentes' =>	0	),
							20 => array( 'id' => 20, 'latitude' => '-30.051937', 'longitude' => '-51.222165', 'acidentes' =>	0	),
							21 => array( 'id' => 21, 'latitude' => '-30.053246', 'longitude' => '-51.222342', 'acidentes' =>	8	),
							22 => array( 'id' => 22, 'latitude' => '-30.053148', 'longitude' => '-51.223265', 'acidentes' =>	0	),
							23 => array( 'id' => 23, 'latitude' => '-30.052999', 'longitude' => '-51.224729', 'acidentes' =>	4	),
							24 => array( 'id' => 24, 'latitude' => '-30.052929', 'longitude' => '-51.225770', 'acidentes' =>	0	),
							25 => array( 'id' => 25, 'latitude' => '-30.052344', 'longitude' => '-51.225673', 'acidentes' =>	0	),
							26 => array( 'id' => 26, 'latitude' => '-30.052251', 'longitude' => '-51.226800', 'acidentes' =>	0	),
							27 => array( 'id' => 27, 'latitude' => '-30.052181', 'longitude' => '-51.227578', 'acidentes' =>	0	),
							28 => array( 'id' => 28, 'latitude' => '-30.052135', 'longitude' => '-51.228318', 'acidentes' =>	0	),
							29 => array( 'id' => 29, 'latitude' => '-30.053128', 'longitude' => '-51.228499', 'acidentes' =>	6	),
							30 => array( 'id' => 30, 'latitude' => '-30.053188', 'longitude' => '-51.227742', 'acidentes' =>	0	),
							31 => array( 'id' => 31, 'latitude' => '-30.053262', 'longitude' => '-51.226961', 'acidentes' =>	0	),
							32 => array( 'id' => 32, 'latitude' => '-30.053889', 'longitude' => '-51.228621', 'acidentes' =>	0	),
							33 => array( 'id' => 33, 'latitude' => '-30.053997', 'longitude' => '-51.227295', 'acidentes' =>	7	),
							34 => array( 'id' => 34, 'latitude' => '-30.053983', 'longitude' => '-51.227064', 'acidentes' =>	6	),
							35 => array( 'id' => 35, 'latitude' => '-30.054081', 'longitude' => '-51.225948', 'acidentes' =>	0	),
							36 => array( 'id' => 36, 'latitude' => '-30.054186', 'longitude' => '-51.224868', 'acidentes' =>	0	),
							37 => array( 'id' => 37, 'latitude' => '-30.054353', 'longitude' => '-51.223363', 'acidentes' =>	0	),
							38 => array( 'id' => 38, 'latitude' => '-30.054432', 'longitude' => '-51.222483', 'acidentes' =>	4	),
							39 => array( 'id' => 39, 'latitude' => '-30.055411', 'longitude' => '-51.222604', 'acidentes' =>	0	),
							40 => array( 'id' => 40, 'latitude' => '-30.055224', 'longitude' => '-51.225043', 'acidentes' =>	0	),
							41 => array( 'id' => 41, 'latitude' => '-30.055021', 'longitude' => '-51.227496', 'acidentes' =>	0	),
							42 => array( 'id' => 42, 'latitude' => '-30.054910', 'longitude' => '-51.228826', 'acidentes' =>	7	),
							43 => array( 'id' => 43, 'latitude' => '-30.055755', 'longitude' => '-51.227619', 'acidentes' =>	0	),
							44 => array( 'id' => 44, 'latitude' => '-30.056006', 'longitude' => '-51.225141', 'acidentes' =>	0	),
							45 => array( 'id' => 45, 'latitude' => '-30.056507', 'longitude' => '-51.227737', 'acidentes' =>	4	),
							46 => array( 'id' => 46, 'latitude' => '-30.056721', 'longitude' => '-51.225248', 'acidentes' =>	0	),
							47 => array( 'id' => 47, 'latitude' => '-30.057153', 'longitude' => '-51.229475', 'acidentes' =>	9	),
							48 => array( 'id' => 48, 'latitude' => '-30.057240', 'longitude' => '-51.228528', 'acidentes' =>	0	),
							49 => array( 'id' => 49, 'latitude' => '-30.057303', 'longitude' => '-51.227905', 'acidentes' =>	0	),
							50 => array( 'id' => 50, 'latitude' => '-30.057355', 'longitude' => '-51.227772', 'acidentes' =>	0	),
							51 => array( 'id' => 51, 'latitude' => '-30.057439', 'longitude' => '-51.226452', 'acidentes' =>	0	),
							52 => array( 'id' => 52, 'latitude' => '-30.057532', 'longitude' => '-51.225376', 'acidentes' =>	0	),
							53 => array( 'id' => 53, 'latitude' => '-30.057604', 'longitude' => '-51.224604', 'acidentes' =>	0	),
							54 => array( 'id' => 54, 'latitude' => '-30.057671', 'longitude' => '-51.224148', 'acidentes' =>	0	),
							55 => array( 'id' => 55, 'latitude' => '-30.057671', 'longitude' => '-51.223853', 'acidentes' =>	0	),
							56 => array( 'id' => 56, 'latitude' => '-30.057778', 'longitude' => '-51.222952', 'acidentes' =>	0	),
							57 => array( 'id' => 57, 'latitude' => '-30.059120', 'longitude' => '-51.224438', 'acidentes' =>	5	),
							58 => array( 'id' => 58, 'latitude' => '-30.058988', 'longitude' => '-51.225554', 'acidentes' =>	4	),
							59 => array( 'id' => 59, 'latitude' => '-30.058774', 'longitude' => '-51.226681', 'acidentes' =>	0	),
							60 => array( 'id' => 60, 'latitude' => '-30.058574', 'longitude' => '-51.227995', 'acidentes' =>	5	),
							61 => array( 'id' => 61, 'latitude' => '-30.058451', 'longitude' => '-51.228808', 'acidentes' =>	0	),
							62 => array( 'id' => 62, 'latitude' => '-30.058363', 'longitude' => '-51.229825', 'acidentes' =>	19	),
							63 => array( 'id' => 63, 'latitude' => '-30.059440', 'longitude' => '-51.230211', 'acidentes' =>	4	),
							64 => array( 'id' => 64, 'latitude' => '-30.059729', 'longitude' => '-51.228199', 'acidentes' =>	0	),
							65 => array( 'id' => 65, 'latitude' => '-30.059892', 'longitude' => '-51.226914', 'acidentes' =>	0	),
							66 => array( 'id' => 66, 'latitude' => '-30.060031', 'longitude' => '-51.225702', 'acidentes' =>	0	),
							67 => array( 'id' => 67, 'latitude' => '-30.060170', 'longitude' => '-51.224726', 'acidentes' =>	0	),
							68 => array( 'id' => 68, 'latitude' => '-30.060349', 'longitude' => '-51.223304', 'acidentes' =>	0	) 
	);

    return $arrayRetorno;
}

function getNodosAcidente(){
	$arrNodos = listarNodos();
	$stringRetorno = '';
	foreach ($arrNodos as $dadosNodo) {
		if ($dadosNodo['acidentes'] > 3) {
			$stringRetorno .= "['<b>".$dadosNodo['acidentes']."</b> Acidentes', ".$dadosNodo['latitude'].",".$dadosNodo['longitude']."],";
		}
	}

	return $stringRetorno;
}

/*
* Retorna um ID de nodo considerado com menor custo dentre uma 
* lista de nodos que foi passada
*/
function buscarNodoComMelhorCusto($listaNodosDisponiveis, $idNodoDestino){

	apagaTabelaTemp();

	$arrayInfo = array();
	$listaIdNodoConsulta = '';
	$separadorListaIdNodoConsulta = '';
	$arrNodos = listarNodos();

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
		$acidentes = $arrNodos[$idNodoDisponivel]['acidentes'];

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

function reconstruct_path($listaIds){

	$listaIds = array_reverse($listaIds);

	$arrRetorno = array();

	foreach ($listaIds as $idNodo) {
		
		if (empty($arrRetorno)) {
			$arrRetorno[] = $idNodo;
		}else{
			if ( temLigacao($idNodo, end($arrRetorno)) ) {
				
				#verifica se tem filhos que estao na lista

				$arrRetorno[] = $idNodo;
			}else{
				#nao tem ligação, não salva na lista
			}
		}
	}

	return (!empty($arrRetorno)) ? $arrRetorno : false ;
}

function temLigacao($idNodo1, $idNodo2){

	$sql = "SELECT 1 as possui_conexao FROM nodo_matriz WHERE 
			( id_nodo_1 = $idNodo1 OR id_nodo_2 = $idNodo1 )
		    AND
		    ( id_nodo_1 = $idNodo2 OR id_nodo_2 = $idNodo2 )";

	$result = mysql_query($sql, conectaBD());
	if (!$result) { return false; }
	$row = mysql_fetch_assoc($result);

	return $row["possui_conexao"];	
}

/*
* Retorna um array com nodos filhos de um determinado nodo
*/
function listarFilhosPorIdNodo($idNodo, $listaCaminhoPercorrido, $comLimite = true){

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

	# retira filhos que possuem mais que 4 acidentes
	if (count($arrayRetorno) > 0 && $comLimite) {
		$arrNodos = listarNodos();
		
		foreach ($arrayRetorno as $ordem => $idNd) {
			if (intval($arrNodos[$idNd]['acidentes']) > 3) {
				unset($arrayRetorno[$ordem]);
			}
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

function getRotaAleatoria(){

	$sortRotas = array();

	array_push($sortRotas, array('7' ,'15'));
	array_push($sortRotas, array('7' ,'20'));
	array_push($sortRotas, array('8' ,'43'));
	array_push($sortRotas, array('8' ,'64'));
	array_push($sortRotas, array('8' ,'68'));
	//array_push($sortRotas, array('17','40'));
	array_push($sortRotas, array('17','50'));
	array_push($sortRotas, array('53','30'));
	array_push($sortRotas, array('63','8'));
	array_push($sortRotas, array('63','20'));
	array_push($sortRotas, array('68','22'));

	return $sortRotas[array_rand($sortRotas)];

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

function getMelhorNodoFilho($listaFilhos, $idNodoDestino){

	$melhorDistancia = 9999999;
	$melhorIdFilho = 0;

	$coordenadasDestino = getCoordenadasPorIdNodo($idNodoDestino);

	foreach ($listaFilhos as $ordem => $idFilho) {

		$coordenadasFilho = getCoordenadasPorIdNodo($idFilho);


		$distancia = getDistance($coordenadasFilho['latitude'], 
									  $coordenadasFilho['longitude'], 
									  $coordenadasDestino['latitude'], 
									  $coordenadasDestino['longitude']);

		
		//echo "Filho: $idFilho - Distancia: $distancia <br/>";
		if ($melhorDistancia > $distancia) {
			$melhorDistancia = $distancia;
			$melhorIdFilho = $idFilho;
		}

	}	

	return $melhorIdFilho;

}