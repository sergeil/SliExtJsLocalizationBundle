<?php

namespace Sli\ExtJsLocalizationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Translation\MessageCatalogue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class IndexController extends Controller
{
    protected function getDomain()
    {
        return 'extjs';
    }

    /**
     * @Template
     * @param string $locale
     */
    public function compileAction($locale = null)
    {
        if (!$locale) {
            /* @var \Symfony\Component\HttpFoundation\Request $request */
            $request = $this->getRequest();
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
        foreach ($kernel->getBundles() as $bundle) {
            try {
                /* @var \Symfony\Component\HttpKernel\Bundle\Bundle $bundle */
                $loader->loadMessages($bundle->getPath().'/Resources/translations', $catalogue);
            } catch (\InvalidArgumentException $e) {
                $skippedBundles[] = $bundle->getName();
            }
        }

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

        return array(
            'tokens_total' => count($tokenGroups, true) - count($tokenGroups),
            'locale' => $locale,
            'token_groups' => $tokenGroups,
            'skipped_bundles' => $kernel->getEnvironment() != 'prod' ? $skippedBundles : array()
        );
    }
}
