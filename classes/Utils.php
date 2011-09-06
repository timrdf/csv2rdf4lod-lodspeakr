<? 

class Utils{
  
  public static function send303($uri, $ext){
  	header("HTTP/1.0 303 See Other");
  	header("Location: ".$uri);
  	header("Content-type: ".$ext);
  	echo $uri."\n\n";
  	exit(0);
  }
  
  public static function send404($uri){
  	header("HTTP/1.0 404 Not Found");
  	echo "I could not find ".$uri." or information about it.\n\n";
  	exit(0);
  }
  
  public static function send406($uri){
  	header("HTTP/1.0 406 Not Acceptable");
  	echo "I can't find a representation suitable for the content type you accept\n\n";
  	exit(0);
  }
  
  public static function send500($uri){
  	header("HTTP/1.0 500 Internal Server Error");
  	echo "An internal error ocurred. Please try later\n\n";
  	exit(0);
  }
  
  public static function uri2curie($uri){
  	global $conf;
  	$ns = $conf['ns'];
  	$curie = $uri;
  	foreach($ns as $k => $v){
  	  $curie = preg_replace("|^$v|", "$k:", $uri);
  	  if($curie != $uri){
  	  	break;
  	  }
  	}
  	return $curie;
  }
  
  public static function getTemplate($uri){
  	$filename = str_replace(":", "_", $uri);
  	if(file_exists ($filename)){
  	  include_once($filename);
  	}
  }
  
  private static function sparqlResult2Obj($data){
  	$aux = $data['results']['bindings'];
  	$obj = array();
  	foreach($aux as $w){
  	  $row = array();
  	  foreach($w as $k => $v){
  	  	$row['value'][$k] = $v['value'];
  	  	if($v['type'] == 'uri'){
  	  	  $row['curie'][$k] = Utils::uri2curie($v['value']);
  	  	  $row['uri'][$k] = 1;
  	  	}
  	  }
  	  if(sizeof($row) >0){
  	  	array_push($obj, $row);
  	  }
  	}
  	return $obj;
  }
  
  public static function showView($uri, $data, $view){
  	global $conf;
  	$base = $conf['view']['standard'];
  	$base['this']['value'] = $uri;
  	$base['this']['curie'] = Utils::uri2curie($uri);
  	$base['ns'] = $conf['ns'];
  	require('lib/Haanga/lib/Haanga.php');
  	Haanga::configure(array(
  	  'template_dir' => './',
  	  'cache_dir' => 'cache/',
  	  ));
  	$r = Utils::sparqlResult2Obj($data);  	
	$vars = compact('base', 'r');
	Haanga::Load($view, $vars);
  	
  }
  
  public static function getExtension($accept_string){
  	global $conf;
  	$extension = "html";
  	foreach($conf['http_accept'] as $ext => $accept_arr){
  	  if(in_array($accept_string, $accept_arr)){
  	    $extension = $ext;
  	  }
  	}
  	return $extension;
  }
  
  public static function getBestContentType($accept_string){
  	global $conf;
   /*
     * TODO: Choose best content type from
     * things like
     * "text/html;q=0.2,application/xml;q=0.1"
     * and so on. In the meantime,
     * assume there is only one CT
     */
     $a = split(",", $accept_string);
     $ct = 'text/html';
     if(strstr($a[0], ";")){
       $a = split(";", $a[0]);
 	 }
     foreach($conf['http_accept'] as $ext => $arr){
       if(in_array($a[0], $arr)){
         $ct = $a[0];
       }
	 }
     
     return $ct;
  }

}
?>
