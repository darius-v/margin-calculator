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
        foreach ($orders as $order) {
            $nextOrders[] = [$previousId, $order];
            $previousId = $order->getId();
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