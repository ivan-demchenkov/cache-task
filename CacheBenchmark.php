<?php

require ('Cache.php');

//error_reporting(0);

class CacheBenchmark{

    private $keys = [];
    private $lastKey;
    private $cache;

    protected function reset(){
      $this->cache = new SmartCache();
      $this->keys = [];
      $this->fillCache();
    }

    protected function generateRandomString($length = null) {
        if($length == null)
        {
          $length = rand(1, 10);
        }
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function testPut($elementsSize, $putElementLength = null){
        $this->fillCache($elementsSize);

        $new_key = $this->lastKey++;
        $this->keys[] = $new_key;
        $new_string = $this->generateRandomString($putElementLength);

        $start_time = microtime(TRUE);
    //    if (session_status() == PHP_SESSION_NONE) {
            session_start();
    //    }
        $this->cache->put($new_key, $new_string);
        $end_time = microtime(TRUE);

        return $end_time - $start_time;
    }

    public function testGet($elementsSize){
        $this->fillCache($elementsSize);

        $test_key = array_rand($this->keys);

        $start_time = microtime(TRUE);
      //  if (session_status() == PHP_SESSION_NONE) {
            session_start();
      //  }
        $this->cache->get($test_key);
        $end_time = microtime(TRUE);

        return $end_time - $start_time;
    }

    protected function fillCache($elementsSize){
      $this->cache = new SmartCache();
      $this->cache->setMaximumCharactersCount($elementsSize*2);
      $this->keys = [];

      $total_len = 0;
      $key = 1;
      while($total_len < $elementsSize){
        $random_element = $this->generateRandomString();

        $this->cache->put($key, $random_element);
        $this->keys[] = $key;
        $key++;
        $total_len += strlen($random_element);
      }
      $this->lastKey = $key;
    }

    public function getKeySize(){
      return sizeof($this->keys);
    }
}

$benchmark = new CacheBenchmark();
$put_result = $benchmark->testPut(100000, 10000);
echo 'put in cache with '.$benchmark->getKeySize().' elements element with 10000 length: '.$put_result.PHP_EOL;
//echo 'put in cache with '.$benchmark->getKeySize().' elements: '.$put_result.PHP_EOL;

//$get_result = $benchmark->testGet(2000000);
//echo 'get from cache with '.$benchmark->getKeySize().' elements: '.$get_result.PHP_EOL;
