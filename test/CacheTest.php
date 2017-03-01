<?php
require_once(dirname(__FILE__).'/../vendor/autoload.php');
//require_once(dirname(__FILE__).'/../Cache.php');

use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase {

    public function testEmpty() {
        $cache = new SmartCache();
        $this->assertNull($cache->get(1));
    }

    public function testPut() {
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(15);
        $key1 = 'A';
        $value1 = '1234567';
        $cache->put($key1, $value1);
        $this->assertEquals($cache->get($key1), $value1);
    }

    public function testGet() {
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(15);
        $key = 'A';
        $data = '1234567';
        $cache->put($key, $data);
        $this->assertEquals($cache->get($key), $data);
    }

    public function testPutWhenFull() {
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(15);

        $key1 = 'A';
        $key2 = 'B';
        $key3 = 'C';
        $key4 = 'D';

        $data1 = '1234567890';
        $data2 = '1234';
        $data3 = '1';
        $data4 = '567';

        $cache->put($key1, $data1);
        $cache->put($key2, $data2);
        $cache->put($key3, $data3);
        $this->assertEquals($cache->get($key1), $data1);
        $cache->put($key4, $data4);
        $this->assertNull($cache->get($key2));
        $this->assertEquals($cache->get($key1), $data1);
    }
}