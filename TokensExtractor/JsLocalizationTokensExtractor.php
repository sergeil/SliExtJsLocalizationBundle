<?php

namespace Sli\ExtJsLocalizationBundle\TokensExtractor;

use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class JsLocalizationTokensExtractor 
{
    private $reporter;

    public function __construct(BadTokensReporterInterface $reporter)
    {
        $this->reporter = $reporter;
    }

    public function extractAll()
    {

    }

    public function extractFromSource($sourceCode, $resourceIdentifier = null)
    {
        $isClassName = implode('', array(
            '@',
            'Ext\.define\(',
            '(\'|")+',
            '(?P<className>.*)', // class name
            '(\'|")+',
            '@'
        ));
        $isStartMarker = '@.*//\s*l10n.*@';

        $className = null;
        $tokensStartPosition = null;
        $tokensEndPosition = null;

        $lines = explode("\n", $sourceCode);
        foreach ($lines as $i=>$line) {
            $matches = array();

            if (preg_match($isClassName, $line, $matches)) {
                $className = $matches['className'];
                continue;
            }

            if (preg_match($isStartMarker, $line)) {
                $tokensStartPosition = $i+1; // array index starts from 0 and we need a next line after the  comment
                continue;
            }

            if (trim($line) == '' && null === $tokensEndPosition && null !== $tokensStartPosition) {
                $tokensEndPosition = $i;
            }
        }

        if (null === $tokensStartPosition || null === $tokensEndPosition || null === $className) {
            return array();
        }

        $tokenLines = array_slice($lines, $tokensStartPosition, $tokensEndPosition-$tokensStartPosition);

        $tokens = array();
        foreach ($tokenLines as $line) {
            $exp = explode(':', $line);

            $token = trim($exp[0]);
            $value = trim($exp[1]); // getting rid of white spaces

            // getting rid of coma
            if ($value{strlen($value)-1} == ',') {
                $value = substr($value, 0, strlen($value)-1);
            }

            // getting rid of wrapping " '
            if ($this->isStringWrappedBy($value, "'")) {
                $value = trim($value, "'");
            } else if ($this->isStringWrappedBy($value, '"')) {
                $value = trim($value, '"');
            } else {
                continue;
            }

            if (strlen($token) < 4) {
                throw new \RuntimeException();
            } else if (substr($token, -4, 4) !== 'Text') {
                throw new \RuntimeException();
            }
            $token = substr($token, 0, -4); // removing "Text" suffix
            $token = $className.'.'.$token;

            $tokens[$token] = array(
                'token' => $token,
                'value' => $value
            );
        }

        return $tokens;
    }

    private function isStringWrappedBy($string, $wrap)
    {
        return $string{0} == $wrap && $string{strlen($string)-1} == $wrap;
    }
}
