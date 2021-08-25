<?php /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */

namespace App\Tests\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\MarginCalculator;
use Monolog\Test\TestCase;

class MarginCalculatorTest extends TestCase
{
    /**
     * @dataProvider provideMargin
     */
    public function testMargin(float $expected, array $orders)
    {
        $calculator = new MarginCalculator($this->mockOrderRepository($orders));

        $this->assertEquals($expected, $calculator->calculate());
    }

    private function mockOrderRepository(array $orders): OrderRepository
    {
        $mock = $this->createMock(OrderRepository::class);

        $this->calculateSellTotals($orders);

        $mock
            ->method('getSellTotals')
            ->willReturn($this->calculateSellTotals($orders))
        ;

        $nextOrders = [];
        $previousId = 0;
        /** @var Order $order */
        foreach ($orders as $order) {
            if ($order->getType() === Order::TYPE_BUY) {
                $nextOrders[] = [$previousId, $order];
                $previousId = $order->getId();
            }
        }

        $mock
            ->method('getNextBuyOrder')
            ->willReturnMap($nextOrders)
        ;

        return $mock;
    }

    private function calculateSellTotals(array $orders): array
    {
        $priceTotal = 0;
        $quantityTotal = 0;
        /** @var Order $order */
        foreach ($orders as $order) {
            if ($order->getType() === Order::TYPE_SELL) {
                $priceTotal += $order->getPrice() * $order->getQuantity();
                $quantityTotal += $order->getQuantity();
            }
        }

        return [0 => ['priceTotal' => $priceTotal, 'quantityTotal' => $quantityTotal]];
    }

    public function provideMargin(): array
    {
        return [
            '1 buy, 1 sell' => [
                'expected margin' => 6*3 - 6*1,
                'orders' => [
                    $this->mockOrder(1, Order::TYPE_BUY, 10, 1),
                    $this->mockOrder(2, Order::TYPE_SELL, 6, 3),
                ],
            ],
            '2nd sell order sells from 1st and 2nd buy orders' => [
                'expected margin' => (21-17) * 6 + (23-17) * 4 + (23-20) * 4,
                'orders' => [
                    $this->mockOrder(1, Order::TYPE_BUY, 10, 17),
                    $this->mockOrder(2, Order::TYPE_SELL, 6, 21),
                    $this->mockOrder(3, Order::TYPE_BUY, 10, 20),
                    $this->mockOrder(4, Order::TYPE_SELL, 8, 23),
                ],
            ],
            'Sell order sells from 2 buy orders' => [
                'expected margin' => (21-17) * 10 + (21-20) * 1,
                'orders' => [
                    $this->mockOrder(1, Order::TYPE_BUY, 10, 17),
                    $this->mockOrder(3, Order::TYPE_BUY, 10, 20),
                    $this->mockOrder(2, Order::TYPE_SELL, 11, 21),
                ],
            ],
            '2 sell orders sells from 2 buy orders, all quantity sold' => [
                'expected margin' => (21-17) * 10 + (21-20) * 1 + (22-20) * 9,
                'orders' => [
                    $this->mockOrder(1, Order::TYPE_BUY, 10, 17),
                    $this->mockOrder(3, Order::TYPE_BUY, 10, 20),
                    $this->mockOrder(2, Order::TYPE_SELL, 11, 21),
                    $this->mockOrder(2, Order::TYPE_SELL, 9, 22),
                ],
            ]
        ];
    }

    private function mockOrder(int $id, string $type, int $quantity, float $price)
    {
        $order = $this->createMock(Order::class);

        $order
            ->method('getId')
            ->willReturn($id)
        ;

        $order
            ->method('getType')
            ->willReturn($type)
        ;

        $order
            ->method('getQuantity')
            ->willReturn($quantity)
        ;

        $order
            ->method('getPrice')
            ->willReturn($price)
        ;

        return $order;
    }
}