<?php

namespace Aljerom\SimpleHydrator;

use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionNamedType;

class SimpleHydrator
{
    public function hydrate(array $data, string $className): object
    {
        $reflection = new ReflectionClass($className);
        $attributes = $this->getAttributes($reflection);
        $dto = $reflection->newInstanceWithoutConstructor();

        $props = [];
        $refProps = $reflection->getProperties();
        foreach ($refProps as $prop) {
            $type = $prop->getType();
            $props[$name = $prop->getName()] = [
                'name' => $name,
                'type' => $type instanceof ReflectionNamedType ? $type->getName() : '',
                'isNullable' => $type->allowsNull(),
                'isObject' => $type instanceof ReflectionNamedType && class_exists($type->getName()),
            ];
        }
        unset($refProps);

        // Promoted properties do not get their default values defined via constructor
        // https://bugs.php.net/bug.php?id=81386&edit=1
        if ($reflection->getConstructor()) {
            foreach ($reflection->getConstructor()->getParameters() as $param) {
                if ($param->isPromoted() && $param->isDefaultValueAvailable()) {
                    $dto->{$param->name} = $param->getDefaultValue();
                }
            }
        }

        $isSetterDto = isset($attributes[Setter::class]);
        foreach ($data as $property => $value) {
            $property = self::camelCase($property);
            if (property_exists($dto, $property)) {
                $value = match (true) {
                    $props[$property]['type'] === DateTimeInterface::class => $value ?
                        new DateTimeImmutable($value) :
                        (new DateTimeImmutable())->setTimestamp(0),
                    $props[$property]['type'] === 'array' => is_string($value) ?
                        json_decode($value, true, 512, JSON_THROW_ON_ERROR) :
                        $value,
                    $props[$property]['isObject'] => $this->hydrate($value, $props[$property]['type']),
                    $value === null && !$props[$property]['isNullable'] && isset($dto->{$property}) => $dto->{$property},
                    default => $value,
                };
                $reflectionProperty = $reflection->getProperty($property);
                $reflectionProperty->setValue($dto, $value);
            }
        }

        return $dto;
    }

    private function getAttributes(ReflectionClass $reflection): array
    {
        if ($refAttributes = $reflection->getAttributes()) {
            $attributes = array_reduce(
                $refAttributes,
                static function ($result, $attr) {
                    $result[$attr->getName()] = $attr;
                    return $result;
                },
                []
            );
        }
        return $attributes ?? [];
    }

    private static function camelCase(string $attribute): string
    {
        if (strpos($attribute, '_')) {
            $attribute = mb_strtolower($attribute);
            $attribute = str_replace('_', '', lcfirst(ucwords($attribute, '_')));
        } else {
            $attribute = lcfirst($attribute);
        }
        return $attribute;
    }
}
