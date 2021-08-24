<?php

namespace App\Controller;

use App\Service\MarginCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MarginController extends AbstractController
{
    #[Route('/get-total-margin', name: 'get_total_margin')]
    public function getTotalMargin(MarginCalculator $marginCalculator): Response
    {
        return $this->json($marginCalculator->calculate());
    }
}