<?php

namespace Sli\ExtJsLocalizationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Yaml\Yaml;
use Sli\ExtJsLocalizationBundle\SliExtJsLocalizationBundle;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class RouterResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $file = $container->getParameter('kernel.cache_dir').'/sliextjsl10n/routing.yml';

        if (!is_dir($dir = dirname($file))) {
            mkdir($dir, 0777, true);
        }

        $resourcePath = $container->getParameterBag()->resolveValue($container->getParameter('router.resource'));
        $resource = array_merge(Yaml::parse(file_get_contents($resourcePath)), array(
            'SliExtJsLocalizationBundle' => array(
                'pattern' => $container->getParameter('sli_ext_js_localization.route'),
                'defaults' => array(
                    '_controller' => 'SliExtJsLocalizationBundle:Index:compile'
                )
            ),
            '_app'  => array('resource' => $container->getParameter('router.resource')),
        ));

        file_put_contents($file, Yaml::dump($resource));
        $container->setParameter('router.resource', $file);
    }
}
