<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

interface ValueObject extends \JsonSerializable, \Stringable
{
    /**
     * @return mixed
     */
    public function toNative(): mixed;

    /**
     * @param ValueObject $object
     *
     * @return bool
     */
    public function equalsTo(ValueObject $object): bool;
}
