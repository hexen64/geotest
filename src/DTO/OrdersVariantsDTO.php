<?php

namespace App\DTO;

class OrdersVariantsDTO
{
    public int $id;
    public string $variantId;
    public bool $isBase;
    public int $cnt;
    public ?string $type;
    public string $name;
    public float $price;
}