<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/03/16
 * Time: 13:42
 */
class DatabaseCacheTest extends \GLFramework\Tests\TestCase
{

    function testCache()
    {
        $this->requireDatabase();
        $cache = $this->getDatabase()->getCache();
        $this->assertInstanceOf('GLFramework\Cache\MemoryCache', $cache);
        $key = "test.key";
        $this->assertFalse($cache->hash($key));
        $cache->set($key, "value");
        $this->assertTrue($cache->hash($key));
        $this->assertEquals("value", $cache->get($key));
        $cache->remove($key);
        $this->assertNull($cache->get($key));
        $this->assertFalse($cache->hash($key));
    }
}
