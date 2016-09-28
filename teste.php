<?php

ob_start();
session_start();
require_once('funcoes.php');

apagaTabelaTemp2();

#36
$origem['latitude'] = '-30.054186';
$origem['longitude'] = '-51.224868';

#5
$destino['latitude'] = '-30.048554';
$destino['longitude'] = '-51.221660';


#Pega id nodo destino
$idNodoDestino = getIdNodo($destino['latitude'],$destino['longitude']);

$arrayNodosVisitados = array();
$_SESSION['listaCoordenadas'] = array();

#primeira chamada
getMenorFilho($origem['latitude'],$origem['longitude'], $destino['latitude'],$destino['longitude'], $idNodoDestino, $arrayNodosVisitados);

if (count($_SESSION['listaCoordenadas']) > 0) {
	foreach ($_SESSION['listaCoordenadas'] as $ordem => $dados) {
		$ultimo .= "{lat: ".$dados['latitude'].", lng: ".$dados['longitude']."}, ";
	}
}

$_SESSION['dados_mapa_2'] = $ultimo;

apagaTabelaTemp();