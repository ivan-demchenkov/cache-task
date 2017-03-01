<?php

/**
 * Interface ISmartCache
 */
interface ISmartCache
{
    public function setMaximumCharactersCount(int $maximum);
    public function get(string $key): ?string;
    public function put(string $key, string $value): void;
}

/**
 * Class SmartCache
 */
class SmartCache implements ISmartCache{

    private $maximumSize;
    private $availableSize;

    private $cache;

    private $cacheHead;
    private $cacheTail;

    /**
     * SmartCache constructor. Init doubly linked list to store cache, links head and tail
     */
    public function __construct()
    {
        $this->cache = [];
        $this->maximumSize = 0;
        $this->availableSize = 0;

        $this->cacheHead = new CacheElement(null, null);
        $this->cacheTail = new CacheElement(null, null);

        $this->cacheHead->setNext($this->cacheTail);
        $this->cacheTail->setPrevious($this->cacheHead);
    }


    /**
     * Set total character limit for cache
     *
     * @param int $maximum
     * @throws Exception
     */
    public function setMaximumCharactersCount(int $maximum){
        if($this->maximumSize != $this->availableSize){
            throw new Exception('Can\'t change limitation of non empty cache');
        }
        $this->maximumSize = $maximum;
        $this->availableSize = $maximum;
    }

    /**
     * Get cached value from cache by key if exist
     *
     * @param string $key
     * @return null|string
     */
    public function get(string $key): ?string{
        if(!isset($this->cache[$key])){
            return null;
        }

        $element = $this->cache[$key];
        if(count($this->cache) == 1){
            return $element->getValue();
        }

        $this->excludeElement($element);
        $this->upElement($element);

        return $element->getValue();
    }

    /**
     * Put key and value to the cache storage
     *
     * @param string $key
     * @param string $value
     * @throws Exception
     */
    public function put(string $key, string $value): void{
        $newValueLength = strlen($value);

        if($newValueLength > $this->maximumSize){
            throw new Exception('Cache value size more than cache size');
        }

        if(isset($this->cache[$key])){
            $this->updateExisting($this->cache[$key], $value);

        }
        else{
            $this->createNew($key, $value);
        }
    }


    /**
     * Move cache element to the cache list head
     *
     * @param CacheElement $cacheElement
     */
    protected function upElement(CacheElement $cacheElement){
        $cacheElement->setPrevious($this->cacheHead);
        $cacheElement->setNext($this->cacheHead->getNext());
        $cacheElement->getNext()->setPrevious($cacheElement);
        $cacheElement->getPrevious()->setNext($cacheElement);
    }

    /**
     * Detach element from doubly linked cache list
     *
     * @param CacheElement $cacheElement
     */
    protected function excludeElement(CacheElement $cacheElement){
        $cacheElement->getPrevious()->setNext($cacheElement->getNext());
        $cacheElement->getNext()->setPrevious($cacheElement->getPrevious());
    }

    /**
     * Update element in cache if given key is exist
     *
     * @param CacheElement $element
     * @param $value
     */
    protected function updateExisting(CacheElement $element, $value){
        $this->excludeElement($element);
        $this->upElement($element);

        $this->availableSize += $element->getLength();
        $element->setValue($value);
        $this->availableSize -= $element->getLength();
    }

    /**
     * Add new CacheElement to cache
     * @param $key
     * @param $value
     */
    protected function createNew($key, $value){
        $element = new CacheElement($key, $value);

        while($this->availableSize < $element->getLength()){
            $this->freeSpace();
        }

        $this->cache[$key] = $element;
        $this->availableSize -= $element->getLength();
        $this->upElement($element);
    }

    /**
     * Remove CacheElement that was not used for most longest time
     */
    protected function freeSpace(){
        $elementToRemove = $this->cacheTail->getPrevious();
        $this->excludeElement($elementToRemove);
        $this->availableSize += $elementToRemove->getLength();
        unset($this->cache[$elementToRemove->getKey()]);
    }
}


/**
 * Class CacheElement
 */
class CacheElement{

    private $key;
    private $value;
    private $length;


    private $previous;
    private $next;

    /**
     * CacheElement constructor.
     * @param $key
     * @param $value
     */
    function __construct($key, $value)
    {
        $this->key = $key;
        $this->setValue($value);
    }

    /**
     * @return mixed
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param mixed $previous
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;
    }

    /**
     * @return mixed
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param mixed $next
     */
    public function setNext($next)
    {
        $this->next = $next;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->setLength(strlen($this->value));
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    private function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}


// Example.
/*$cache = new SmartCache();

$cache->setMaximumCharactersCount(15);


$cache->put('A', '1234567890'); // 10 chars im memory
$cache->put('B', '1234');       // 4 + 10 = 14 chars in memory
$cache->put('C', '1');          // 4 + 10 + 1= 15 chars in memory
var_dump($cache->get('A'));     // Will return 1234567890
$cache->put('D', '567');        // 4 + 10 + 1 + 3= 18 chars > 16, an elements must be deleted
// Because A was used more recently then B, A will not be removed.
var_dump($cache->get('B'));     // Will return null
var_dump($cache->get('A'));     // Will return 1234567890 */
