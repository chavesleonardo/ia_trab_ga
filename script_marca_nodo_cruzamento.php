<?php

#altera o tempo limite para 300 seg
set_time_limit(300);

$nodeCounter = 0;

#Lê o XML
$xml = simplexml_load_file("files/nodos_cruzamento.xml");

#percorre o XML
foreach ($xml->children() as $tag => $tagData) {

	#Pega as informações do tipo NODE (pontos)
	if ($tag == 'node') {
				
        //chama função inserção ao banco
        if(alteraNode($tagData['id'])){
			$nodeCounter++;
        }

	}//if node
	
}

echo "<b>FIM!<br/>Nodos alterados:</b> {$nodeCounter}";

/*
* Insere os dados recebidos na tabela 'node'
*/
function alteraNode($idNode){
	
	//verifica se o id não é vazio
	if ($idNode) {

		//conecta ao banco
		$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
		
		//abre a base de dados
		mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
		
		//cria o comando SQL
		$sql = "UPDATE node SET cruzamento = 1 WHERE id = $idNode ";
		
		//executa o comando SQL
		return mysql_query($sql, $conecta);
	}
}