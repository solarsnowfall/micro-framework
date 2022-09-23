<?php

namespace Solar\MicroFramework\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use DateInterval;

/**
 *
 */
abstract class CacheClientTest extends TestCase
{
    protected CacheInterface $adapter;

    protected string $extension;

    abstract protected function checkEnvironment(): void;

    abstract protected function getAdapter(): CacheInterface;

    protected function setUp() : void
    {
        parent::setUp();

        $this->checkEnvironment();

        $this->adapter = $this->getAdapter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->adapter->clear();
    }

    public function testSet(): void
    {
        $result = $this->adapter->set('test_key', 'test_value');

        $this->assertTrue($result, 'set() must return true upon success');
        $this->assertEquals('test_value', $this->adapter->get('test_key'));
    }

    public function testSetWithIntegerTtl()
    {
        $result = $this->adapter->set('test_key', 'test_value', 1);
        $this->assertTrue($result, 'set() must return true upon success');
        $this->assertEquals('test_value', $this->adapter->get('test_key'));

        sleep(2);

        $this->assertFalse($this->adapter->get('test_key'), 'Value must expire after ttl elapse');
    }

    public function testSetWithDateIntervalTtl()
    {
        $result = $this->adapter->set('test_key', 'test_value', new DateInterval('PT1S'));
        $this->assertTrue($result, 'set() must return true upon success');
        $this->assertEquals('test_value', $this->adapter->get('test_key'));

        sleep(2);

        $this->assertFalse($this->adapter->get('test_key'), 'Value must expire after ttl elapse');
    }

    public function testSetWithExpiredTtl()
    {
        $this->adapter->set('test_key1', 'test_value');
        $this->adapter->set('test_key1', 'test_value', 0);
        $this->assertNull($this->adapter->get('test_key1'), 'set() with 0 ttl must expire value');
        $this->assertFalse($this->adapter->has('test_key1'), 'set() with 0 ttl must fail has() check');

        $this->adapter->set('test_key2', 'test_value', -1);
        $this->assertNull($this->adapter->get('test_key2'), 'set() with negative ttl must expire value');
        $this->assertFalse($this->adapter->has('test_key2'), 'set() with negative ttl fail has() check');
    }

    public function testGet(): void
    {
        $this->assertNull($this->adapter->get('test_key'), 'get() with unset key must return null');
        $this->assertEquals(
            'default',
            $this->adapter->get('test_key', 'default'),
            'get() must return default on miss'
        );

        $this->adapter->set('test_key', 'test_value');
        $this->assertEquals(
            'test_value',
            $this->adapter->get('test_key', 'default'),
            'get() must return value when found, not default'
        );
    }
}