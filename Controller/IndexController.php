<?php

namespace Sli\ExtJsLocalizationBundle\Controller;

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
     * @param Request $request
     * @param string $locale
     * @return Response
     */
    public function compileAction(Request $request, $locale = null)
    {
        if (!$locale) {
            $locale = $request->getLocale();
        }

        /* @var \Symfony\Component\Translation\TranslatorInterface $translator */
        $translator = $this->get('translator');
        /* @var \Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader $loader */
        $loader = $this->get('translation.loader');
        /* @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = $this->get('kernel');

        $catalogue = new MessageCatalogue($locale);

        $skippedBundles = array();
        /* @var \Symfony\Component\HttpKernel\Bundle\Bundle $bundle */
        foreach ($kernel->getBundles() as $bundle) {
            try {
                $loader->loadMessages($bundle->getPath() . '/Resources/translations', $catalogue);
                $skippedBundle = false;
            } catch (\InvalidArgumentException $e) {
                $skippedBundle = true;
            }

            if ($skippedBundle) {
                $skippedBundles[] = $bundle->getName();
            }
        }

        try {
            $loader->loadMessages($this->getTranslationsDir(), $catalogue);
        } catch (\InvalidArgumentException $e) {}


        $tokenGroups = array();
        foreach ($catalogue->all($this->getDomain()) as $fullToken=>$translation) {
            $className = explode('.', $fullToken);
            $token = array_pop($className);
            $className = implode('.', $className);

            if (!isset($tokenGroups[$className])) {
                $tokenGroups[$className] = array();
            }
            
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
