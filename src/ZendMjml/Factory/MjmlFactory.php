<?php

namespace ZendMjml\Factory;

use ZendMjml\Service\Mjml;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MjmlFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ZendMjml\Service\Mjml
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config')['mjml'];
        $httpClient = $serviceLocator->get('Client\Mjml');
        $renderer = $serviceLocator->get('Zend\View\Renderer\RendererInterface');

        $adapterConfig = $config['transportAdapter'];
        if (is_string($adapterConfig)) {
            $transport = $serviceLocator->get($adapterConfig);
        } elseif (is_array($adapterConfig)) {
            $transport = \Zend\Mail\Transport\Factory::create($adapterConfig);
        } else {
            throw new \Exception('Transport Adapter cannot be found.');
        }

        return new Mjml($httpClient, $transport, $renderer);
    }
}
