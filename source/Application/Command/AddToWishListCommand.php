<?php


namespace OxidEsales\Eshop\Application\Command;


class AddToWishListCommand
{
    private $productId;
    private $amount;
    private $selection;

    public function __construct($sProductId, $amount, $selection)
    {
        $this->productId = $sProductId;
        $this->amount = abs($amount);
        $this->selection = $selection;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getSelection()
    {
        return $this->selection;
    }

    public function removeItem()
    {
        return $this->amount == 0;
    }
}