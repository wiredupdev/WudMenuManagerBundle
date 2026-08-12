<?php

namespace Wiredupdev\MenuManagerBundle\Menu;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class CachedProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function process(MenuItemInterface $menuItem): void
    {
        $itemKey = \sprintf('#menu_%s', $menuItem->getId());

        $menuItem = $this->cache->get($itemKey, function (ItemInterface $item) use ($menuItem): MenuItemInterface {
            /**
             * @var ProcessorInterface $process
             */
            foreach ($this->processor as $process) {
                if ($process instanceof Cacheable) {
                    $process->process($menuItem);
                    $this->removeProcess($process::class);
                }
            }

            return $menuItem;
        });

        $this->processor->process($menuItem);
    }

    public function getIterator(): \Traversable
    {
        return $this->processor->getIterator();
    }

    public function addProcess(ProcessInterface $process): void
    {
        $this->processor->addProcess($process);
    }

    public function removeProcess(string $class): void
    {
        $this->processor->removeProcess($class);
    }

    public function hasProcess(string $class): bool
    {
        return $this->processor->hasProcess($class);
    }
}
