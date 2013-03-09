<?php

namespace Sli\ExtJsLocalizationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Sli\ExtJsLocalizationBundle\Extractor\ExtjsClassesExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TranslationUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sli:update-extjs-translation')
             ->addArgument('locale', InputArgument::REQUIRED)
             ->addArgument('bundle', InputArgument::REQUIRED)
             ->addOption(
                'path-provider', null, InputOption::VALUE_OPTIONAL, 'Service ID of implementation of FileProviderInterface'
            )
             ->addOption(
                'output-format', null, InputOption::VALUE_OPTIONAL, 'Dictionary format that will be used to store found translations', 'xlf'
            )
            ->setDescription(<<<TEXT
Command extracts translations tokens from extjs classes.
TEXT
        );
    }

    protected function resolveResourcesDirectory(Bundle $bundle)
    {
        return $bundle->getPath().'/Resources/public/js';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \Symfony\Component\HttpKernel\Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        /* @var \Symfony\Component\HttpKernel\Bundle\Bundle $bundle */
        $bundle = $kernel->getBundle($input->getArgument('bundle'));
        /* @var \Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader $loader */
        $loader = $this->getContainer()->get('translation.loader');
        /* @var \Symfony\Component\Translation\Writer\TranslationWriter $writer */
        $writer = $this->getContainer()->get('translation.writer');

        $outputFormat = $input->getOption('output-format');
        if (!in_array($outputFormat, $writer->getFormats())) {
            $output->writeln('<error>Wrong output format</error>');
            $output->writeln(sprintf('Supported formats are %s.', implode(', ', $writer->getFormats())));

            return 1;
        }

        $extractor = new ExtjsClassesExtractor(
            $input->getOption('path-provider') ? $this->getContainer()->get($input->getOption('path-provider')) : null
        );

        $catalogue = new MessageCatalogue($input->getArgument('locale'));
        $extractor->extract($this->resolveResourcesDirectory($bundle), $catalogue);

        if (count($catalogue->all()) == 0) {
            $output->writeln('<info>No translation tokens were found.</info>');
            return 1;
        }

        $bundleTransPath = $bundle->getPath().'/Resources/translations';

        $loader->loadMessages($bundleTransPath, $catalogue);
        $writer->writeTranslations($catalogue, $outputFormat, array('path' => $bundleTransPath));

        $output->writeln(sprintf('%s tokens were successfully parsed.', count($catalogue->all())));
    }
}
