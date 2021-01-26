<?php

/**
 * @package
 * @author  Lubo Grozdanov <grozdanov.lubo@gmail.com>
 */

declare(strict_types=1);

namespace App\Util;

/**
 * Class DataProvider
 *
 * @package App\Util
 */
class DataProvider
{
    private ?object $object = null;

    private array $normalizeContext = [];

    private array $data = [];

    private array $denormalizeContext = [];

    public function getObject(): ?object
    {
        return $this->object;
    }

    public function setObject(?object $object): void
    {
        $this->object = $object;
    }

    public function getNormalizeContext(): array
    {
        return $this->normalizeContext;
    }

    public function setNormalizeContext(array $normalizeContext): void
    {
        $this->normalizeContext = $normalizeContext;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getDenormalizeContext(): array
    {
        return $this->denormalizeContext;
    }

    public function setDenormalizeContext(array $denormalizeContext): void
    {
        $this->denormalizeContext = $denormalizeContext;
    }
}
