<?php

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider;

class DataProviderDecorator extends DataProvider
{
    private $cache;
    private $logger;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param CacheItemPoolInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct($host, $user, $password, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        parent::__construct($host, $user, $password);
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $input)
    {
        $cacheKey = json_encode($input);

        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        try {
            $result = parent::get($input);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }

        $cacheItem->set($result)->expiresAt(new DateTime('+1 day'));

        return $result;
    }
}
