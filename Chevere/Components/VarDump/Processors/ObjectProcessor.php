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

namespace Chevere\Components\VarDump\Processors;

use ReflectionObject;
use ReflectionProperty;
use Throwable;
use Chevere\Components\Type\Interfaces\TypeInterface;
use Chevere\Components\VarDump\VarDumpeable;
use Chevere\Components\VarDump\VarFormat;
use Chevere\Components\VarDump\Interfaces\VarInfoInterface;
use function ChevereFn\stringStartsWith;

final class ObjectProcessor extends AbstractProcessor
{
    private object $var;

    private ReflectionObject $reflectionObject;

    private array $properties;

    private string $className;

    private $aux;

    public function type(): string
    {
        return TypeInterface::OBJECT;
    }

    protected function process(): void
    {
        $this->var = $this->varDump->dumpeable()->var();
        $this->reflectionObject = new ReflectionObject($this->var);
        if (in_array($this->reflectionObject->getName(), $this->varDump->dontDump())) {
            $this->val .= $this->varDump->formatter()->highlight(
                VarInfoInterface::_OPERATOR,
                $this->varDump->formatter()->emphasis(
                    $this->reflectionObject->getName()
                )
            );

            return;
        }
        $this->setProperties();

        // $this->classFile = $this->reflectionObject->getFileName();
        $this->className = get_class($this->var);
        $this->handleNormalizeClassName();
        $this->info = $this->className;
    }

    private function setProperties(): void
    {
        $this->properties = [];
        foreach (VarInfoInterface::PROPERTIES_REFLECTION_MAP as $visibility => $filter) {
            /** @scrutinizer ignore-call */
            $properties = $this->reflectionObject->getProperties($filter);
            foreach ($properties as $property) {
                if (!isset($this->properties[$property->getName()])) {
                    $this->setProperty($property);
                }
                $this->properties[$property->getName()]['visibility'][] = $visibility;
            }
        }
        foreach ($this->properties as $k => $v) {
            $this->processProperty($k, $v);
        }
    }

    private function setProperty(ReflectionProperty $property): void
    {
        $property->setAccessible(true);
        try {
            $value = $property->getValue($this->var);
        } catch (Throwable $e) {
            // $e
        }
        $this->properties[$property->getName()] = ['value' => $value ?? null];
    }

    private function processProperty($key, $var): void
    {
        $visibility = implode(' ', $var['visibility'] ?? $this->properties['visibility']);
        $wrappedVisibility = $this->varDump->formatter()->highlight(VarInfoInterface::_PRIVACY, $visibility);
        $property = '$' . $this->varDump->formatter()->filterEncodedChars($key);
        $wrappedProperty = $this->varDump->formatter()->highlight(VarInfoInterface::_VARIABLE, $property);
        $this->val .= "\n" . $this->varDump->indentString() . $wrappedVisibility . ' ' . $wrappedProperty . ' ';
        $this->aux = $var['value'];
        if (is_object($this->aux) && property_exists($this->aux, $key)) {
            try {
                $reflector = new ReflectionObject($this->aux);
                $prop = $reflector->getProperty($key);
                $prop->setAccessible(true);
                $propValue = $prop->getValue($this->aux);
                if ($this->aux == $propValue) {
                    $this->val .= $this->varDump->formatter()->highlight(
                        VarInfoInterface::_OPERATOR,
                        '(' . $this->varDump->formatter()->emphasis('circular object reference') . ')'
                    );

                    return;
                }
            } catch (Throwable $e) {
                // $e
                return;
            }
        }
        $this->handleDeepth();
    }

    private function handleDeepth(): void
    {
        if ($this->varDump->depth() < 4) {
            $new = (new VarFormat(new VarDumpeable($this->aux), $this->varDump->formatter()))
                ->withDontDump(...$this->varDump->dontDump())
                ->withIndent($this->varDump->indent())
                ->withDepth($this->varDump->depth())
                ->withProcess();
            $this->val .= $new->toString();

            return;
        }
        $this->val .= $this->varDump->formatter()->highlight(
            VarInfoInterface::_OPERATOR,
            '(' . $this->varDump->formatter()->emphasis('max depth reached') . ')'
        );
    }

    private function handleNormalizeClassName(): void
    {
        if (stringStartsWith(VarInfoInterface::_CLASS_ANON, $this->className)) {
            // $this->className = (new Path($this->className))->absolute();
        }
    }
}