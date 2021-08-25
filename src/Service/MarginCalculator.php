<?php

namespace App\Service;

use App\Repository\OrderRepository;

class MarginCalculator
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    /**
     * According to Lithuanian laws in force (however, this practice is also accepted in other countries),
     * accounting standards indicate that the FIFO (first-in, first-out) principle must be applied when selling
     * tangible products and accounting for them. That is, the oldest purchased goods must be sold earlier than
     * the one purchased later.
     * This becomes relevant for the calculation of the sales margin, i.e. how much the sales price has exceeded
     * the purchase price (cost price). Especially when the flow of purchases and sales is constant.
     * For example, we bought 10 decorative garden dwarfs for €17 each, sold 6 pieces for €21 per each, then
     * we bought another 10 pcs. batch for €20 and we sold 8 pcs with the price €23 for each.
     * This means that we bought the first 6 dwarfs for €17 per unit and sold for €21 each, so we earned €4
     * from each, i.e. €4 x 6 = €24 in total. Then, when selling the second 8 dwarfs, we had to sell 4 from the
     * first batch (purchased for €17) and another 4 from the second one (€20 each), so we earned (€23-
     * €17)x4+ (€23-€20)x4 = €6x4 + €3x4 = €36. Then € 24 + €36 = €60 comes out in total.
     * The remaining 6 dwarfs are not involved in the calculation, as they have not yet been sold.
     *
     */
    public function calculate(): float
    {
        $sellTotals = $this->orderRepository->getSellTotals();

        $sellQuantity = $sellTotals[0]['quantityTotal'];
        $oldestItemsBuyQuantity = 0;
        $previousBuyOrderId = 0;
        $buyCostPriceTotal = 0;
        while ($sellQuantity > $oldestItemsBuyQuantity) {
            $buyOrder = $this->orderRepository->getNextBuyOrder($previousBuyOrderId);
            $needMoreBuyQuantity = $sellQuantity - $oldestItemsBuyQuantity;
            if ($needMoreBuyQuantity > $buyOrder->getQuantity()) {
                $buyCostPriceTotal += $buyOrder->getQuantity() * $buyOrder->getPrice();
            } else {
                $buyCostPriceTotal += $needMoreBuyQuantity * $buyOrder->getPrice();
            }

            $oldestItemsBuyQuantity += $buyOrder->getQuantity();
            $previousBuyOrderId = $buyOrder->getId();
        }

        return $sellTotals[0]['priceTotal'] - $buyCostPriceTotal;
    }
}
