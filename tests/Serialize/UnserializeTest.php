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

namespace Chevere\Tests\Serialize;

use Chevere\Components\Serialize\Unserialize;
use PHPUnit\Framework\TestCase;
use stdClass;

final class UnserializeTest extends TestCase
{
    public function getStdClass(): object
    {
        $object = new stdClass();
        $object->prop1 = 'one';
        $object->prop2 = ['two', 3, false];

        return $object;
    }

    public function testConstruct(): void
    {
        $object = $this->getStdClass();
        $objectClass = get_class($object);
        $serialized = serialize($object);
        $unserialize = new Unserialize($serialized);
        $this->assertEquals($objectClass, $unserialize->type()->typeHinting());
        $this->assertEquals($object, $unserialize->var());
        $this->assertInstanceOf($objectClass, $unserialize->var());
    }
}
