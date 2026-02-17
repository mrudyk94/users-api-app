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
            throw new DomainException('Phone number is empty');
        }

        if(mb_strlen($value) !== self::LENGTH) {
            throw new DomainException("`$value` is not a valid phone number value");
        }

        if (!preg_match('/^\+380\d{9}$/', $value)) {
            throw new DomainException("`$value` does not match the format +380XXXXXXXXX");
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
