<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\CustomType;

use App\Domain\ValueObject\MobilePhone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class MobilePhoneType extends StringType
{
    public const TYPE_NAME = 'vo_mobile_phone';

    /***
     * @inheritDoc
     * @return mixed
     */
    public function convertToDatabaseValue(
        $value,
        AbstractPlatform $platform
    ): mixed {
        if (! $value instanceof MobilePhone) {
            return $value;
        }

        return parent::convertToDatabaseValue(
            $value->asString(),
            $platform
        );
    }

    /***
     * @inheritDoc
     * @return mixed
     */
    public function convertToPHPValue(
        $value,
        AbstractPlatform $platform
    ): mixed {
        if ($value === null) {
            return null;
        }

        return new MobilePhone(parent::convertToPHPValue(
            $value,
            $platform
        ));
    }

    /**
     * @param AbstractPlatform $platform
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::TYPE_NAME;
    }
}
