<?php
// php docs/example/01_basic.php
use Backbrain\Automapper\Contract\Builder\MemberOptionsBuilderInterface;
use Backbrain\Automapper\Contract\Builder\ProfileBuilderInterface;
use Backbrain\Automapper\MapperConfiguration;

require_once __DIR__ . '/../../vendor/autoload.php';

class AccountDTO {
    public string $givenName;

    public string $familyName;

    public int $age;

    public float $height;
}

class ProfileDTO {
    private string $givenName;

    private string $familyName;

    private string $fullName;

    private int $age;

    private float $height;

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

    public function setAge(int $age): ProfileDTO
    {
        $this->age = $age;
        return $this;
    }

    public function setHeight(float $height): ProfileDTO
    {
        $this->height = $height;
        return $this;
    }
}


$config = new MapperConfiguration(fn (ProfileBuilderInterface $config) => $config
    ->createMap(AccountDTO::class, ProfileDTO::class)
    ->forMember(
        'fullName',
        fn (MemberOptionsBuilderInterface $opts) => $opts->mapFrom(
            fn (AccountDTO $source) => sprintf('%s %s (%d)', $source->givenName, $source->familyName, $source->age)
        )
    )
);

$account = new AccountDTO();
$account->givenName = 'John';
$account->familyName = 'Doe';
$account->age = 30;
$account->height = 1.75;

$autoMapper = $config->createMapper();
$profile = $autoMapper->map($account, ProfileDTO::class);

dump($profile);