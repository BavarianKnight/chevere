<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\Type;

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Type\TypeNotFoundException;
use Chevere\Interfaces\Type\TypeInterface;

final class Type implements TypeInterface
{
    private string $type;

    private string $validator;

    private string $primitive = '';

    private string $typeHinting;

    public function __construct(string $type)
    {
        $this->type = $type;
        $this->setPrimitive();
        $this->assertHasPrimitive();
        $this->validator = TypeInterface::TYPE_VALIDATORS[$this->primitive];
        $this->typeHinting = $this->primitive;
        if (in_array($this->primitive, [TypeInterface::CLASS_NAME, TypeInterface::INTERFACE_NAME])) {
            $this->typeHinting = $this->type;
        }
    }

    public function validator(): callable
    {
        return $this->validator;
    }

    public function primitive(): string
    {
        return $this->primitive;
    }

    public function typeHinting(): string
    {
        return $this->typeHinting;
    }

    public function validate($var): bool
    {
        if (is_object($var) && $this->isAbleToValidateObjects()) {
            return $this->validateObject($var);
        }

        return $this->validator()($var);
    }

    private function isAbleToValidateObjects(): bool
    {
        return in_array($this->primitive, [TypeInterface::CLASS_NAME, TypeInterface::INTERFACE_NAME]);
    }

    private function validateObject(object $object): bool
    {
        $objectClass = get_class($object);
        switch ($this->primitive) {
            case TypeInterface::CLASS_NAME:
                return $this->isClassName($objectClass);
            case TypeInterface::INTERFACE_NAME:
            default:
                return $this->isInterfaceImplemented($object);
        }
    }

    private function isClassName(string $objectClass): bool
    {
        return $this->type === $objectClass;
    }

    private function isInterfaceImplemented(object $object): bool
    {
        return $object instanceof $this->type;
    }

    private function setPrimitive(): void
    {
        if (isset(TypeInterface::TYPE_VALIDATORS[$this->type])) {
            $this->primitive = $this->type;

            return;
        }

        if (class_exists($this->type)) {
            $this->primitive = 'className';
        } elseif (interface_exists($this->type)) {
            $this->primitive = 'interfaceName';
        }
    }

    private function assertHasPrimitive(): void
    {
        if ('' === $this->primitive) {
            throw new TypeNotFoundException(
                (new Message('Type %type% not found'))
                    ->code('%type%', $this->type)
            );
        }
    }
}
