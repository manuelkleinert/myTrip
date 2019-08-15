<?php
namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\TransportableType;
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
     * @throws \Exception
     */
    public function mapAction(): Response
    {
        $this->get('coreshop.seo.presentation')->updateSeoMetadata($this->document);

        $transportableTypeList = new TransportableType\Listing();
        $transportableTypeList->setOrderKey('o_index');
        $transportableTypeList->setOrder('ASC');

        return $this->renderTemplate('MyTrip/map.html.twig', [
            'loginUser' => $this->loginUser,
            'transportableTypeList' => $transportableTypeList,
        ]);
    }
}