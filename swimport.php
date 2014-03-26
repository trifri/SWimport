<html>
	<body>
		<h1>Quick-and-easy information finder</h1>

		<p>Get information about an actor, director, tv-show or movie by typing it below.</p>

		<form action="?" method="get">
			Search:  <input type="text" name="searchterm"> <br><br>
			<input type="submit" value ="Submit">
		</form>

		<p><strong>Results for 
		<?php echo isset($_GET['searchterm']) ? htmlspecialchars($_GET['searchterm']) : '';?>:</strong></p> <!--Hva skjer her?-->



		
		<!-- The actual data import code starts here -->

		<!-- Author of a large part of the code: http://johnwright.me/code-examples/sparql-query-in-code-rest-php-and-json-tutorial.php -->



		<?php

		$searchterm = '';
		if(isset($_GET['searchterm'])) $searchterm = $_GET['searchterm'];

		//Adding underscores to make it searchable in DBpedia
		function addUnderscores($var){
				$newvar = preg_replace('/\s+/', '_', $var);
				return $newvar;
			
		}

		function getUrlDbpediaAbstract($search){
		   $format = 'json';

		   $query =
		   "PREFIX dbp: <http://dbpedia.org/resource/>
		   PREFIX dbp2: <http://dbpedia.org/ontology/>
		   PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
 
		   SELECT ?abstract, ?type
		   WHERE {
		      dbp:".$search." dbp2:abstract ?abstract .
		      dbp:".$search." rdf:type ?type .
		      FILTER langMatches(lang(?abstract), 'en')
		   }";
   
		   $searchUrl = 'http://dbpedia.org/sparql?'
		      .'query='.urlencode($query)
		      .'&format='.$format;

		   return $searchUrl;
		}


		function request($url){

		if (!function_exists('curl_init')){
			die('CURL is not installed!');
		}
		$ch= curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		/*
			For more information on curl:
			http://www.php.net/curl_setopt
		*/	
		$response = curl_exec($ch); 
		curl_close($ch);
		return $response;
	}


	function printArray($array, $spaces = ""){

		$retValue = "";
		if(is_array($array)){	
			$spaces = $spaces."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 
			$retValue = $retValue."<br/>";
 
			foreach(array_keys($array) as $key){
				$retValue = $retValue.$spaces."<strong>".$key."</strong>".printArray($array[$key],$spaces);
			}	
			$spaces = substr($spaces, 0, -30);
		}
		else $retValue = $retValue." - ".$array."<br/>";
		return $retValue;
	}

	$requestURL = getUrlDbpediaAbstract(addUnderscores($searchterm));

	$responseArray = json_decode(request($requestURL),true); 


	//Prints the information returned from DBpedia
	if($searchterm != null && isset($responseArray["results"]["bindings"][0]["abstract"]["value"])){ 
		echo $responseArray["results"]["bindings"][0]["abstract"]["value"];
	} else echo "No results. Are you sure the spelling is correct?";


	?>



	</body>
</html>