<?php
namespace GrzegorzDrozd\CurrencyConverter;

use Interop\Container\ContainerInterface;
use Swap\Builder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Zend\Log\Logger;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\ModuleManagerInterface;

/**
 * Wrapper around currency converter.
 *
 *
 * @package GrzegorzDrozd\CurrencyConverter
 */
class CurrencyConverterService {

    /**
     * For how long store rate in cache
     *
     * @var int
     */
    protected $cacheTtl = 600;

    /**
     * Converter object
     *
     * @var \Swap\Swap
     */
    protected $converter;

    /**
     * Cache dir location
     *
     * @var string
     */
    protected $tempDir;

    /**
     * @var Logger
     */
    protected $logging;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * CurrencyConverterService constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger) {
        $this->setTempDir(sys_get_temp_dir());
        $this->logging = $logger;
    }

    /**
     * Do currency conversion.
     *
     * @param string $from
     * @param string $to
     * @param int $amount
     * @return string
     */
    public function convert(string $from, string $to, $amount = 1): ?string {
        try {
            $swap = $this->getConverter();
        } catch (\Exchanger\Exception\Exception $e) {
            $this->getLoggin()->err($e->getMessage());
            return null;
        }

        try {
            $rate = $swap->latest(sprintf('%s/%s', $from, $to));
            return $rate->getValue()*$amount;
        } catch (\Exchanger\Exception\Exception  $e){
            $this->getLoggin()->err($e->getMessage());
            return null;
        }
    }

    /**
     * Setup converter
     *
     * @return \Swap\Swap
     */
    protected function getConverter(): \Swap\Swap {
        if (null !== $this->converter){
            return $this->converter;
        }

        // get cache layer
        try {
            $filesystemAdapter = new Local($this->getTempDir());
            $filesystem        = new Filesystem($filesystemAdapter);
            $cachePool         = new FilesystemCachePool($filesystem);
        } catch (\Exception $e) {
            $this->getLoggin()->err($e->getMessage());
            throw new \RuntimeException('Unable to setup converter', null, $e);
        }

        // build currency converter
        try {
            $this->converter = (new Builder(['cache_ttl' => $this->getCacheTtl()]))
                ->useCacheItemPool($cachePool)
                ->add('forge', ['api_key' =>  $this->apiKey])
                ->build();
        } catch (\Exception $e) {
            $this->getLoggin()->err($e->getMessage());
            throw new \RuntimeException('Unable to create converter', null, $e);
        }
        
        return $this->converter;
    }

    /**
     * @return mixed
     */
    public function getCacheTtl() {
        return $this->cacheTtl;
    }

    /**
     * @param int $cacheTtl
     * @return CurrencyConverterService
     */
    public function setCacheTtl(int $cacheTtl): CurrencyConverterService {
        $this->cacheTtl = $cacheTtl;
        return $this;
    }

    /**
     * @return string
     */
    public function getTempDir(): string {
        return $this->tempDir;
    }

    /**
     * @param string $tempDir
     * @return CurrencyConverterService
     */
    public function setTempDir(string $tempDir): CurrencyConverterService {
        // changing cache directory we need to change cache layer.
        // Easiest way is to set it to null and re-crete object
        $this->converter = null;
        $this->tempDir = $tempDir;

        return $this;
    }

    /**
     * @return Logger
     */
    public function getLogging(): Logger {
        return $this->logging;
    }

    /**
     * @param Logger $logging
     */
    public function setLogging(Logger $logging): void {
        $this->logging = $logging;
    }

    /**
     * @return string
     */
    public function getApiKey(): string {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Please set api key');
        }
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void {
        $this->apiKey = $apiKey;
    }
}
