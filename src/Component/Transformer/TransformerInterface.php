<?php


namespace App\Component\Transformer;


use App\Component\DTO\DTOInterface;
use Doctrine\ORM\EntityManagerInterface;

interface TransformerInterface
{
    static function fromEntity(TransformerConfig $config);

    static function toEntity(DTOInterface $dto, EntityManagerInterface $em);
}