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

    public function testCorrectRemoveOrder(){
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(15);

        $key1 = 'A';
        $key2 = 'B';
        $key3 = 'C';

        $data1 = '12345';
        $data2 = '67890';
        $data3 = '11111';

        $cache->put($key1, $data1);
        $cache->put($key2, $data2);
        $cache->put($key3, $data3);

        $cache->get($key3);
        $cache->get($key2);

        $key4 = 'D';
        $key5 = 'E';

        $data4 = '22222';
        $data5 = '33333';

        $cache->put($key4, $data4);
        $this->assertNull($cache->get($key1));

        $cache->put($key5, $data5);
        $this->assertNull($cache->get($key3));
    }

    public function testSingleReplace(){
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(5);

        $key1 = 'A';
        $key2 = 'B';

        $data1 = '12345';
        $data2 = '67890';

        $cache->put($key1, $data1);
        $cache->put($key2, $data2);

        $this->assertEquals($cache->get($key2), $data2);
    }

    public function testUpdateExisting(){
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(7);

        $key1 = 'A';
        $data1 = '12345';
        $data2 = '6789';

        $cache->put($key1, $data1);
        $cache->put($key1, $data2);

        $this->assertEquals($cache->get($key1), $data2);
    }

    public function testUpdateSetCorrectLength(){
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(7);

        $key1 = 'A';
        $data1 = '12345';
        $data2 = '6789';

        $cache->put($key1, $data1);
        $cache->put($key1, $data2);

        $key2 = 'B';
        $data3 = '777';

        $this->assertEquals($cache->get($key1), $data2);
    }

    public function testCorrectFullReplacement(){
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(7);

        $key1 = 'A';
        $key2 = 'B';

        $data1 = '123';
        $data2 = '6789';

        $cache->put($key1, $data1);
        $cache->put($key2, $data2);

        $key3 = 'C';
        $data3 = '1234567';
        $cache->put($key3, $data3);

        $this->assertEquals($cache->get($key3), $data3);
        $this->assertNull($cache->get($key1));
        $this->assertNull($cache->get($key2));
    }

    public function testUpdateLengthLimit(){
        $cache = new SmartCache();
        $cache->setMaximumCharactersCount(7);

        $key1 = 'A';
        $key2 = 'B';

        $data1 = '123';
        $data2 = '678912';
        $data3 = '123';

        $cache->put($key1, $data1);
        $cache->put($key2, $data3);

        $cache->put($key1, $data2);

        $this->assertNull($cache->get($key2));
        $this->assertEquals($cache->get($key1), $data2);
    }
}