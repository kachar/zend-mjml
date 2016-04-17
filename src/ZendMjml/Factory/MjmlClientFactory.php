<?php

namespace ZendMjml\Factory;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Cache\CacheSubscriber;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MjmlClientFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return GuzzleHttp\Client
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config')['mjml'];
        $client = new GuzzleClient([
            'base_url' => rtrim($config['mjmlServiceUrl'], '/'),
            'defaults' => [
                'timeout' => $config['timeout'],
                'connect_timeout' => $config['connectTimeout'],
            ],
        ]);

        CacheSubscriber::attach($client);

        return $client;
    }
}
