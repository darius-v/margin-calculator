<?php

namespace App\Controller;

use App\Service\MarginCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class MarginController extends AbstractController
{
    #[Route('/get-total-margin', name: 'get_total_margin')]
    public function getTotalMargin(MarginCalculator $marginCalculator): RedirectResponse
    {
        $this->addFlash('info', 'Margin: ' . $marginCalculator->calculate());

        return $this->redirectToRoute('order_form');
    }
}