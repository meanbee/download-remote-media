<?php namespace Meanbee\DownloadRemoteMedia\Commands;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

class AbstractCommand extends AbstractMagentoCommand
{
    /** @var  InputInterface $_input */
    protected $_input;
    /** @var  OutputInterface $_output */
    protected $_output;

    /**
     * @return DialogHelper
     */
    protected function getDialog()
    {

        /** @var DialogHelper $dialog */
        return $this->getHelper('dialog');
    }
    /**
     * @param InputInterface $input
     * @return $this
     */
    protected function setInput(InputInterface $input)
    {
        $this->_input = $input;
        return $this;
    }

    /**
     * @return InputInterface
     */
    protected function getInput()
    {
        return $this->_input;
    }

    /**
     * @param OutputInterface $output
     * @return $this
     */
    protected function setOutput(OutputInterface $output)
    {
        $this->_output = $output;
        return $this;
    }

    /**
     * @return OutputInterface
     */
    protected function getOutput()
    {
        return $this->_output;
    }
}
