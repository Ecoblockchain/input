<?php
    set_include_path(get_include_path() . PATH_SEPARATOR . './lib/');
    require_once "EasyRdf.php";

    // Start building up a RDF graph
    $doap = new EasyRdf_Graph('http://www.aelius.com/njh/easyrdf/doap.rdf');
    $easyrdf = $doap->resource('#easyrdf', 'doap:Project', 'foaf:Project');
    $easyrdf->addLiteral('doap:name',  'EasyRDF');
    $easyrdf->addLiteral('doap:shortname', 'easyrdf');

    // Load some properties from the composer file
    $composer = json_decode(file_get_contents('composer.json'));
    $easyrdf->addLiteral('doap:revision', $composer->version);
    $easyrdf->addLiteral('doap:shortdesc', $composer->description, 'en');
    $easyrdf->addResource('doap:homepage', $composer->homepage);

    $easyrdf->addLiteral('doap:programming-language', 'PHP');
    $easyrdf->addLiteral(
        'doap:description', 'EasyRdf is a PHP library designed to make it easy to consume and produce RDF. '.
        'It was designed for use in mixed teams of experienced and inexperienced RDF developers. '.
        'It is written in Object Oriented PHP and has been tested extensively using PHPUnit.', 'en'
    );
    $easyrdf->addResource('doap:license', 'http://usefulinc.com/doap/licenses/bsd');
    $easyrdf->addResource('doap:download-page', 'http://github.com/njh/easyrdf/downloads');
    $easyrdf->addResource('doap:download-page', 'http://github.com/njh/easyrdf/downloads');
    $easyrdf->addResource('doap:bug-database', 'http://github.com/njh/easyrdf/issues');
    $easyrdf->addResource('doap:mailing-list', 'http://groups.google.com/group/easyrdf');

    $easyrdf->addResource('doap:category', 'http://dbpedia.org/resource/Resource_Description_Framework');
    $easyrdf->addResource('doap:category', 'http://dbpedia.org/resource/PHP');
    $easyrdf->addResource('doap:category', 'http://dbpedialite.org/things/24131#id');
    $easyrdf->addResource('doap:category', 'http://dbpedialite.org/things/53847#id');

    $repository = $doap->newBNode('doap:GitRepository');
    $repository->addResource('doap:browse', 'http://github.com/njh/easyrdf');
    $repository->addResource('doap:location', 'git://github.com/njh/easyrdf.git');
    $easyrdf->addResource('doap:repository', $repository);

    $njh = $doap->resource('http://www.aelius.com/njh#me', 'foaf:Person');
    $njh->add('foaf:name', 'Nicholas J Humfrey');
    $easyrdf->add('doap:maintainer', $njh);
    $easyrdf->add('doap:developer', $njh);
    $easyrdf->add('foaf:maker', $njh);

    print $doap->serialise('rdfxml');
