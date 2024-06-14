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

class UidToRfc4122Profile extends Profile
{
    public function __construct()
    {
        $this
            // create default map for UUIDs
            ->createMap(Uuid::class, 'string')
                ->convertUsing(fn (UuidV6 $source): string => $source->toRfc4122())
            ->createMap(UuidV6::class, 'string')
                ->convertUsing(fn (UuidV6 $source): string => $source->toRfc4122())
            ->createMap(UuidV5::class, 'string')
                ->convertUsing(fn (UuidV5 $source): string => $source->toRfc4122())
            ->createMap(UuidV4::class, 'string')
                ->convertUsing(fn (UuidV4 $source): string => $source->toRfc4122())
            ->createMap(UuidV3::class, 'string')
                ->convertUsing(fn (UuidV3 $source): string => $source->toRfc4122())
            ->createMap(UuidV1::class, 'string')
                ->convertUsing(fn (UuidV1 $source): string => $source->toRfc4122())

            // create default map for Ulids
            ->createMap(Ulid::class, 'string')
                ->convertUsing(fn (Ulid $source): string => $source->toRfc4122())
        ;
    }
}
