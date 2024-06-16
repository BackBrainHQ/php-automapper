<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Profiles;

use Backbrain\Automapper\Profile;

class DateTimeProfile extends Profile
{
    public function __construct()
    {
        $this
            // create default map for DateTime classes
            ->createMap(\DateTime::class, \DateTimeInterface::class)
                ->convertUsing(fn (\DateTime $source): \DateTimeInterface => $source)
            ->createMap(\DateTimeImmutable::class, \DateTimeInterface::class)
                ->convertUsing(fn (\DateTimeImmutable $source): \DateTimeInterface => $source)
            ->createMap(\DateTimeImmutable::class, \DateTime::class)
                ->convertUsing(fn (\DateTimeImmutable $source): \DateTime => \DateTime::createFromImmutable($source));
    }
}
