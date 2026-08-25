<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_mentions')]
    public function mentionsLegales(): Response
    {
        return $this->render('front/legal/mentions_legales.html.twig');
    }

    #[Route('/cgu', name: 'app_legal_cgu')]
    public function cgu(): Response
    {
        return $this->render('front/legal/cgu.html.twig');
    }

    #[Route('/confidentialite', name: 'app_legal_confidentialite')]
    public function confidentialite(): Response
    {
        return $this->render('front/legal/confidentialite.html.twig');
    }
}
