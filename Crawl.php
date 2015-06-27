<?php
/**
 * Crawl class contains all functions related to scanning and saving URLs
 */
include_once('simple_html_dom.php');

class Crawl {

    private $targetUrl;
    private $html;
    private $urls = array();
    public static $count = 0;

    function __construct($targetUrl) {
        $this->targetUrl = $targetUrl;
        self::$count++;
    }

    /*
     * Initiate crawling and crawl 10 URLs
     */
    function run()
    {
        print "URL number ".self::$count.PHP_EOL;
        print "Crawling ".$this->targetUrl.PHP_EOL;

        if(!$this->validateUrl()){
            print "URL is invalid or not reachable!".PHP_EOL;
        } else {
            $this->html = new simple_html_dom();
            $this->html->load_file($this->targetUrl);
            $this->scanUrls();
            if(!empty($this->urls))
                $this->saveUrls();
        }

        if(self::$count == 10) {
            print "Finished crawling ".self::$count." URLs!".PHP_EOL;
            die();
        }

        //Start recursion
        $urls = $this->urls;
        $this->clear();

        foreach($urls as $url) {
            $obj = new Crawl($url);
            $obj->run();
        }
    }

    /**
     * Validate a given URL
     * @return bool
     */
    function validateUrl() {
        //check, if a valid url is provided
        if(!filter_var($this->targetUrl, FILTER_VALIDATE_URL))
        {
            return false;
        }

        try {
            $headers = get_headers($this->targetUrl, 1);
        }
        catch(Exception $e) {
            print "Could not connect to ".$this->targetUrl.PHP_EOL;
            return false;
        }

        if(!empty($headers)) {
            print "Got headers for ".$this->targetUrl.PHP_EOL;
            foreach($headers as $header){
                if(preg_match('/^HTTP\/\S+\s+([1-9][0-9][0-9])\s+.*/', $header, $matches) ){// "HTTP/*** ### ***"
                    $code = (int)$matches[1];
                    if($code == 200) return true;
                    else return false;
                }
            }
        }
        else return false;
    }

    /**
     * Scan a webpage for all URLs
     * @return array of URLs
     */
    function scanUrls()
    {
        foreach($this->html->find('a') as $link)
        {
            $href = rtrim($link->href,"/");
            if(filter_var($href, FILTER_VALIDATE_URL) && $href != $this->targetUrl) {
                $this->urls[] = $href;
            }
        }

        $this->urls = array_unique($this->urls);
    }

    /*
     * Write all URLs to a file
     */
    function saveUrls() {
        try {
            $file = fopen("URL-".self::$count.".txt", "w");
        } catch(Exception $e) {
            print "Could not create file!";
            return false;
        }

        if(!$file)
            return false;

        foreach($this->urls as $url) {
            fwrite($file,$url.PHP_EOL);
        }

        fclose($file);

        print "Finished writing ".count($this->urls)." URLs to URL-".self::$count.".txt".PHP_EOL;
    }

    /*
     * Make all variables null instead of unsetting
     */
    private function clear() {
        $this->targetUrl = null;
        $this->html = null;
        $this->urls = array();
    }

    function __destruct() {
        print "Destroying object".PHP_EOL;
        $this->clear();
    }

}