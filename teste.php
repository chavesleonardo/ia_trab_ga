<?php

require_once('funcoes.php');

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


#Pega id nodo destino
$idNodoDestino = getIdNodo($destino['latitude'],$destino['longitude']);

$arrayNodosVisitados = array();

#primeira chamada
getMenorFilho($origem['latitude'],$origem['longitude'], $destino['latitude'],$destino['longitude'], $idNodoDestino, $arrayNodosVisitados);

//getAcidentesPorRegiaoComFiltros(-30.0611, -30.0477, -51.2305, -51.2212, 'cruzamento');

//getAcidentesPorRegiaoComFiltros(-30.0611, -30.0477, -51.2305, -51.2212, 'cruzamento');

//echo getQuantidadeAcidentesPorRaio(-30.049462, -51.224195, 0.05);