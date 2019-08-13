<?php
namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class MyTripController extends FrontendController
{
    /**
     * @var object|string
     */
    private $loginUser;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;


    public function __construct(
        Session $session,
        TokenStorage $tokenStorage
    )
    {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->loginUser = $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @return Response
     */
    public function mapAction(): Response
    {
        $this->get('coreshop.seo.presentation')->updateSeoMetadata($this->document);

        return $this->renderTemplate('MyTrip/map.html.twig', [
            'loginUser' => $this->loginUser
        ]);
    }
}