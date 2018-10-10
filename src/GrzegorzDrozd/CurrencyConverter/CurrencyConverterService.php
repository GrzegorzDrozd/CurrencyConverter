<?php
namespace GrzegorzDrozd\CurrencyConverter;

use Swap\Builder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Zend\Log\Logger;

/**
 * Wrapper around currency converter.
 *
 *
 * @package GrzegorzDrozd\CurrencyConverter
 */
class CurrencyConverterService {

    /**
     * @var int
     */
    protected $cacheTtl = 6000;

    /**
     * @var \Swap\Swap
     */
    protected $converter;

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var Logger
     */
    protected $logging;

    /**
     * CurrencyConverterService constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger) {
        $this->setTempDir(sys_get_temp_dir());
        $this->logging = $logger;
    }

    /**
     * @param string $from
     * @param string $to
     * @return string
     */
    public function convert(string $from, string $to): ?string {
        try {
            $swap = $this->getConverter();
        } catch (\Exchanger\Exception\Exception $e) {
            $this->logging->err($e->getMessage());
            return null;
        }

        try {
            $rate = $swap->latest(sprintf('%s/%s', $from, $to));
            return $rate->getValue();
        } catch (\Exchanger\Exception\Exception  $e){
            $this->logging->err($e->getMessage());
            return null;
        }
    }

    /**
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
            $this->logging->err($e->getMessage());
            throw new \RuntimeException();
        }

        // build currency converter
        try {
            $this->converter = (new Builder(['cache_ttl' => $this->getCacheTtl()]))
                ->useCacheItemPool($cachePool)
                ->add('forge', ['api_key' => 'NpQYwk5r38oRWweqlsBHTyVyUsknJr4c'])
                ->build();
        } catch (\Exception $e) {
            $this->logging->err($e->getMessage());
            throw new \RuntimeException();
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
}
