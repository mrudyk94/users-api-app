<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

trait ValueObjectTrait
{
    protected mixed $value;

    public function asString(): string
    {
        return strval($this->value);
    }

    public function __toString(): string
    {
        return strval($this->value);
    }

    public function equalsTo(ValueObject $valueObject): bool
    {
        return $this->value === $valueObject->toNative();
    }

    public function jsonSerialize(): mixed
    {
        return $this->toNative();
    }

    public function toNative(): mixed
    {
        return $this->value;
    }
}
