<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Uses a service container to create constraint validators with dependencies.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 * @author Alex Kalyvitis <alex.kalyvitis@gmail.com>
 */
class ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var \Pimple
     */
    protected $container;

    /**
     * @var array
     */
    protected $serviceNames;

    /**
     * @var array
     */
    protected $validators;

    /**
     * Constructor
     *
     * @param \Pimple $container    DI container
     * @param array   $serviceNames Validator service names
     */
    public function __construct(\Pimple $container, array $serviceNames = array())
    {
        $this->container    = $container;
        $this->serviceNames = $serviceNames;
        $this->validators   = array();
    }

    /**
     * Returns the validator for the supplied constraint.
     *
     * @param  Constraint          $constraint A constraint
     * @return ConstraintValidator A validator for the supplied constraint
     */
    public function getInstance(Constraint $constraint)
    {
        $name = $constraint->validatedBy();

        if (isset($this->validators[$name])) {
            return $this->validators[$name];
        }

        $this->validators[$name] = $this->createValidator($name);

        return $this->validators[$name];
    }

    /**
     * Returns the validator instance
     *
     * @param  string              $name
     * @return ConstraintValidator
     */
    private function createValidator($name)
    {
        if (isset($this->serviceNames[$name])) {
            return $this->container[$this->serviceNames[$name]];
        }

        return new $name();
    }
}
