<?php

namespace Sli\ExtJsLocalizationBundle\Controller;

use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class IndexController extends Controller
{
    /**
     * @return string
     */
    protected function getDomain()
    {
        return 'extjs';
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'SliExtJsLocalizationBundle:Index:compile.html.twig';
    }

    /**
     * @return string
     */
    protected function getTranslationsDir()
    {
        return $this->get('kernel')->getRootdir() . '/Resources/translations';
    }

    /**
     * @param $directory
     * @param MessageCatalogue $catalogue
     */
    protected function loadMessages($directory, MessageCatalogue $catalogue)
    {
        $isSf4 = $this->getContainer()->has('translation.reader');
        if ($isSf4) {
            /* @var \Symfony\Component\Translation\Reader\TranslationReaderInterface $reader */
            $reader = $this->get('translation.reader');
            $reader->read($directory, $catalogue);
        } else {
            /* @var \Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader $loader */
            $loader = $this->get('translation.loader');
            $loader->loadMessages($directory, $catalogue);
        }
    }

    /**
     * @param Request $request
     * @param string $locale
     * @return Response
     */
    public function compileAction(Request $request, $locale = null)
    {
        if (!$locale) {
            $locale = $request->getLocale();
        }

        /* @var \Symfony\Component\Translation\TranslatorBagInterface $translator */
        $translator = $this->get('translator');

        /* @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = $this->get('kernel');

        $catalogue = new MessageCatalogue($locale);

        $skippedBundles = array();
        /* @var \Symfony\Component\HttpKernel\Bundle\Bundle $bundle */
        foreach ($kernel->getBundles() as $bundle) {
            try {
                $this->loadMessages($bundle->getPath() . '/Resources/translations', $catalogue);
                $skippedBundle = false;
            } catch (\InvalidArgumentException $e) {
                $skippedBundle = true;
            }

            if ($skippedBundle) {
                $skippedBundles[] = $bundle->getName();
            }
        }

        try {
            $this->loadMessages($this->getTranslationsDir(), $catalogue);
        } catch (\InvalidArgumentException $e) {}

        $mergeOperation = new MergeOperation($translator->getCatalogue($locale), $catalogue);
        $catalogue = $mergeOperation->getResult();

        $tokenGroups = array();
        foreach ($catalogue->all($this->getDomain()) as $fullToken => $translation) {
            $className = explode('.', $fullToken);
            $token = array_pop($className);
            $className = implode('.', $className);

            if (!isset($tokenGroups[$className])) {
                $tokenGroups[$className] = array();
            }

            /* @var \Symfony\Component\Translation\TranslatorInterface $translator */
            $tokenGroups[$className][$token] = $translator->trans($fullToken, array(), $this->getDomain(), $locale);
        }

        $body = $this->renderView($this->getTemplate(), array(
            'tokens_total' => count($tokenGroups, true) - count($tokenGroups),
            'locale' => $locale,
            'token_groups' => $tokenGroups,
            'skipped_bundles' => $kernel->getEnvironment() != 'prod' ? $skippedBundles : array()
        ));

        return new Response($body, 200, array('Content-Type' => 'application/javascript; charset=UTF-8'));
    }
}
