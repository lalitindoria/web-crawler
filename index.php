<?php
/*
 * Accepts URL from command line
 */
include_once('Crawl.php');

$url = empty($argv[1]) ? NULL : $argv[1] ;

if(!$url) {
    print "Please pass the URL as a parameter.".PHP_EOL;
    die();
}

$crawler = new Crawl($url);

//Initiate crawling
$urls = $crawler->run();
