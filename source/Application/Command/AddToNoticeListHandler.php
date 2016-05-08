<?php

namespace OxidEsales\Eshop\Application\Command;

class AddToNoticeListHandler
{
    public function handleAddToNoticeListCommand(AddToNoticeListCommand $command)
    {
        $oBasket = oxNew('oxuserbasket');
        $oBasket->loadNoticeBasket();
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