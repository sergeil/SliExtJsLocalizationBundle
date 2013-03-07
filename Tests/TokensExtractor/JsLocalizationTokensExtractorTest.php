<?php

namespace Sli\ExtJsLocalizationBundle\Tests\TokensExtractor;

use Sli\ExtJsLocalizationBundle\TokensExtractor\JsLocalizationTokensExtractor;
use Sli\ExtJsLocalizationBundle\TokensExtractor\BadTokensReporterInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class JsLocalizationTokensExtractorTest extends \PHPUnit_Framework_TestCase
{
    /* @var JsLocalizationTokensExtractor $extractor */
    private $extractor;
    private $reporter;

    public function setUp()
    {
        $this->reporter = $this->getMock(BadTokensReporterInterface::CLAZZ);
        $this->extractor = new JsLocalizationTokensExtractor($this->reporter);
    }

    public function testExtractFromSource()
    {
        $class1 = file_get_contents(__DIR__.'/resources/class1.js');
        $tokens = $this->extractor->extractFromSource($class1);

        $this->assertTrue(is_array($tokens));
        $this->assertEquals(2, count($tokens), 'From class "resources/class1.js" two tokens must have been extracted');
        $this->assertValidTokenDeclaration($tokens, 'Company.foo.bar.MyClass.firstname', 'Firstname');
        $this->assertValidTokenDeclaration($tokens, 'Company.foo.bar.MyClass.lastname', 'Lastname');
    }

    private function assertValidTokenDeclaration(array $tokens, $expectedName, $expectedValue, $expectedDescription = null)
    {
        $this->assertArrayHasKey($expectedName, $tokens, "Unable to find token '$expectedName'");

        $token = $tokens[$expectedName];

        $this->assertArrayHasKey('token', $token);
        $this->assertArrayHasKey('value', $token);
    }
}
