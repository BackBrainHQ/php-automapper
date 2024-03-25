<?php
// php docs/example/01_getting_started_usage.php
use Backbrain\Automapper\Contract\Builder\Options;
use Backbrain\Automapper\Contract\Builder\Config;
use Backbrain\Automapper\MapperConfiguration;

require_once __DIR__ . '/../../vendor/autoload.php';

class AccountDTO {
    public string $givenName;
    public string $familyName;
}

class ProfileDTO {
    private string $givenName;
    private string $familyName;
    private string $fullName;

    public function setGivenName(string $givenName): ProfileDTO
    {
        $this->givenName = $givenName;
        return $this;
    }

    public function setFamilyName(string $familyName): ProfileDTO
    {
        $this->familyName = $familyName;
        return $this;
    }

    public function setFullName(string $fullName): ProfileDTO
    {
        $this->fullName = $fullName;
        return $this;
    }
}


$config = new MapperConfiguration(fn (Config $config) => $config
    ->createMap(AccountDTO::class, ProfileDTO::class)
    ->forMember(
        'fullName',
        fn (Options $opts) => $opts->mapFrom(
            fn (AccountDTO $source) => sprintf('%s %s', $source->givenName, $source->familyName)
        )
    )
);

$account = new AccountDTO();
$account->givenName = 'John';
$account->familyName = 'Doe';

$autoMapper = $config->createMapper();
$profile = $autoMapper->map($account, ProfileDTO::class);

dump($profile);