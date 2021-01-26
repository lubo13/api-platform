<?php

/**
 * @package
 * @author  Lubo Grozdanov <grozdanov.lubo@gmail.com>
 */

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Util\DataProvider;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * This normalizer only set data that we need to change Dynamic Relation Type
 * Just see \App\PropertyInfo\Metadata\Property\PropertyMetadataDynamicTypeMetadataFactory
 */
class DataProviderPopulateNormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface, ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private const NORMALIZER_ALREADY_CALLED = 'generic_normalizer_already_called';
    private const DENORMALIZER_ALREADY_CALLED = 'generic_denormalizer_already_called';

    private DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::DENORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $context[self::DENORMALIZER_ALREADY_CALLED] = true;

        $obj = $object;
        if ($object instanceof Paginator && $object->count() > 0) {
            try {
                $obj = $object->getIterator()[0];
            } catch (\Exception) {
            }
        }

        $this->dataProvider->setObject($obj);
        $this->dataProvider->setNormalizeContext($context);

        return $this->normalizer->normalize($object, $format, $context);
    }


    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        if (isset($context[self::DENORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::DENORMALIZER_ALREADY_CALLED] = true;

        $this->dataProvider->setData($data);
        $this->dataProvider->setDenormalizeContext($context);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
