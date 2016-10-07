<?php

require_once('nodos.php');

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
	array_push($sortRotas, array('17','50'));
	array_push($sortRotas, array('17','22'));
	array_push($sortRotas, array('22','20'));
	array_push($sortRotas, array('22','15'));
	array_push($sortRotas, array('40','17'));
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

function a_star($startNode, $goalNode){

	$openSet  = array();
	$closedSet= array();
	$fScore   = array();
	$gScore   = array();
	$cameFrom = array();
	$listaNodos = listarNodos();

	$openSet = adicionarLista($openSet, $startNode);
	$gScore[$startNode] = 0;
	$fScore[$startNode] = heuristicCostEstimate($startNode, $goalNode);
	$cameFrom = adicionarLista($cameFrom,$startNode);

	while (!empty($openSet)) {

		$idCurrent = getLowerCost($openSet, $fScore);

		if ($idCurrent == $goalNode) {
			/*
			echo "Open:<br>";
			echoArray($openSet);
			echo "Closed:<br>";
			echoArray($closedSet);
			echo "fScore:<br>";
			echoArray($fScore);
			echo "gScore:<br>";
			echoArray($gScore);
			echo "cameFrom:<br>";
			echoArray($cameFrom);
			echoArray(reconstructPath($cameFrom, $idCurrent));
			exit;
			*/
			#return reconstructPath($cameFrom, $idCurrent);
			$rota = reconstructPath($cameFrom, $idCurrent);
			$_SESSION['dados_rota_padrao'] = desenhaRota($rota);
		}

		$openSet = removerLista($openSet, $idCurrent);
		$closedSet = adicionarLista($closedSet, $idCurrent);
		$filhosCurrent = $listaNodos[$idCurrent]['filhos'];

		foreach ($filhosCurrent as $idFilhoCurrent => $distanciaFilhoCurrent) {

			if (in_array($idFilhoCurrent, $closedSet)) {
				# Ignore idFilhoCurrent which is already evaluated.
			}else{

				# The distance from start to idFilhoCurrent
				$gScoreTentative = $gScore[$idCurrent] + distBetween($idCurrent, $idFilhoCurrent);

				if (!in_array($idFilhoCurrent, $openSet)) {
					$openSet = adicionarLista($openSet, $idFilhoCurrent);

				}else if($gScoreTentative >= $gScore[$idFilhoCurrent]){
					# This is not a better path.

				}

				# This path is the best until now. Record it!
	            $cameFrom = adicionarLista($cameFrom,$idFilhoCurrent);
	            $gScore[$idFilhoCurrent] = $gScoreTentative;
	            $fScore[$idFilhoCurrent] = $gScore[$idFilhoCurrent] + heuristicCostEstimate($idFilhoCurrent, $goalNode);

			}

		}

	}

}//end a_star

function new_a_star($startNode, $goalNode){

	$openSet  = array();
	$closedSet= array();
	$fScore   = array();
	$gScore   = array();
	$cameFrom = array();
	$listaNodos = listarNodos();

	$openSet = adicionarLista($openSet, $startNode);
	$gScore[$startNode] = 0;
	$fScore[$startNode] = heuristicCostEstimate($startNode, $goalNode);
	$cameFrom = adicionarLista($cameFrom,$startNode);

	while (!empty($openSet)) {

		$idCurrent = getLowerCost($openSet, $fScore);

		if ($idCurrent == $goalNode) {
			/*
			echo "Open:<br>";
			echoArray($openSet);
			echo "Closed:<br>";
			echoArray($closedSet);
			echo "fScore:<br>";
			echoArray($fScore);
			echo "gScore:<br>";
			echoArray($gScore);
			echo "cameFrom:<br>";
			echoArray($cameFrom);
			echoArray(reconstructPath($cameFrom, $idCurrent));
			exit;
			*/
			#return reconstructPath($cameFrom, $idCurrent);
			$rota = reconstructPath($cameFrom, $idCurrent);
			$_SESSION['dados_rota_acidentes'] = desenhaRota($rota);

			$_POST['coordenadasOrigem'] = $listaNodos[$startNode]['latitude'].','.$listaNodos[$startNode]['longitude'];
			$_POST['coordenadasDestino'] = $listaNodos[$goalNode]['latitude'].','.$listaNodos[$goalNode]['longitude'];

		}

		$openSet = removerLista($openSet, $idCurrent);
		$closedSet = adicionarLista($closedSet, $idCurrent);
		$filhosCurrent = $listaNodos[$idCurrent]['filhos'];

		foreach ($filhosCurrent as $idFilhoCurrent => $distanciaFilhoCurrent) {
			if($listaNodos[$idFilhoCurrent]['acidentes'] != 0){
				unset($filhosCurrent[$idFilhoCurrent]);
			}
		}

		foreach ($filhosCurrent as $idFilhoCurrent => $distanciaFilhoCurrent) {

			if (in_array($idFilhoCurrent, $closedSet)) {
				# Ignore idFilhoCurrent which is already evaluated.
			}else{

				# The distance from start to idFilhoCurrent
				$gScoreTentative = $gScore[$idCurrent] + distBetween($idCurrent, $idFilhoCurrent);

				if (!in_array($idFilhoCurrent, $openSet)) {
					$openSet = adicionarLista($openSet, $idFilhoCurrent);

				}else if($gScoreTentative >= $gScore[$idFilhoCurrent]){
					# This is not a better path.
				}

				# This path is the best until now. Record it!
	            $cameFrom = adicionarLista($cameFrom,$idFilhoCurrent);
	            $gScore[$idFilhoCurrent] = $gScoreTentative;
	            $fScore[$idFilhoCurrent] = $gScore[$idFilhoCurrent] + heuristicCostEstimate($idFilhoCurrent, $goalNode);

			}

		}

	}

}//end a_star

function getLowerCost($openSet, $fScore){
	
	$retorno = array('lowestCost' => 0, 'lowestId' => 0);

	foreach ($openSet as $idNodo) {
		if ($retorno['lowestCost'] == 0) {
			$retorno['lowestCost'] = $fScore[$idNodo];
			$retorno['lowestId'] = $idNodo;
		}else{
			if ($fScore[$idNodo] < $retorno['lowestCost']) {
				$retorno['lowestCost'] = $fScore[$idNodo];
				$retorno['lowestId'] = $idNodo;
			}
		}
	}

	return $retorno['lowestId'];
}

function heuristicCostEstimate($idStart, $idGoal){

	$startNodeData = listarNodos($idStart);
	$goalNodeData = listarNodos($idGoal);

	return getDistance( $startNodeData['latitude'], 
						$startNodeData['longitude'], 
						$goalNodeData['latitude'], 
						$goalNodeData['longitude'] );

}

function distBetween($idStart, $idGoal){

	$startNodeData = listarNodos($idStart);
	$goalNodeData = listarNodos($idGoal);

	return getDistance( $startNodeData['latitude'], 
						$startNodeData['longitude'], 
						$goalNodeData['latitude'], 
						$goalNodeData['longitude'] );

}

function reconstructPath($cameFrom, $idCurrent){
	
	$listaIds = array_reverse($cameFrom);
	$arrRetorno[] = $idCurrent;

	foreach ($listaIds as $idNodo) {

		if ($idNodo != end($arrRetorno)) {
			$filhosIdNodo = listarNodos(end($arrRetorno));
			
			if ( array_key_exists($idNodo, $filhosIdNodo['filhos']) ) {
				$arrRetorno[] = $idNodo;
			}
		}
		
	}

	return (!empty($arrRetorno)) ? $arrRetorno : false ;
}

function desenhaRota($caminho){
	$retorno = '';
	if (!empty($caminho)) {
		foreach ($caminho as $idNodo) {
			$dadosNodo = listarNodos($idNodo);
			$retorno .= "{lat: ".$dadosNodo['latitude'].", lng: ".$dadosNodo['longitude']."}, ";
		}
	}
	return $retorno;
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