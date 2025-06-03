<?php

namespace App\Entity;

use App\Repository\DriverRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DriverRepository::class)]
class Driver
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Имя водителя обязательно')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Имя водителя должно содержать минимум {{ limit }} символа',
        maxMessage: 'Имя водителя не должно превышать {{ limit }} символов'
    )]
    #[Assert\Regex(
        pattern: '/^[а-яА-ЯёЁa-zA-Z\s-]+$/u',
        message: 'Имя может содержать только буквы, пробелы и дефис'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Телефон обязателен')]
    #[Assert\Regex(
        pattern: '/^\+7[0-9]{10}$/',
        message: 'Телефон должен быть в формате +7XXXXXXXXXX'
    )]
    private ?string $phone = null;

    #[ORM\Column]
    private ?bool $is_active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;
        return $this;
    }
} 