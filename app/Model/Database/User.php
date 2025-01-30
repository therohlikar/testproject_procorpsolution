<?php

namespace App\Model\Database;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\Column]
    protected string $name;

    #[ORM\Column]
    protected string $mail;

    #[ORM\Column]
    protected string $password;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mail' => $this->mail,
            'password' => $this->password
        ];
    }
}