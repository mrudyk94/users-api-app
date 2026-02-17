<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

trait ValueObjectTrait
{
    protected mixed $value;

    /**
     * @return string
     */
    public function asString(): string
    {
        return strval($this->value);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return strval($this->value);
    }

    /**
     * @param ValueObject $valueObject
     * @return bool
     */
    public function equalsTo(ValueObject $valueObject): bool
    {
        return $this->value === $valueObject->toNative();
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toNative();
    }

    /**
     * @return mixed
     */
    public function toNative(): mixed
    {
        return $this->value;
    }
}
