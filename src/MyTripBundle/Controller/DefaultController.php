<?php

namespace MyTripBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends FrontendController
{

    public function mapAction(Request $request)
    {
        return $this->render('@MyTrip/Map/default.html.twig');
    }
}
