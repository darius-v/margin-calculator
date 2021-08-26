<?php

namespace App\Validator;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Validator\Constraint\MaxSell;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxSellValidator extends ConstraintValidator
{
    public function __construct(private OrderRepository $orderRepository, private RequestStack $requestStack)
    {
    }

    /**
     * @param Order $order
     * @param Constraint|MaxSell $constraint
     */
    public function validate($order, $constraint)
    {
        if (!isset($this->requestStack->getCurrentRequest()->get('order')['sell'])) {
            return;
        }

        $quantities = $this->orderRepository->getSellBuyQuantities();

        if (!isset($quantities[0])) {
            $this->context->buildViolation('Need to buy items first')
                ->atPath('sell')
                ->addViolation()
            ;

            return;
        }

        if (!isset($quantities[1])) {
            $sellOrdersQuantity = 0;
        } else {
            $sellOrdersQuantity = $quantities[1]['quantityTotal'];
        }

        $canSellQuantity = $quantities[0]['quantityTotal'] - $sellOrdersQuantity;

        if ($order->getQuantity() > $canSellQuantity) {
            $this->context->buildViolation(sprintf(
                    'You can sell only %d items, need to buy more if you want to sell more.',
                    $canSellQuantity
                ))
                ->atPath('sell')
                ->addViolation()
            ;
        }
    }
}