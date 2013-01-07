<?php

/**
 * TDTInput is the main model class
 * @author: Pieter Colpaert
 */
class TDTInput {

    //Extractor, Mapper, Loader
    private $e,$m,$l;

    //$ts is an array of transformers, as more transformations can be 
    private $ts;
    
    /**
     * Reads the input.ini file and initiates all classes according to their configuration
     */
    public function __construct($config){
        $src = $config["source"];

        //parse ini file for the extractor and create an instance of the right class
        $extractmethod = $config["extract"];
        $extractorclass = $extractmethod . "Extractor";
        include_once("cores/input/model/extract/AExtractor.class.php");
        include_once("cores/input/model/extract/CSVExtractor.class.php");
        $extractorconfig = parse_ini_file("custom/" . $config["extractfile"]);
        $this->e = new $extractorclass($src,$extractorconfig);
        
        //parse ini files for the transformers
        $this->ts = array();

        //parse ini file for the mapper
        $mapperconfig = parse_ini_file("custom/" . $config["mapfile"],true);
        $mapmethod = $config["map"] . "Mapper";
        include_once("cores/input/model/map/AMapper.class.php");
        include_once("cores/input/model/map/RDFMapper.class.php");
        $this->m = new $mapmethod($mapperconfig);
        
        //parse ini file for the loader
        include_once("cores/input/model/load/ALoader.class.php");
        include_once("cores/input/model/load/CLILoader.class.php");
        include_once("cores/input/model/load/RDFLoader.class.php");
        $loaderconfig = parse_ini_file("custom/" . $config["loadfile"]);
        $this->l = new RDFLoader($loaderconfig);
    }

    /**
     * Execute our model according to the configuration parsed in the constructor
     */
    public function execute(){
        Log::getInstance()->logInfo("Starting the extractor");
        $numberofchunks = 0;

        while($this->e->hasNext()){
            //1. EXTRACT
            $chunk = $this->e->pop();

            //2. TRANSFORM
            foreach($this->ts as $t){
                $chunk = $t->execute($chunk);
                Log::getInstance()->logInfo("Transform: ",$chunk);
            }

            //3. MAP
            if(!is_null($this->m)){
                $chunk = $this->m->execute($chunk);
            }

            //4. LOAD
            if(!is_null($this->l)){
                $this->l->execute($chunk);
            }

            $numberofchunks++;
        }
        Log::getInstance()->logInfo("Loaded $numberofchunks chunks in the store");
    }
}
