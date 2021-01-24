<?php

declare(strict_types=1);

namespace App\PropertyInfo\Metadata\Property;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Routing\Matcher\TraceableUrlMatcher;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

final class PropertyMetadataDynamicTypeMetadataFactory implements PropertyMetadataFactoryInterface
{
    private PropertyMetadataFactoryInterface $decorated;

    private RouterInterface $router;

    private ResourceMetadataFactoryInterface $resourceMetadataFactory;

    private $object;

    private array $normalizeContext = [];

    private array $data = [];

    private array $denormalizeContext = [];

    public function __construct(PropertyMetadataFactoryInterface $decorated, RouterInterface $router, ResourceMetadataFactoryInterface $resourceMetadataFactory)
    {
        $this->decorated = $decorated;
        $this->router = $router;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    public function setObjectAndContext($object, array $context): void
    {
        $this->object = $object;
        $this->normalizeContext = $context;
    }

    public function setDataAndContext(array $data, array $context): void
    {
        $this->data = $data;
        $this->denormalizeContext = $context;
    }

    public function create(string $resourceClass, string $name, array $options = []): PropertyMetadata
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $name, $options);


        return $this->updatePropertyMetadataType($propertyMetadata, $name, $resourceClass);
    }

    private function updatePropertyMetadataType(PropertyMetadata $propertyMetadata, $name, string $resourceClass): PropertyMetadata
    {
        /** @var \Symfony\Component\PropertyInfo\Type */
        $type = $propertyMetadata->getType();

        if ($type !== null && $type->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT) {
            $reflectionClass = new \ReflectionClass($type->getClassName());

            $operation = $this->normalizeContext['collection_operation_name'] ?? $this->normalizeContext['item_operation_name'] ?? $this->denormalizeContext['collection_operation_name'] ?? $this->denormalizeContext['item_operation_name'] ?? null;

            if (
                $reflectionClass->isAbstract() === true
                && $reflectionClass->isInterface() === false
            ) {
                //documentation // deserialization //serialization
                if ($operation === null) {
                    $propertyMetadata = $this->onDocumentation($name, $propertyMetadata, $resourceClass);
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
     * Let assume in RequestForProposal we have relation to AbstractStandardWorkflow, but there we can understand from what concrete type is AbstractStandardWorkflow from the workflow's property value
     * Or with other words we change relation from abstract to concrete relay on property's value type
     */
    private function onSerialization(string $name, PropertyMetadata $propertyMetadata, string $resourceClass): PropertyMetadata
    {
        /** @var \Symfony\Component\PropertyInfo\Type */
        $type = $propertyMetadata->getType();

        if (method_exists($this->object, 'get'.$name)) {
            $concreteRelation = $this->object->{'get'.$name}();
            if ($concreteRelation !== null) {
                $concreteClass = get_class($concreteRelation);
                $concreteType = new Type(Type::BUILTIN_TYPE_OBJECT, $type->isNullable(), $concreteClass);
                $propertyMetadata = $propertyMetadata->withType($concreteType);
                try {
                    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
                    $attributes = $resourceMetadata->getAttributes();
                    if (isset($attributes[$name]['readableLink'])) {
                        $propertyMetadata = $propertyMetadata->withReadableLink($attributes[$name]['readableLink']);
                    }
                } catch (ResourceClassNotFoundException $exception) {
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
     * ON POST: Let assume in RequestForProposal we have relation to AbstractStandardWorkflow, but there we can understand from what concrete type is AbstractStandardWorkflow from the IRI in workflow's property value that is send from customer
     * Or with other words we change relation from abstract to concrete relay on property's IRI that comes from outside
     *
     */
    private function onDeserialization(string $name, PropertyMetadata $propertyMetadata, string $resourceClass): PropertyMetadata
    {
        /** @var \Symfony\Component\PropertyInfo\Type */
        $type = $propertyMetadata->getType();

        // On PUT HTTP Request we relay on already set relation object first
        $objectToPopulate = $this->denormalizeContext[AbstractObjectNormalizer::OBJECT_TO_POPULATE] ?? null;

        if ($objectToPopulate !== null) {
            if (method_exists($objectToPopulate, 'get'.$name)) {
                $concreteRelation = $objectToPopulate->{'get'.$name}();
                if ($concreteRelation !== null) {
                    $concreteClass = get_class($concreteRelation);

                    $concreteType = new Type(Type::BUILTIN_TYPE_OBJECT, $type->isNullable(), $concreteClass);
                    $propertyMetadata = $propertyMetadata->withType($concreteType);
                    try {
                        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
                        $attributes = $resourceMetadata->getAttributes();
                        if (isset($attributes[$name]['readableLink'])) {
                            $propertyMetadata = $propertyMetadata->withReadableLink($attributes[$name]['readableLink']);
                        }
                    } catch (ResourceClassNotFoundException $exception) {
                        // just skip
                    }
                }
            }

            return $propertyMetadata;
        }

        if (isset($this->data[$name]) === true) {
            $concreteClass = null;
            try {
                $context = $this->router->getContext();
                $context->setMethod('GET');
                $routeCollection = $this->router->getRouteCollection();
                $matcher = new TraceableUrlMatcher($routeCollection, $context);
                /** @var \Symfony\Component\Routing\Route */
                $apiRouteInfo = $matcher->match($this->data[$name]);
                $concreteClass = $apiRouteInfo['_api_resource_class'] ?? null;
            } catch (\Exception $exception) {
                throw new UnexpectedValueException(sprintf('Invalid IRI "%s".', $this->data[$name]));
            }

            if ($concreteClass !== null && is_subclass_of($concreteClass, $type->getClassName())) {
                $concreteType = new Type(Type::BUILTIN_TYPE_OBJECT, $type->isNullable(), $concreteClass);
                $propertyMetadata = $propertyMetadata->withType($concreteType);
                try {
                    $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
                    $attributes = $resourceMetadata->getAttributes();
                    if (isset($attributes[$name]['readableLink'])) {
                        $propertyMetadata = $propertyMetadata->withReadableLink($attributes[$name]['readableLink']);
                    }
                } catch (ResourceClassNotFoundException $exception) {
                    // just skip
                }
            } else {
                throw new UnexpectedValueException(sprintf('Invalid IRI "%s".', $this->data[$name]));
            }
        }

        return $propertyMetadata;
    }

    /**
     * TODO: Currently not implemented: Here the idea is somehow on documentation to change type from AbstractStandardWorkflow to not be showed
     * TODO: like embedded object in RequestForProposal {}, but to be showed like "string" (from embedded object to IRI only)
     */
    private function onDocumentation(string $name, PropertyMetadata $propertyMetadata, string $resourceClass): PropertyMetadata
    {
//        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
//        $attributes = $resourceMetadata->getAttributes();
//        if (isset($attributes[$name]['writableLink']) && $attributes[$name]['writableLink'] === true) {
//            $propertyMetadata = $propertyMetadata->withWritableLink(true);
//        } else {
//            $propertyMetadata = $propertyMetadata->withWritableLink(false);
//        }

        return $propertyMetadata;
    }
}
