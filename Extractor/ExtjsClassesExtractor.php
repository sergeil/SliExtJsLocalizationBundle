<?php

namespace Sli\ExtJsLocalizationBundle\Extractor;

use Sli\ExtJsLocalizationBundle\FileProvider\FileProviderInterface;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ExtjsClassesExtractor implements ExtractorInterface
{
    private $prefix;
    private $pathProvider;

    public function __construct(FileProviderInterface $pathProvider)
    {
        $this->pathProvider = $pathProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        foreach ($this->pathProvider->getFiles($directory) as $filename) {
            $this->extractTokens($filename, $catalogue);
        }
    }

    private function extractTokens($filename, $catalogue)
    {

    }
}
