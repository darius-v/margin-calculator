<?php

namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;

class MarginCalculator
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    public function calculate(): float
    {
//        $orders = $this->orderRepository->getOrders();

        /*$margin = 0;
        $previousBuyOrderId = 0;
        $previousSellOrderId = 0;
        $buyOrder = $this->orderRepository->getNextOrder($previousBuyOrderId, Order::TYPE_BUY);
        $itemsLeft = 0;
        while ($buyOrder !== null) {


            $sellOrder = $this->orderRepository->getNextOrder($previousBuyOrderId, Order::TYPE_SELL);

            if ($itemsLeft > 0) {
                $margin = $itemsLeft * $sellOrder->getPrice() - $buyOrder->getQuantity() * $buyOrder->getPrice();
            }

            if ($sellOrder->getQuantity() >= $buyOrder) {
                $margin = $sellOrder->getQuantity() * $sellOrder->getPrice() - $buyOrder->getQuantity() * $buyOrder->getPrice();
                $itemsLeft = $buyOrder->getQuantity() - $sellOrder->getQuantity();
            }
        }*/


        // pasiimam visus sell orderius ir suzinom kieki ir kaina is viso.
        // tada paimam pirmu buy orderiu tiek kad kiekis atistiktu. Ir paskaiciuojam kaina

        $sellTotals = $this->orderRepository->getSellTotals();

        $sellQuantity = $sellTotals[0]['quantityTotal'];
        $oldestItemsBuyQuantity = 0;
        $previousBuyOrderId = 0;
        $buyCostPriceTotal = 0;
        while ($sellQuantity > $oldestItemsBuyQuantity) {
            $buyOrder = $this->orderRepository->getNextOrder($previousBuyOrderId, Order::TYPE_BUY); // todo maybe no need 2nd param?
            $needMoreBuyQuantity = $sellQuantity - $oldestItemsBuyQuantity;
            if ($needMoreBuyQuantity > $buyOrder->getQuantity()) {
                $buyCostPriceTotal += $buyOrder->getQuantity() * $buyOrder->getPrice();
            } else {
                $buyCostPriceTotal += $needMoreBuyQuantity * $buyOrder->getPrice();
            }

            $oldestItemsBuyQuantity += $buyOrder->getQuantity();
        }


//        foreach ($orders as $order) {
//            if ($order->getType() === Order::TYPE_BUY) {
//                $margin -= $order->
//            }
//        }

//        370 islaidos
//        6*21 + 8 * 23 = 310 pajamos

        return $buyCostPriceTotal -  $sellTotals[0]['priceTotal'];
    }
}
