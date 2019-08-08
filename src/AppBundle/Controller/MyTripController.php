<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Response;

class MyTripController extends FrontendController
{
    /**
     * @return Response
     */
    public function mapAction(): Response
    {
        $this->get('coreshop.seo.presentation')->updateSeoMetadata($this->document);

        return $this->renderTemplate('MyTrip/map.html.twig');
    }
}