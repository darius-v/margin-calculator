<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\Type\OrderType;
use App\Validator\Constraint\MaxSell;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    #[Route('/', name: 'order_form')]
    public function ordersAction(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order, ['constraints' => [new MaxSell()]]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$order` variable has also been updated
            $order = $form->getData();

            /** @var SubmitButton $buyButton */
            $buyButton = $form->get('buy');

            if ($buyButton->isClicked()) {
                $order->setType(Order::TYPE_BUY);
            } else {
                $order->setType(Order::TYPE_SELL);
            }

            $entityManager->persist($order);
            $entityManager->flush();

            $this->addFlash('success', 'Order added');

            return $this->redirectToRoute('order_form');
        }

        return $this->render('base.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}