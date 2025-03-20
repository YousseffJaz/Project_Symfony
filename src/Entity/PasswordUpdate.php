<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use InvalidArgumentException;

class PasswordUpdate
{
    #[Assert\NotBlank(message: 'Le mot de passe ne peut pas être vide')]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères !',
        maxMessage: 'Votre mot de passe ne peut pas dépasser {{ limit }} caractères !'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/',
        message: 'Le mot de passe doit contenir au moins une lettre et un chiffre'
    )]
    private ?string $newPassword = null;

    #[Assert\NotBlank(message: 'La confirmation du mot de passe ne peut pas être vide')]
    #[Assert\EqualTo(
        propertyPath: 'newPassword',
        message: 'Les mots de passe sont différents !'
    )]
    private ?string $confirmPassword = null;

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        if (empty(trim($newPassword))) {
            throw new InvalidArgumentException('Le mot de passe ne peut pas être vide');
        }

        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('Le mot de passe doit faire au moins 8 caractères');
        }

        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/', $newPassword)) {
            throw new InvalidArgumentException('Le mot de passe doit contenir au moins une lettre et un chiffre');
        }

        $this->newPassword = $newPassword;
        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        if (empty(trim($confirmPassword))) {
            throw new InvalidArgumentException('La confirmation du mot de passe ne peut pas être vide');
        }

        $this->confirmPassword = $confirmPassword;
        return $this;
    }

    public function isPasswordValid(): bool
    {
        return $this->newPassword === $this->confirmPassword;
    }
}
