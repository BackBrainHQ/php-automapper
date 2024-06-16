<?php

declare(strict_types=1);

namespace Backbrain\Automapper\Profiles;

use Backbrain\Automapper\Profile;

class AnonymousProfile extends Profile
{
    /**
     * @param callable ...$fn Will get called with the Backbrain\Automapper\Contract\Builder\Map instance as the first argument
     */
    public function __construct(string $sourceClassName, string $destClassName, callable ...$fn)
    {
        if (!class_exists($sourceClassName)) {
            throw new \InvalidArgumentException(sprintf('Source class %s does not exist', $sourceClassName));
        }

        if (!class_exists($destClassName)) {
            throw new \InvalidArgumentException(sprintf('Destination class %s does not exist', $destClassName));
        }

        $map = $this->createMap($sourceClassName, $destClassName);

        foreach ($fn as $f) {
            $f($map);
        }
    }
}
