<?php

/**
 * Usage sample (to see the output just copy all the files on a web server and
 * point your your browser here).
 *
 * @package Samples
 */

// load the library
require('parser.php');

/**
 * The simplest ever custom parser. Returns a random 4-digit number
 */
class RandomNoGenerator {
    /**
     * Just returns the random number
     */
    public function parse() {
        return rand(1000, 9999);
    }
}

/**
 * A sample cyclic parser using an array of names as data-source
 */
class SimplestCyclicParser extends PhemeLoopParser {
    /**
     * Custom per item behavior
     */
    public function _parseItem($html, $vars, $blockName = 'document', $blockParams = null) {
       return parent::_parseItem($html, array('name' => $vars), $blockName, $blockParams);
    }
}

/*
 * Note that there is a generic PhemeLoopParser designed for usage in loops.
 * It is an overall good idea to use it (or its descendants) for your loop needs
 * but in our case we only use one variable in loops, so I developed a simpler
 * implementation from scratch.
 */

// our cyclic parser's data-source, as simple as it could be
$persons = array(
    'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
    'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
    'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
    'Ana', 'Marina', 'Vasile', 'Victoria', // 19 items
);

// sample block, with no extra logic, just some static vars
$subParser = new PhemeParser(array(
    '$title' => 'Subtitle (overrides upper level variable)',
    '$info' => '<strong>Startic vars are {notParsed/}</strong>.',
));

// master template parser. We link all the rules and subtemplates to this instance
$documentParser = new PhemeParser(array(
    // static variables
    '$title' => 'Document title',
    '$customVar' => '<em>top level variable value</em>',
    '$sampleArray' => array(
        'middle' => array('inner' => 'working'),
    ),

    // dynamic vars
    '@body' => 'Body content (block): {blockParser}<strong>templated</strong> - {@subTemplate}{/blockParser}',

    // blocks and block templates (dynamic vars)
    'subTemplate' => '1 - {$title}, 2 - {$customVar}',
    'blockParser' => $subParser,
    'randomNo' => new RandomNoGenerator(),

    // using skins in cycles
    'cycleSkin' =>
        /*
         * The {itemSkin/} block is required to fire-up the cycle.
         * If it will not be present in the template the cycle will just NOT run!
         */
        '{itemSkin}Item name: {$name} <br />{/itemSkin}',
    '@cycleResults' => "{@cycleSkin}",
    'cycleResults' => new PhemeParser(array(
        '$name' => 'TOP LEVEL',
        'itemSkin' => new SimplestCyclicParser(array(), array(), $persons),
    ), array('skinnable' => true)),
));

// load, parse and output the document
$document = file_get_contents('template.html');
echo $documentParser->parse($document);
//exit();

// =========================== BENCHMARKING ====================================

// benchmarking Pheme:
$start = microtime(true);
echo '<br />Pheme benchmark 100 loops: [ ';
for ($i = 0; $i < 10; $i++) {
    for ($j = 0; $j < 10; $j++) {
        $documentParser->parse($document);
    }
    echo '. ';
    flush();
}
echo '] '.(microtime(true)- $start).' sec ('.PhemeParser::$iterations.' iterations)';

