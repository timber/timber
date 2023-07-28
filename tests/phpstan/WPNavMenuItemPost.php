<?php

namespace TimberPHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use WP_Post;

class WPNavMenuItemPost implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        $nav_menu_item_properties = [
            'object_id',
            'menu_item_parent',
            'object',
            'type',
        ];

        return $classReflection->is(WP_Post::class) && \in_array($propertyName, $nav_menu_item_properties, true);
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new WPPostPropertyReflection($classReflection);
    }
}
