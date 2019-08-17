<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\Data\Geopoint;
use Pimcore\Model\DataObject\Journey;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\DataObject\Step;
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

        if ($this->journeyAccess($this->journey, true)) {
            return $this->renderTemplate('MyTrip/map.html.twig', [
                'journeyId' => $this->journey->getId()
            ]);
        }

        return $this->renderTemplate('MyTrip/map.html.twig');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function addStep(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->journey = Journey::getById($data['journeyId']);

        $response = [
            'message' => 'error.not.login',
            'success' => false,
        ];


        if($this->journeyAccess($this->journey)) {

            $step = New Step();
            $step->setParent(Service::createFolderByPath(
                sprintf('%s/%s', $this->journey->getFullPath(), 'steps')));
            $step->setKey(Service::getValidKey(sprintf('%s_%s_%s',
                $data['title'],
                round($data['lat']),
                round($data['lng'])
            ), 'object'));
            $step->setKey(Service::getUniqueKey($step));
            $step->setTitle($data['title']);
            $step->setGeoPoint(new Geopoint( $data['lng'], $data['lat']));
            $step->setPublished(true);
            $step->save();

            $response = [
                'message' => 'save.step',
                'success' => true,
            ];
        }

        return new JsonResponse($response);
    }

    public function loadSteps(): JsonResponse
    {
        $response = [
            'message' => 'save.step',
            'success' => true,
        ];
        return new JsonResponse($response);
    }

    /**
     * @param $journey
     * @param bool $guest
     * @return bool
     */
    private function journeyAccess($journey, $guest = false)
    {
        if (!$journey instanceof Journey) {
            return false;
        }

        if ($guest && !$journey->getPrivate()) {
            return true;
        }

        return $this->loginUser instanceof MembersUser && ($journey->getOwner() === $this->loginUser
            || in_array($this->loginUser, $this->journey->getShare(), true));
    }
}
