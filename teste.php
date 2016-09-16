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

#primeira chamada
getMenorFilho($origem['latitude'],$origem['longitude'], $destino['latitude'],$destino['longitude'], $idNodoDestino);
