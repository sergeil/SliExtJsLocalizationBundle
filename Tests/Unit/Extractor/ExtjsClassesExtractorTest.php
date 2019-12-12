<?php

namespace Sli\ExtJsLocalizationBundle\Tests\Unit\Extractor;

use Sli\ExtJsLocalizationBundle\Extractor\ExtjsClassesExtractor;
use Sli\ExtJsLocalizationBundle\FileProvider\FileProviderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Sli\ExtJsLocalizationBundle\FileProvider\ExtjsClassesProvider;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ExtjsClassesExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $provider = $this->createMock('Sli\ExtJsLocalizationBundle\FileProvider\FileProviderInterface');
        $provider->expects($this->any())
                 ->method('getFiles')
                 ->will($this->returnValue(array(__DIR__.'/resources/class1.js')));

        $catalogue = new MessageCatalogue('en');

        $extractor = new ExtjsClassesExtractor($provider);
        $extractor->setPrefix('**');
        $extractor->extract(__DIR__.'/resources', $catalogue);

        $tokens = $catalogue->all('extjs');
        $this->assertTrue(is_array($tokens));
        $this->assertEquals(3, count($tokens));
        $this->assetToken('Company.foo.bar.MyClass.firstname', '**Firstname', $tokens);
        $this->assetToken('Company.foo.bar.MyClass.lastname', '**Lastname', $tokens);
        $this->assetToken('Company.foo.bar.MyClass.options', '**Options:', $tokens);
    }

    public function test__construct()
    {
        $extractor = new ExtjsClassesExtractor();
        $this->assertInstanceOf(ExtjsClassesProvider::clazz(), $extractor->getPathProvider());
    }

    private function assetToken($name, $value, array $tokens)
    {
        $this->assertArrayHasKey($name, $tokens);
        $this->assertEquals($value, $tokens[$name]);
    }
}
