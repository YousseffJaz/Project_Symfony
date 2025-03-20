<?php

namespace App\Tests\Entity;

use App\Entity\PasswordUpdate;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use TypeError;

class PasswordUpdateTest extends TestCase
{
    private PasswordUpdate $passwordUpdate;

    protected function setUp(): void
    {
        $this->passwordUpdate = new PasswordUpdate();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->passwordUpdate->getNewPassword());
        $this->assertNull($this->passwordUpdate->getConfirmPassword());
    }

    public function testNewPasswordGetterAndSetter(): void
    {
        $password = "MonSuperMotDePasse123";
        
        $this->passwordUpdate->setNewPassword($password);
        $this->assertEquals($password, $this->passwordUpdate->getNewPassword());
    }

    public function testConfirmPasswordGetterAndSetter(): void
    {
        $password = "MonSuperMotDePasse123";
        
        $this->passwordUpdate->setConfirmPassword($password);
        $this->assertEquals($password, $this->passwordUpdate->getConfirmPassword());
    }

    /**
     * @dataProvider validPasswordsProvider
     */
    public function testValidPasswords(string $password): void
    {
        $this->passwordUpdate->setNewPassword($password);
        $this->passwordUpdate->setConfirmPassword($password);

        $this->assertEquals($password, $this->passwordUpdate->getNewPassword());
        $this->assertEquals($password, $this->passwordUpdate->getConfirmPassword());
        $this->assertTrue($this->passwordUpdate->isPasswordValid());
    }

    public function validPasswordsProvider(): array
    {
        return [
            'mot de passe simple' => ['password12345'],
            'mot de passe complexe' => ['P@ssw0rd2023'],
            'mot de passe très long' => ['Ab1Ab1Ab1Ab1Ab1Ab1'],
            'mot de passe avec caractères spéciaux' => ['Password1@#$'],
            'mot de passe alphanumérique' => ['SuperPassword123']
        ];
    }

    /**
     * @dataProvider invalidPasswordsProvider
     */
    public function testInvalidPasswords(string $password): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->passwordUpdate->setNewPassword($password);
    }

    public function invalidPasswordsProvider(): array
    {
        return [
            'mot de passe vide' => [''],
            'mot de passe avec que des espaces' => ['        '],
            'mot de passe trop court' => ['Pw1'],
            'mot de passe sans lettre' => ['12345678'],
            'mot de passe sans chiffre' => ['PasswordOnly'],
            'mot de passe avec caractères non autorisés' => ['Password€123']
        ];
    }

    public function testPasswordMismatch(): void
    {
        $this->passwordUpdate->setNewPassword('Password123');
        $this->passwordUpdate->setConfirmPassword('DifferentPassword123');

        $this->assertFalse($this->passwordUpdate->isPasswordValid());
    }

    public function testNullPasswords(): void
    {
        $this->expectException(TypeError::class);
        $this->passwordUpdate->setNewPassword(null);
    }

    public function testEmptyConfirmPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->passwordUpdate->setConfirmPassword('');
    }

    public function testPasswordValidation(): void
    {
        $validPassword = 'ValidPassword123';
        
        // Test avec le même mot de passe
        $this->passwordUpdate->setNewPassword($validPassword);
        $this->passwordUpdate->setConfirmPassword($validPassword);
        $this->assertTrue($this->passwordUpdate->isPasswordValid());

        // Test avec des mots de passe différents
        $this->passwordUpdate->setConfirmPassword('DifferentPassword123');
        $this->assertFalse($this->passwordUpdate->isPasswordValid());
    }
} 