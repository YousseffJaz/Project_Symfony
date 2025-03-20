<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;


class PasswordUpdate
{
    #[Assert\Length(min: 8, minMessage: 'Votre mot de passe doit faire au moins 8 caractères !')]
    private $newPassword;

    #[Assert\EqualTo(propertyPath: 'newPassword', message: 'Les mots de passe sont différents !')]
    private $confirmPassword;


    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}
