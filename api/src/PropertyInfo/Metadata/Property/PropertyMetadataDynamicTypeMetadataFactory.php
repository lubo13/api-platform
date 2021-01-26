<?php

/**
 * @package
 * @author  Lubo Grozdanov <grozdanov.lubo@gmail.com>
 */

declare(strict_types=1);

namespace App\PropertyInfo\Metadata\Property;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use App\Entity\DynamicRelationInterface;
use App\Util\DataProvider;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * Class PropertyMetadataDynamicTypeMetadataFactory
 *
 * @package App\PropertyInfo\Metadata\Property
 */
final class PropertyMetadataDynamicTypeMetadataFactory implements PropertyMetadataFactoryInterface
{
    private PropertyMetadataFactoryInterface $decorated;

    private RouterInterface $router;

    private ResourceMetadataFactoryInterface $resourceMetadataFactory;

    private DataProvider $dataProvider;

    public function __construct(PropertyMetadataFactoryInterface $decorated, RouterInterface $router, ResourceMetadataFactoryInterface $resourceMetadataFactory, DataProvider $dataProvider)
    {
        $this->decorated = $decorated;
        $this->router = $router;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->dataProvider = $dataProvider;
    }

    public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);

        return $this->updatePropertyMetadataType($propertyMetadata, $property, $resourceClass);
    }

    private function updatePropertyMetadataType(PropertyMetadata $propertyMetadata, string $name, string $resourceClass): PropertyMetadata
    {
        /** @var \Symfony\Component\PropertyInfo\Type */
        $type = $propertyMetadata->getType();

        if ($type !== null && $type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT) {
            $reflectionClass = new \ReflectionClass($type->getClassName());

            $operation = $this->dataProvider->getNormalizeContext()['collection_operation_name'] ?? $this->dataProvider->getNormalizeContext()['item_operation_name'] ?? $this->dataProvider->getDenormalizeContext(
                )['collection_operation_name'] ?? $this->dataProvider->getDenormalizeContext()['item_operation_name'] ?? null;

            if (
                $reflectionClass->implementsInterface(DynamicRelationInterface::class)
                && $reflectionClass->isAbstract() === true
                && $reflectionClass->isInterface() === false
            ) {
                /* documentation | deserialization | serialization */
                if ($operation === null) {
                    $propertyMetadata = $this->onDocumentation($propertyMetadata);
                } elseif (strtolower($operation) !== 'get') {
                    $propertyMetadata = $this->onDeserialization($name, $propertyMetadata, $resourceClass);
                } elseif (strtolower($operation) === 'get') {
                    $propertyMetadata = $this->onSerialization($name, $propertyMetadata, $resourceClass);
                }
            }
        }

        return $propertyMetadata;
    }

    /**
     * When the API is called with GET HTTP Method (for Collection or single Item) we want somehow to dynamic to change Type of relation for properly serialization
     * The idea to change relation from abstract to concrete relay on property's value type
     */
    private function onSerialization(string $name, PropertyMetadata $propertyMetadata, string $resourceClass): PropertyMetadata
    {
        /** @var \Symfony\Component\PropertyInfo\Type */
        $type = $propertyMetadata->getType();
        $object = $this->dataProvider->getObject();

        if ($object && method_exists($object, 'get'.$name)) {
            $concreteRelation = $object->{'get'.$name}();
            if ($concreteRelation !== null) {
                $concreteClass = get_class($concreteRelation);
                $concreteType = new Type(Type::BUILTIN_TYPE_OBJECT, $type ? $type->isNullable() : false, $concreteClass);
                $propertyMetadata = $propertyMetadata->withType($concreteType);
                try {
                    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
                    $attributes = $resourceMetadata->getAttributes();
                    if (isset($attributes[$name]['readableLink'])) {
                        $propertyMetadata = $propertyMetadata->withReadableLink($attributes[$name]['readableLink']);
                    }
                } catch (ResourceClassNotFoundException) {
                    // just skip
                }
            }
        }

        return $propertyMetadata;
    }

    /**
     * When the API is called with other than GET HTTP Method (for Collection or single Item) we want somehow to dynamic to change Type of relation for properly deserialization
     *
     * ON PUT: First checking if there is already set relation an if there is we use this concrete type
     *
     * ON POST: Let assume we have relation to AbstractRelation, but there we can understand from what concrete type is AbstractRelation from the IRI that is passed from customer
     * Or with other words we change relation from abstract to concrete relay on property's IRI that comes from outside
     *
     */
    private function onDeserialization(string $name, PropertyMetadata $propertyMetadata, string $resourceClass): PropertyMetadata
    {
        /** @var \Symfony\Component\PropertyInfo\Type */
        $type = $propertyMetadata->getType();

        // On PUT HTTP Request we relay on already set relation object first
        $objectToPopulate = $this->dataProvider->getDenormalizeContext()[AbstractObjectNormalizer::OBJECT_TO_POPULATE] ?? null;

        if ($objectToPopulate !== null) {
            if (method_exists($objectToPopulate, 'get'.$name)) {
                $concreteRelation = $objectToPopulate->{'get'.$name}();
                if ($concreteRelation !== null) {
                    $concreteClass = get_class($concreteRelation);

                    $concreteType = new Type(Type::BUILTIN_TYPE_OBJECT, $type ? $type->isNullable() : false, $concreteClass);
                    $propertyMetadata = $propertyMetadata->withType($concreteType);
                    try {
                        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
                        $attributes = $resourceMetadata->getAttributes();
                        if (isset($attributes[$name]['readableLink'])) {
                            $propertyMetadata = $propertyMetadata->withReadableLink($attributes[$name]['readableLink']);
                        }
                    } catch (ResourceClassNotFoundException) {
                        // just skip
                    }
                }
            }

            return $propertyMetadata;
        }

        // On POST HTTP Request we understand how to change property type from passed iri
        if (isset($this->dataProvider->getData()[$name]) === true) {
            $concreteClass = null;
            try {
                $context = $this->router->getContext();
                $context->setMethod('GET');
                $routeCollection = $this->router->getRouteCollection();
                $matcher = new TraceableUrlMatcher($routeCollection, $context);
                /** @var \Symfony\Component\Routing\Route */
                $apiRouteInfo = $matcher->match($this->dataProvider->getData()[$name]);
                $concreteClass = $apiRouteInfo['_api_resource_class'] ?? null;

                if ($concreteClass !== null && is_subclass_of($concreteClass, $type->getClassName())) {
                    $concreteType = new Type(Type::BUILTIN_TYPE_OBJECT, $type->isNullable(), $concreteClass);
                    $propertyMetadata = $propertyMetadata->withType($concreteType);
                    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
                    $attributes = $resourceMetadata->getAttributes();
                    if (isset($attributes[$name]['readableLink'])) {
                        $propertyMetadata = $propertyMetadata->withReadableLink($attributes[$name]['readableLink']);
                    }
                }
            } catch (LoaderLoadException | ResourceClassNotFoundException) {
                // just skip
            } catch (\Exception) {
                throw new UnexpectedValueException(sprintf('Invalid IRI "%s".', $this->dataProvider->getData()[$name]));
            }
        }

        return $propertyMetadata;
    }

    /**
     * Here the idea is somehow on documentation to change type from { } to be showed like "string <iri-reference>"
     */
    private function onDocumentation(PropertyMetadata $propertyMetadata): PropertyMetadata
    {
        $concreteType = new Type(Type::BUILTIN_TYPE_STRING);
        $propertyMetadata = $propertyMetadata->withType($concreteType)->withSchema(['type' => 'string', 'format' => 'iri-reference',]);

        return $propertyMetadata;
    }
}
