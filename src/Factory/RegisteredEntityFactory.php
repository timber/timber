<?php

namespace Timber\Factory;

use InvalidArgumentException;
use Timber\RegisteredEntity;

/**
 * Internal API class for instantiating registered entities like post types and taxonomies.
 *
 * Subclasses provide the lookup of the vanilla WordPress objects and the class map filters.
 *
 * @internal
 */
abstract class RegisteredEntityFactory
{
    /**
     * The default Timber class of the entities this factory builds.
     *
     * @return class-string<RegisteredEntity>
     */
    abstract protected function entity_class(): string;

    /**
     * The class of the vanilla WordPress objects this factory builds entities from.
     *
     * @return class-string
     */
    abstract protected function wp_object_class(): string;

    /**
     * Looks up a vanilla WordPress object by its registered name.
     */
    abstract protected function get_wp_object(string $name): ?object;

    /**
     * Resolves arguments to a list of registered names.
     *
     * @return string[]
     */
    abstract protected function query_names(array $args): array;

    /**
     * Applies the class map filter of the entity.
     */
    abstract protected function apply_classmap_filter(): array;

    /**
     * Applies the class filter of the entity.
     */
    abstract protected function apply_class_filter(string $class, object $wp_object): string;

    /**
     * @param mixed $params A registered name, an array of names, an array of query arguments, a
     *                      vanilla WordPress object or a Timber entity.
     *
     * @return RegisteredEntity|RegisteredEntity[]|null
     */
    public function from($params)
    {
        if (\is_object($params)) {
            return $this->from_object($params);
        }

        if (\is_string($params)) {
            return $this->from_name($params);
        }

        if (!\is_array($params)) {
            throw new InvalidArgumentException(\sprintf(
                'Expected a name, an array or an instance of %s or %s, got %s',
                $this->entity_class(),
                $this->wp_object_class(),
                \get_debug_type($params)
            ));
        }

        // A flat list of names.
        if (!empty($params) && \array_is_list($params) && $this->is_array_of_strings($params)) {
            return $this->from_names($params);
        }

        return $this->from_query_args($params);
    }

    protected function from_name(string $name): ?RegisteredEntity
    {
        $wp_object = $this->get_wp_object($name);

        if (!$wp_object) {
            return null;
        }

        return $this->build($wp_object);
    }

    /**
     * @param string[] $names
     * @return RegisteredEntity[] Keyed by name. Names that aren’t registered are skipped.
     */
    protected function from_names(array $names): array
    {
        $entities = [];

        foreach ($names as $name) {
            $entity = $this->from_name($name);

            if ($entity) {
                $entities[$name] = $entity;
            }
        }

        return $entities;
    }

    /**
     * @return RegisteredEntity[] Keyed by name.
     */
    protected function from_query_args(array $args): array
    {
        return $this->from_names(\array_values($this->query_names($args)));
    }

    protected function from_object(object $obj): RegisteredEntity
    {
        $entity_class = $this->entity_class();

        if ($obj instanceof $entity_class) {
            return $obj;
        }

        $wp_object_class = $this->wp_object_class();

        if ($obj instanceof $wp_object_class) {
            return $this->build($obj);
        }

        throw new InvalidArgumentException(\sprintf(
            'Expected an instance of %s or %s, got %s',
            $entity_class,
            $wp_object_class,
            $obj::class
        ));
    }

    /**
     * @return class-string<RegisteredEntity>
     */
    protected function get_entity_class(object $wp_object): string
    {
        $map = $this->apply_classmap_filter();

        $class = $map[$wp_object->name] ?? null;

        if (\is_callable($class)) {
            $class = $class($wp_object);
        }

        $class ??= $this->entity_class();

        return $this->apply_class_filter($class, $wp_object);
    }

    protected function build(object $wp_object): RegisteredEntity
    {
        $class = $this->get_entity_class($wp_object);

        return $class::build($wp_object);
    }

    protected function is_array_of_strings($arr)
    {
        if (!\is_array($arr)) {
            return false;
        }
        foreach ($arr as $v) {
            if (!\is_string($v)) {
                return false;
            }
        }
        return true;
    }
}
