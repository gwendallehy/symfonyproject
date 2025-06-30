<?php
namespace App\Tests\Validator;

use App\Validator\StrongPassword;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StrongPasswordValidatorTest extends KernelTestCase
{
private ValidatorInterface $validator;

protected function setUp(): void
{
self::bootKernel();
$this->validator = static::getContainer()->get(ValidatorInterface::class);
}

public function testValidPassword(): void
{
$violations = $this->validator->validate('Abc123@!', [new StrongPassword()]);
$this->assertCount(0, $violations);
}

public function testTooWeakPassword(): void
{
$violations = $this->validator->validate('abc', [new StrongPassword()]);
$this->assertGreaterThan(0, count($violations));
$this->assertSame(
'Le mot de passe doit contenir au moins 8 caractères, avec une majuscule, une minuscule, un chiffre et un caractère spécial.',
$violations[0]->getMessage()
);
}
}
