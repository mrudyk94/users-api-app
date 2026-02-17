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
            throw new DomainException('Password is empty');
        }

        if(mb_strlen($value) !== self::LENGTH) {
            throw new DomainException(
                sprintf(
                    "Password is too long. Maximum length: %s characters.",
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
