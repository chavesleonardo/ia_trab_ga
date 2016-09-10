<?php

#altera o tempo limite para 300 seg
set_time_limit(300);

/*
* Essa função irá inserir 30254 nodos ao banco de dados e
* ---- ruas de acordo com os dados do arquivo menino_deus.osm deste projeto
*/

$nodeCounter = 0;
$wayCounter = 0;

#Lê o XML
$xml = simplexml_load_file("files/menino_deus.osm");
//$xml = simplexml_load_file("teste.osm");
//echo "<pre>";print_r($xml);exit;

#percorre o XML
foreach ($xml->children() as $tag => $tagData) {

	#Pega as informações do tipo NODE (pontos)
	if ($tag == 'node') {
		
		$arrayConteudo = array();

		//monta array com campos para o banco
		$arrayConteudo['id_original'] = $tagData['id'];
		$arrayConteudo['latitude'] = $tagData['lat'];
		$arrayConteudo['longitude'] = $tagData['lon'];
		
        //chama função inserção ao banco
        if(inserirNode($arrayConteudo)){
			$nodeCounter++;
        };

	}//if node

	#Pega as informações do tipo WAYS (vias)
	if ($tag == 'way') {

		$arrayConteudo = array();
		$wayCounter++;
		$idWayOriginal = 0;

		//monta array com campos para o banco
		$idWayOriginal = (string) $tagData['id'];
		if ($idWayOriginal != 0 || $idWayOriginal != '') {
	
			//salva no array o id inserido da rua (way)
			inserirWay($idWayOriginal);
			$wayCounter++;
		
			//percorre tag em busca de subtags chamadas ref e salva no array
			foreach($tagData->children() as $subTags) {

				$subTagRef = (string) $subTags['ref'];

				if ( $subTagRef != '') {
					inserirWayNode(array('way_id' => $idWayOriginal, 'node_id' => $subTagRef));
				}
			}

		}else{
			echo "Erro ao inserir way";
			exit;
		}

	} //if way
	
}

echo "<b>FIM!<br/>Nodos inseridos:</b> {$nodeCounter}";
echo "<br/><b>Ways Inseridas: </b>{$wayCounter}";

/*
* Insere os dados recebidos na tabela 'node'
*/
function inserirNode($arrayDados = false){
	
	//verifica se o array não é vazio
	if ($arrayDados) {

		//conecta ao banco
		$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
		
		//abre a base de dados
		mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
		
		//cria o comando SQL
		$sql = "INSERT INTO node ( id, latitude, longitude )
					 VALUES ( ".$arrayDados['id_original'].", '".$arrayDados['latitude']."', '".$arrayDados['longitude']."' )";
		
		//executa o comando SQL
		return mysql_query($sql, $conecta);
	}
}

/*
* Insere os dados recebidos na tabela 'way'
*/
function inserirWay($id = false){
	
	//verifica se o array não é vazio
	if ($id) {

		//conecta ao banco
		$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
		
		//abre a base de dados
		mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
		
		//cria o comando SQL
		$sql = "INSERT INTO way ( id )
					 VALUES ( ".$id." )";
		
		//executa o comando SQL
		return mysql_query($sql, $conecta);

	}
}

/*
* Insere os dados recebidos na tabela 'way_node'
* que é a N para N
*/
function inserirWayNode($arrayDados = false){
	
	//verifica se o array não é vazio
	if ($arrayDados) {

		//echo"<pre>";print_r($arrayDados);exit;

		//conecta ao banco
		$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
		
		//abre a base de dados
		mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
		
		//cria o comando SQL
		$sql = "INSERT INTO way_node ( way_id, node_id )
					 VALUES ( '".$arrayDados['way_id']."', '".$arrayDados['node_id']."' )";
		
		//executa o comando SQL
		return mysql_query($sql, $conecta);

	}
}