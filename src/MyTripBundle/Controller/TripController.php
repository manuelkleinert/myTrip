<?php

namespace MyTripBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;

class TripController extends FrontendController
{
    public function mapDetailAction(Request $request)
    {
        return $this->render('Map/default.html.twig');
    }
}
