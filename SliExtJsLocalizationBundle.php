<?php

namespace Sli\ExtJsLocalizationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sli\ExtJsLocalizationBundle\DependencyInjection\Compiler\RouterResourcePass;

class SliExtJsLocalizationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RouterResourcePass());
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
