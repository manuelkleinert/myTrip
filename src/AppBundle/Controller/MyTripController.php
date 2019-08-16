<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\Journey;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Model\DataObject\TransportableType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @var Journey
     */
    private $journey;


    /**
     * MyTripController constructor.
     * @param Session $session
     * @param TokenStorage $tokenStorage
     */
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
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function mapAction(Request $request): Response
    {
        $this->get('coreshop.seo.presentation')->updateSeoMetadata($this->document);
        $this->journey = Journey::getById($request->get('id'));

        if ($this->loginUser instanceof MembersUser) {
            $journeyList = new Journey\Listing();
            $journeyList->setCondition('owner__id = :userId OR share LIKE :userIdLike', [
                'userId' => $this->loginUser->getId(),
                'userIdLike' => sprintf('%%,%s,%%', $this->loginUser->getId())
            ]);
            $journeyList->setOrderKey('from');
            $journeyList->setOrder('DESC');
            $journeyList->load();

            $transportableTypeList = new TransportableType\Listing();
            $transportableTypeList->setOrderKey('o_index');
            $transportableTypeList->setOrder('ASC');
            $transportableTypeList->load();

            return $this->renderTemplate('MyTrip/map.html.twig', [
                'user' => $this->loginUser,
                'transportableTypeList' => $transportableTypeList,
                'journeyList' => $journeyList,
                'journeyId' => $this->journeyAccess($this->journey) ? $this->journey->getId() : null
            ]);
        }

        if ($this->journeyAccess($this->journey)) {
            return $this->renderTemplate('MyTrip/map.html.twig', [
                'journeyId' => $this->journey->getId()
            ]);
        }

        return $this->renderTemplate('MyTrip/map.html.twig');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addStep(Request $request): JsonResponse
    {

        p_r($request->request);
        p_r($request->isXmlHttpRequest());
        p_r($request->query);

        $data = [
            'message' => 'error.not.login',
            'success' => false,
        ];

        if ($this->loginUser instanceof MembersUser) {
            $data = [
                'message' => 'app.step.is.save',
                'success' => true,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @param $journey
     * @return bool
     */
    private function journeyAccess($journey)
    {
        return $journey instanceof Journey && (!$journey->getPrivate() || $journey->getOwner() === $this->loginUser
                || in_array($this->loginUser, $this->journey->getShare(), true));
    }
}
