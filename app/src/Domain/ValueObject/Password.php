<?php

namespace App\Domain\ValueObject;

use DomainException;

final class Password implements ValueObject
{
    use ValueObjectTrait;

    const LENGTH = 8;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        if ($value === '') {
            throw new DomainException('Пароль порожній');
        }

        if(mb_strlen($value) !== self::LENGTH) {
            throw new DomainException(
                sprintf(
                    "Пароль занадто довгий. Максимальна довжина: %s символів.",
                    self::LENGTH
                )
            );
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
