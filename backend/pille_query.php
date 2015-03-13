<?php 
require ("resources/easyrdf/vendor/autoload.php");

class pille_query{


private $medical_drugs;


public function __construct(){

/* Set up prefixs */
EasyRdf_Namespace::set('rdf','http://www.w3.org/1999/02/22-rdf-syntax-ns#');        EasyRdf_Namespace::set('owl','http://www.w3.org/2002/07/owl#');                     EasyRdf_Namespace::set('xsd','http://www.w3.org/2001/XMLSchema#');
EasyRdf_Namespace::set('rdfs','http://www.w3.org/2000/01/rdf-schema#');
EasyRdf_Namespace::set('lmh','http://www.semanticweb.org/espen/ontologies/2015/2/untitled-ontology-10#');
}

public function query($query){
$sparql = new EasyRdf_Sparql_Client("http://pille.idi.ntnu.no/api/sparql");
$result = $sparql->query($query);
return $result;
}
}

?>


