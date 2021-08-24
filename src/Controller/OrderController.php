<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\Type\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/', name: 'order_form')]
    public function ordersAction(Request $request): Response
    {
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $order = $form->getData();

            /** @var SubmitButton $buyButton */
            $buyButton = $form->get('buy');

            if ($buyButton->isClicked()) {
                $order->setType(Order::TYPE_BUY);
            } else {
                $order->setType(Order::TYPE_SELL);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('order_form');
        }

        return $this->render('base.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}