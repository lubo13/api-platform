<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\PropertyInfo\Metadata\Property\PropertyMetadataDynamicTypeMetadataFactory;
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
class GenericNormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface, ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private const NORMALIZER_ALREADY_CALLED = 'generic_normalizer_already_called';
    private const DENORMALIZER_ALREADY_CALLED = 'generic_denormalizer_already_called';

    private PropertyMetadataDynamicTypeMetadataFactory $dynamicTypeMetadataFactory;

    public function __construct(PropertyMetadataDynamicTypeMetadataFactory $dynamicTypeMetadataFactory)
    {
        $this->dynamicTypeMetadataFactory = $dynamicTypeMetadataFactory;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::DENORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return true;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::DENORMALIZER_ALREADY_CALLED] = true;

        $obj = $object;
        if ($object instanceof Paginator && $object->count() > 0) {
            $obj = $object->getIterator()[0];
        }
        $this->dynamicTypeMetadataFactory->setObjectAndContext($obj, $context);

        return $this->normalizer->normalize($object, $format, $context);
    }


    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        if (isset($context[self::DENORMALIZER_ALREADY_CALLED])) {
            return false;
        }

        return true;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $context[self::DENORMALIZER_ALREADY_CALLED] = true;

        $this->dynamicTypeMetadataFactory->setDataAndContext($data, $context);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
