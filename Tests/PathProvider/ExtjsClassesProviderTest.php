<?php

namespace Sli\ExtJsLocalizationBundle\Tests\PathProvider;

use Sli\ExtJsLocalizationBundle\FileProvider\ExtjsClassesProvider;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ExtjsClassesProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFiles()
    {
        $provider = new ExtjsClassesProvider();
        $result = $provider->getFiles(__DIR__.'/resources');
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
    }
}
