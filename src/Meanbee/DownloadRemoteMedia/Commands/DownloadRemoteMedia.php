<?php namespace Meanbee\DownloadRemoteMedia\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\DialogHelper;


class DownloadRemoteMedia extends AbstractCommand
{
    /** @var  $current_product \Mage_Catalog_Model_Product */
    protected $current_product;
    protected $image_attributes = array('image');

    protected function configure()
    {
        $this
            ->setName('media:fetch:products')
            ->addOption('remote-url', null, InputOption::VALUE_REQUIRED, 'The URL images should be fetched from')
            ->addOption('skus', null, InputOption::VALUE_OPTIONAL, 'CSV of SKUs to fetch images for')
	    ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of SKUs to fetch images for')
            ->addOption('show-skipped', null, InputOption::VALUE_OPTIONAL, 'Hide/show messages that can skipped (defaults to hidden, useful for debugging)')
            ->addOption('image-attributes', null, InputOption::VALUE_OPTIONAL, 'CSV of Image attributes you would like to download, defaults to just the base image.')
            ->addOption('no-overwrite', null, InputOption::VALUE_OPTIONAL, 'Images are overwritten by default. Use this option to disable', false)
            ->setDescription('Fetch product images from a remote store');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setInput($input);
        $this->setOutput($output);

        $this->detectMagento($this->getOutput());
        if ($this->initMagento()) {
            $remote_url = $this->validateUrl($this->getInput()->getOption('remote-url'));
            $this->prepareImageAttributes();
            $this->fetchImagesFromRemote($remote_url);
        }
    }

    public function fetchImagesFromRemote($remote_url)
    {
        $collection = $this->prepareCollection();

        /** @var \Mage_Catalog_Model_Product $product */
        foreach ($collection as $product) {
            $this->current_product = $product;
            foreach ($this->getImageAttributes() as $image) {
                $this->downloadImages($product, $image, $product->getMediaConfig(), $remote_url);
            }
        }
    }

    /**
     * @return \Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function prepareCollection()
    {
        /** @var \Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = \Mage::getModel('catalog/product')->getCollection();

        $collection->addAttributeToSelect($this->getImageAttributes());

        $skus = $this->getSkus();
        if (!empty($skus)) {
            $collection->addAttributeToFilter('sku', array('in' => $skus));
        }
	$limit = $this->getLimit();
        if (!empty($limit)) {
	    $collection->addAttributeToSort('entity_id', 'DESC');
            $collection->getSelect()->limit($limit);
        }
	$collection->load();
        return $collection;
    }

    protected function prepareImageAttributes()
    {
        if ($this->getInput()->getOption('image-attributes')) {
            $this->image_attributes = explode(',', $this->getInput()->getOption('image-attributes'));
        }
    }

    protected function getImageAttributes()
    {
        return $this->image_attributes;
    }

    /**
     *
     * @param \Mage_Catalog_Model_Product $product
     * @param string $image_attribute
     * @param \Mage_Catalog_Model_Product_Media_Config $media_config
     * @param $remote_url
     */
    protected function downloadImages($product, $image_attribute, $media_config, $remote_url)
    {
        $image = $product->getData($image_attribute);
        $base_path = $media_config->getBaseMediaPath();
        $local_file = $base_path . $image;


        // Don't attempt to download an image that already exists.
        if (file_exists($local_file) && $this->getInput()->getOption('no-overwrite')) {
            $this->log("File exists... skipping", true);
            return;
        }

        $this->log(sprintf('Attempting image: %s, %s', $image_attribute, $image));

        $download_url = $remote_url . 'media' . DS . $media_config->getBaseMediaUrlAddition() . $image;
        // Fetch the contents of the remote image.
        if (!($remote = file_get_contents($download_url))) {
            $this->log("Failed to download file... skipping", false, true);
            return;
        }

        $local_file_dir = dirname($local_file);
        $directory_exists =  $this->createDirectories($local_file_dir);
        if (!$directory_exists) {
            return;
        }

        file_put_contents($local_file, $remote);

        if (!file_exists($local_file)) {
            $this->log(sprintf("File failed to save: %s", $local_file));
            return;
        }
        $this->log("File downloaded successfully");
    }

    /**
     * Attempt to make directories when they don't exist.
     *
     * @param $path
     * @return bool
     */
    protected function createDirectories($path)
    {
        if (is_dir($path) && !is_writable($path)) {
            $this->log("Directory exists, but isn't writeable");
            return false;
        }

        if (is_dir($path)) {
            return true;
        }

        if (!mkdir($path, 0777, true)) {
            $this->log("Failed to create directories: " . $path);
            return false;
        }

        return true;
    }

    /**
     * Validate a URL.
     *
     * @param $url
     * @return string
     * @throws \Exception
     */
    protected function validateUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \Exception('Invalid URL');
        }

        // Ensure URL has a trailing slash.
        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        return $url;
    }

    protected function getSkus()
    {
        if (($skus = $this->getInput()->getOption('skus')) !== null) {
            return explode(',', $skus);
        }
        return array();
    }

    /**
     * Set limit of SKU collection.
     *
     * @return int
     */
    protected function getLimit()
    {
        if (($limit = $this->getInput()->getOption('limit')) !== null) {
            return (int)$limit;
        }
        return 0;
    }

    /**
     * Output a message to the console.
     *
     * @param $message
     * @param bool $can_skip
     */
    protected function log($message, $can_skip = false)
    {
        if ($can_skip && !$this->showSkipped()) {
            return;
        }

        $this->getOutput()->write($message, true);
    }

    /**
     * Ability to show/hide messages that can be skipped.
     *
     * @return bool
     */
    protected function showSkipped()
    {
        return (bool) $this->getInput()->getOption('show-skipped');
    }

}
