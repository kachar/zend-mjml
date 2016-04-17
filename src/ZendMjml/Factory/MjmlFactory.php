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

        $transport = \Zend\Mail\Transport\Factory::create($config['transportAdapter']);
        $renderer = $serviceLocator->get('Zend\View\Renderer\RendererInterface');

        return new Mjml($httpClient, $transport, $renderer);
    }
}
