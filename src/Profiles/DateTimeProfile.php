<?php

namespace Backbrain\Automapper\Profiles;

use Backbrain\Automapper\Profile;
use Symfony\Component\Clock\DatePoint;

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
            ->createMap(DatePoint::class, \DateTime::class)
                ->convertUsing(fn (DatePoint $source): \DateTime => \DateTime::createFromImmutable($source))
            ->createMap(\DateTimeImmutable::class, \DateTime::class)
                ->convertUsing(fn (\DateTimeImmutable $source): \DateTime => \DateTime::createFromImmutable($source));
    }
}
