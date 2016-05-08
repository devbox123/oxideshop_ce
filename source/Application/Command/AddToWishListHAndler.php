<?php

namespace OxidEsales\Eshop\Application\Command;

class AddToWishListHandler
{
    public function handleAddToWishListCommand(AddToWishListCommand $command)
    {
        $oBasket = oxNew('oxuserbasket');
        $oBasket->loadWishBasket();
        $oBasket->addItemToBasket(
            $command->getProductId(),
            $command->getAmount(),
            $command->getSelection(),
            $command->removeItem()
        );

        // recalculate basket count
        $oBasket->getItemCount(true);
    }
}