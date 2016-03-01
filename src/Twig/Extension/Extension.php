<?php

namespace Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;  
use \Twig_Extension;

class Extension extends Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    public function getName() 
    {
        return 'my_twig.extensions';
    }

    public function getFilters() {
        return array(
            'json_decode'   => new \Twig_Filter_Method($this, 'jsonDecode'),
        );
    }

    public function jsonDecode($str) {
        return json_decode($str);
    }
}