<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DomainException;

final class MobilePhone implements ValueObject
{
    use ValueObjectTrait;

    const LENGTH = 13;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        if ($value === '') {
            throw new DomainException('Телефон порожній');
        }

        if(mb_strlen($value) !== self::LENGTH) {
            throw new DomainException("`$value` неправильне значення для телефону");
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
