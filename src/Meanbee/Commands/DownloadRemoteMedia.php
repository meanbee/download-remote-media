<?php

namespace Meanbee\Commands;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\DialogHelper;


class DownloadRemoteMedia extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('media:fetch:products')
            ->addOption('remove-url', null, InputOption::VALUE_REQUIRED, 'The URL images should be fetched from')
            ->addOption('skus', null, InputOption::VALUE_OPTIONAL, 'CSV of SKUs to fetch images for')
            ->addOption('show-skipped', false, InputOption::VALUE_OPTIONAL, 'Hide/show messages that can skipped (defaults to hidden, useful for debugging)')
            ->setDescription('Test transactional emails easily.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_input = $input;
        $this->_output = $output;
    }

}