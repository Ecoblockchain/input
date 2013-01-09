<?php

var_dump($_SERVER['DOCUMENT_ROOT']);

define('VERTERE_DIR', '/Applications/MAMP/htdocs/TDTInput/includes/Vertere-RDF/dist/');
define('MORIARTY_DIR', VERTERE_DIR . 'lib/moriarty/');
define('MORIARTY_ARC_DIR', VERTERE_DIR . 'lib/arc/');

define('NS_CONV', 'http://example.com/schema/data_conversion#');
define('NS_RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');


include_once MORIARTY_DIR . 'moriarty.inc.php';
include_once MORIARTY_DIR . 'simplegraph.class.php';
include_once VERTERE_DIR . 'inc/sequencegraph.class.php';
include_once VERTERE_DIR . 'inc/vertere.class.php';
include_once VERTERE_DIR . 'inc/diagnostics.php';

class RDFMapper extends AMapper {

    private $vertere;

    function __construct($config) {
        parent::__construct($config);
        
        $spec_file_name = 'http://localhost:8888/airports.csv.spec.ttl';
        $spec_file = file_get_contents($spec_file_name);
        $spec = new SimpleGraph();
        $spec->from_turtle($spec_file);

        //Find the spec in the graph
        $specs = $spec->get_subjects_of_type(NS_CONV . 'Spec');
        if (count($specs) != 1) {
            throw new Exception('spec document must contain exactly one conversion spec');
        }

        $this->vertere = new Vertere($spec, $specs[0]);
    }

    public function execute(&$chunk) {
//        $graph = new EasyRdf_Graph();
//        $baseurl = Config::get("general","hostname").Config::get("general","subdir");
//        
//        $thing = $graph->resource($baseurl . $chunk["0"], $this->config["row"]["class"]);
//        $i = 0;
//        foreach($this->config["columns"] as $index => $col){
//            $thing->set($col,$chunk[$index]);
//            $i++;
//        }
//        return $graph;
        //Check if mapping file is the current one
        //Load spec and create new Vertere converter
        return $this->vertere->convert_array_to_graph($chunk);
    }

}
