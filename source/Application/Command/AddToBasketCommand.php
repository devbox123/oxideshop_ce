<?php
namespace OxidEsales\Eshop\Application\Command;

class AddToBasketCommand
{
    private $products;
    private $basket;

    public function __construct($products, $basket)
    {
        $this->products = $products;
        $this->basket = $basket;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return \oxbasket
     */
    public function getBasket()
    {
        return $this->basket;
    }
}