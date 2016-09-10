<?php

/*
* SCRIPT DE IMPORTAÇÃO DOS ACIDENTES DE CSV PARA MySQL PARA MELHOR MANIPULAÇÃO DE DADOS
*/
 
// Abrir arquivo para leitura
$arq = fopen('files/acidentes-2015.csv', 'r');
if ($arq) {
 
    // Ler cabecalho do arquivo
    $cabecalho = fgetcsv($arq, 0, ',');
 
    // Enquanto nao terminar o arquivo
    while (!feof($arq)) {
 
        // Ler uma linha do arquivo
        $linha = fgetcsv($arq, 0, ',');
        if (!$linha) {
            continue;
        }
 
        // Montar registro com valores indexados pelo cabecalho
        $conteudoLinha = array_combine($cabecalho, $linha);

        //echo "<pre>";
        //print_r($conteudoLinha);

        //chama função inserção ao banco
        inserir($conteudoLinha);

    }
    fclose($arq);
}

function inserir($arrayDados = false){
	
	//verifica se o array não é vazio
	if ($arrayDados) {

		//conecta ao banco
		$conecta = mysql_connect("localhost", "root", "") or print (mysql_error()); 
		
		//abre a base de dados
		mysql_select_db("ia_trab_ga", $conecta) or print(mysql_error()); 
		
		//cria o comando SQL
		$sql = "INSERT INTO acidentes (
										logradouro_1, logradouro_2, altura_numero, 
									   	tipo_localidade, tipo_acidente, data, 
									   	dia_semana, clima, qtde_automovel, 
					 				   	qtde_motocicleta, latitude, longitude
					 				  )

					 VALUES (
					 		'".$arrayDados['logradouro_1']."', '".$arrayDados['logradouro_2']."', '".$arrayDados['altura_numero']."',
					 		'".$arrayDados['tipo_localidade']."', '".$arrayDados['tipo_acidente']."', '".$arrayDados['data']."',
					 		'".$arrayDados['dia_semana']."', '".$arrayDados['clima']."', '".$arrayDados['qtde_automovel']."',
					 		'".$arrayDados['qtde_motocicleta']."', '".$arrayDados['latitude']."', '".$arrayDados['longitude']."'
					 		)";
		
		//executa o comando SQL
		$result = mysql_query($sql, $conecta);
	}
}