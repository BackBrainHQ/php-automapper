<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Profiles;

use Backbrain\Automapper\Profile;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV1;
use Symfony\Component\Uid\UuidV3;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV5;
use Symfony\Component\Uid\UuidV6;

class Rfc4122ToUidProfile extends Profile
{
    public function __construct()
    {
        $this
            // create default map for UUIDs
            ->createMap('string', Uuid::class)
                ->convertUsing(fn (string $source): Uuid => Uuid::fromString($source))
            ->createMap('string', UuidV6::class)
                ->convertUsing(fn (string $source): UuidV6 => UuidV6::fromString($source))
            ->createMap('string', UuidV5::class)
                ->convertUsing(fn (string $source): UuidV5 => UuidV5::fromString($source))
            ->createMap('string', UuidV4::class)
                ->convertUsing(fn (string $source): UuidV4 => UuidV4::fromString($source))
            ->createMap('string', UuidV3::class)
                ->convertUsing(fn (string $source): UuidV3 => UuidV3::fromString($source))
            ->createMap('string', UuidV1::class)
                ->convertUsing(fn (string $source): UuidV1 => UuidV1::fromString($source))

            // create default map for Ulids
            ->createMap('string', Ulid::class)
                ->convertUsing(fn (string $source): Ulid => Ulid::fromString($source))
        ;
    }
}
