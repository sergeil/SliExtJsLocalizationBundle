<?php

namespace Sli\ExtJsLocalizationBundle\FileProvider;

use Symfony\Component\Finder\Finder;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ExtjsClassesProvider implements FileProviderInterface
{
    private $regex;

    public function __construct()
    {
        $this->regex = implode('', array( // FIXME stupid one
            '@',
            'Ext\.define\(',
                '(\'|")+',
                    '(?P<className>.*)',
                '(\'|")+',
                '.*',
                ',',
            '@'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getFiles($directory)
    {
        $paths = array();

        $finder = new Finder();
        foreach ($finder->files()->name('*.js')->in($directory) as $filepath) {
            if ($this->isValidExtjsClass(file_get_contents($filepath))) {
                $paths[] = $filepath;
            }
        }

        return $paths;
    }

    protected function isValidExtjsClass($source)
    {
        return preg_match($this->regex, $source);
    }
}
