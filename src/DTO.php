<?php

declare(strict_types=1);

namespace Eseath\PayKeeper;

use Eseath\PayKeeper\Attributes\MapFrom;
use ReflectionClass;
use ReflectionEnum;
use ReflectionNamedType;

abstract class DTO
{
    public static function createFrom(array $data)
    {
        $reflectionClass = new ReflectionClass(static::class);
        $parameters = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $mapFromAttributes = $property->getAttributes(MapFrom::class);

            // Определяем имя поля в массиве (либо из атрибута, либо как имя свойства)
            $fieldName = $propertyName;
            if (!empty($mapFromAttributes)) {
                $mapFromAttribute = $mapFromAttributes[0]->newInstance();
                $fieldName = $mapFromAttribute->sourceField;
            }


            $propertyValue = $data[$fieldName];
            $propertyType = $property->getType();

            if ($propertyValue && $propertyType->getName() === 'DateTime') {
                $parameters[$propertyName] = new \DateTime($data[$fieldName], new \DateTimeZone('Europe/Moscow'));
            } else if ($propertyType instanceof ReflectionNamedType && enum_exists($propertyType->getName())) {
                $enumClass = $propertyType->getName();
//                $parameters[$propertyName] = $enumClass::from($data[$fieldName]);
                $parameters[$propertyName] = (new ReflectionEnum($enumClass))->getCase($data[$fieldName])->getValue();
            } else {
                $parameters[$propertyName] = $data[$fieldName];
            }
        }

        return $reflectionClass->newInstanceArgs($parameters);
    }
}
