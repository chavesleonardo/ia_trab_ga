# ia_trab_ga
Trabalho do Grau A de Inteligência Artificial

**Objetivo:**
Criar um algoritmo baseado em A\* que evite rotas que contenham mais de 3 acidentes.

**Descrição:**
Uma área de um bairro de Porto Alegre foi delimitada para obter-se os nodos de cada ponto. O bairro escolhido foi Menino Deus. Ao todo foram criados 68 nodos. Para cada nodo, foi verificado a quantidade de acidentes em um raio de 50 metros, visto que a base de acidentes não continha todas as coordenadas precisamente. O sistema irá exibir uma rota padrão e outra rota com o algoritmo modificado. Caso todas as rotas contenham mais de 3 acidentes o algoritmo é abortado, visto que não há alternativa.

**Desenvolvimento:**
Foi utilizado PHP para linguagem de programação com interface web HTML, CSS e JAVASCRIPT para implementação das funcionalidades do google maps.

**Limitações**
- Obter uma lista precisa de nodos das ruas de porto alegre (muitos nodos estavam dentro de terrenos).
- Coordenadas dos acidentes do *datapoa* continham muitas coordenadas imprecisas, necessitando buscar por um raio de 50 metros.
- Conseguir gerar uma rota utilizando o sentido da via.

**Bugs Encontrados**
- Não calcula o *g(n)*.
- Não adiciona fronteiras no open, dando a rota como encerrada e não encontrada.

**Nova tentativa de correção de bugs criando um algoritmo baseado no seguinte pseudocódigo:**
https://en.wikipedia.org/wiki/A*_search_algorithm#Pseudocode

**LIVE DEMO**
https://algoritmoastar.herokuapp.com/
