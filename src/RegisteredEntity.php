<?php

namespace Timber;

use Stringable;

/**
 * Class RegisteredEntity
 *
 * Base class for objects that wrap something registered through a `register_*()` function, like a
 * post type or a taxonomy. Everything that was passed to the registration function ends up as a
 * property on the object.
 *
 * Unlike `Timber\CoreEntity`, a registered entity describes a kind of content instead of holding
 * content itself, so it has no meta.
 *
 * @api
 * @since 2.6.0
 *
 * @template TWp of object The vanilla WordPress object this entity wraps.
 * @phpstan-consistent-constructor
 */
abstract class RegisteredEntity extends Core implements CoreInterface, Stringable
{
    /**
     * The underlying WordPress Core object.
     *
     * @var TWp|null
     */
    protected ?object $wp_object = null;

    /**
     * @api
     * @var string The name the entity was registered with (ex: `post` or `genre`).
     */
    public $name;

    /**
     * @api
     * @var object The labels of the entity.
     */
    public $labels;

    /**
     * @api
     * @var object The capabilities of the entity.
     */
    public $cap;

    /**
     * @internal
     */
    protected function __construct()
    {
    }

    /**
     * Capability a user needs to manage the content of this entity in the admin.
     *
     * @internal
     */
    abstract protected function edit_capability(): string;

    /**
     * Path of the admin screen that lists the content of this entity, relative to `admin_url()`.
     *
     * @internal
     */
    abstract protected function edit_path(): string;

    /**
     * @internal
     *
     * @param TWp $wp_object The vanilla WordPress object to build from.
     * @return static
     */
    public static function build(object $wp_object): static
    {
        $entity = new static();
        $entity->init($wp_object);

        return $entity;
    }

    /**
     * @internal
     *
     * @param TWp $wp_object
     */
    protected function init(object $wp_object): void
    {
        $this->wp_object = $wp_object;
        $this->import($wp_object);
    }

    /**
     * The string the entity will render as by default.
     *
     * @api
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Gets the underlying WordPress Core object.
     *
     * @api
     * @return TWp|null
     */
    public function wp_object(): ?object
    {
        return $this->wp_object;
    }

    /**
     * Gets the human-readable name of the entity.
     *
     * Use this instead of `name`, which holds the name the entity was registered with
     * (ex: `post_tag`), and not the label you defined for it (ex: `Tags`).
     *
     * @api
     * @example
     * ```twig
     * <h1>{{ entity.title }}</h1>
     * ```
     *
     * @return string
     */
    public function title(): string
    {
        return $this->labels->name ?? $this->name;
    }

    /**
     * Checks whether the current user can manage the content of this entity in the admin.
     *
     * @api
     * @example
     * ```twig
     * {% if entity.can_edit %}
     *     <a href="{{ entity.edit_link }}">Manage</a>
     * {% endif %}
     * ```
     *
     * @return bool
     */
    public function can_edit(): bool
    {
        return \current_user_can($this->edit_capability());
    }

    /**
     * Gets the link to the admin screen that lists the content of this entity.
     *
     * @api
     * @example
     * ```twig
     * {% if entity.can_edit %}
     *     <a href="{{ entity.edit_link }}">Manage</a>
     * {% endif %}
     * ```
     *
     * @return string|null The edit URL or `null` if the current user can’t manage the content of
     *                     this entity.
     */
    public function edit_link(): ?string
    {
        if (!$this->can_edit()) {
            return null;
        }

        return \admin_url($this->edit_path());
    }
}
