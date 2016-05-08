<?php
namespace OxidEsales\Eshop\Application\Command;

class AddToBasketHandler
{
    public function handleAddToBasketCommand(AddToBasketCommand $command)
    {
        $basketItem = null;
        $basket = $command->getBasket();

        $basketInfo = $basket->getBasketSummary();

        $basketItemAmounts = array();

        foreach ($command->getProducts() as $addProductId => $productInfo) {

            $data = $this->prepareProductInformation($addProductId, $productInfo);
            $productAmount = $basketInfo->aArticles[$data['id']];
            $products[$addProductId]['oldam'] = isset($productAmount) ? $productAmount : 0;

            //If we already changed articles so they now exactly match existing ones,
            //we need to make sure we get the amounts correct
            if (isset($basketItemAmounts[$data['oldBasketItemId']])) {
                $data['amount'] = $data['amount'] + $basketItemAmounts[$data['oldBasketItemId']];
            }

            $basketItem = $this->addItemToBasket($basket, $data);

            if (is_a($basketItem, 'oxBasketItem') && $basketItem->getBasketItemKey()) {
                $basketItemAmounts[$basketItem->getBasketItemKey()] += $data['amount'];
            }

            if (!$basketItem) {
                $info = $basket->getBasketSummary();
                $productAmount = $info->aArticles[$data['id']];
                $products[$addProductId]['am'] = isset($productAmount) ? $productAmount : 0;
            }
        }

        //if basket empty remove possible gift card
        if ($basket->getProductsCount() == 0) {
            $basket->setCardId(null);
        }

        $basket->save();

        return $basketItem;


    }

    /**
     * Add one item to basket. Handle eventual errors.
     *
     * @param \oxbasket $basket
     * @param $data
     *
     * @return \oxbasketitem
     */
    protected function addItemToBasket($basket, $itemData)
    {
        $basketItem = $basket->addToBasket(
            $itemData['id'],
            $itemData['amount'],
            $itemData['selectList'],
            $itemData['persistentParameters'],
            $itemData['override'],
            $itemData['bundle'],
            $itemData['oldBasketItemId']
        );

        return $basketItem;
    }


    /**
     * Prepare information for adding product to basket.
     *
     * @param $productInfo
     *
     * @return array
     */
    protected function prepareProductInformation($addProductId, $productInfo)
    {
        $return = array();

        $return['id'] = isset($productInfo['aid']) ? $productInfo['aid'] : $addProductId;
        $return['amount'] = isset($productInfo['am']) ? $productInfo['am'] : 0;
        $return['selectList'] = isset($productInfo['sel']) ? $productInfo['sel'] : null;

        $return['override'] = isset($productInfo['override']) ? $productInfo['override'] : null;
        $return['bundle'] = isset($productInfo['bundle']) ? true : false;
        $return['oldBasketItemId'] = isset($productInfo['basketitemid']) ? $productInfo['basketitemid'] : null;

        return $return;
    }
}