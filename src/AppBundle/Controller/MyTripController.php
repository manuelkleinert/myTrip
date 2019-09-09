<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\JourneyType;
use GuzzleHttp\Client;
use Pimcore\Config;
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
use Exception;

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
     * @var Config
     */
    private $websiteConfig;


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
        $this->websiteConfig = Config::getWebsiteConfig();
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

            $createJourney = $this->createForm(JourneyType::class);
            $createJourney = $createJourney->handleRequest($request);
            if ($createJourney->isSubmitted() && $createJourney->isValid()) {
                $data = $createJourney->getData();

                $journey = new Journey();
                $journey->setParent(Service::createFolderByPath(sprintf('journeys/%s', $this->loginUser->getId())));
                $journey->setKey(Service::getValidKey($data['title'], 'object'));
                $journey->setTitle($data['title']);
                $journey->setSubTitle($data['subTitle']);
                $journey->setOwner($this->loginUser);
                $journey->setPublished(true);
                $journey->save();
            }

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
                'createJourneyForm' => $createJourney->createView(),
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
     * @throws Exception
     */
    public function updateStep(Request $request): JsonResponse
    {
        $step = null;
        $data = json_decode($request->getContent(), true);
        $this->journey = Journey::getById($data['journeyId']);

        $response = [
            'message' => 'error.not.login',
            'success' => false,
        ];

        if($this->journeyAccess($this->journey)) {

            if ($data['stepId']) {
                $step = Step::getById($data['stepId']);
            } else {
                $step = New Step();
            }

            $transportableObject = TransportableType::getById($data['transportableId']);

            if ($step instanceof Step) {
                $step->setParent(Service::createFolderByPath(
                    sprintf('%s/%s', $this->journey->getFullPath(), 'steps')));
                $step->setKey(Service::getValidKey(sprintf('%s_%s_%s',
                    $data['title'],
                    round($data['lat']),
                    round($data['lng'])
                ), 'object'));
                $step->setKey(Service::getUniqueKey($step));
                $step->setJourney($this->journey);
                $step->setTitle($data['title']);
                $step->setGeoPoint(new Geopoint( $data['lng'], $data['lat']));
                $step->setPublished(true);

                if ($transportableObject instanceof TransportableType) {
                    $step->setTransporation($transportableObject);
                }

                $step->save();

                $response = [
                    'message' => 'save.step',
                    'success' => true,
                ];
            }
        }

        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function removeStep(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $response = ['message' => 'error.not.remove.step', 'success' => false];

        $this->journey = Journey::getById($data['journeyId']);

        if($this->journeyAccess($this->journey)) {
            $step = Step::getById($data['stepId']);
            if ($step instanceof Step && $this->journey === $step->getJourney()) {
                $step->delete();
                $response = [
                    'message' => 'remove.step',
                    'success' => true
                ];
            }
        }

        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function loadStep(Request $request): JsonResponse
    {
        $response = [ 'message' => 'no.access', 'success' => false ];
        $data = json_decode($request->getContent(), true);
        $step = Step::getById($data['id']);

        if ($step instanceof Step) {
            $this->journey = $step->getJourney();
            if($this->journeyAccess($this->journey)) {

                $transporationId = '';
                if ($step->getTransporation() instanceof TransportableType) {
                    $transporationId = $step->getTransporation()->getId();
                }

                $response = [
                    'message' => 'load.step',
                    'success' => true,
                    'data' => [
                        'journeyId' => $this->journey->getId(),
                        'stepId' => $step->getId(),
                        'title' => $step->getTitle(),
                        'text' => $step->getText(),
                        'date' => $step->getDateTime(),
                        'dateTo' => $step->getDateTimeTo(),
                        'lat' => $step->getGeoPoint()->getLatitude(),
                        'lng' => $step->getGeoPoint()->getLongitude(),
                        'transportableId' => $transporationId,
                    ]
                ];
            }
        }

        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function loadStepList(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->journey = Journey::getById($data['id']);

        $response = [ 'message' => 'no.access', 'success' => false ];

        if($this->journeyAccess($this->journey)) {
            $stepsList = new Step\Listing();
            $stepsList->setCondition('journey__id = :id', [ 'id' => $this->journey->getId()]);
            $stepsList->setOrderKey(['dateTime', 'o_creationDate']);
            $stepsList->setOrder(['ASC', 'ASC']);
            $stepsList->load();

            $stepsResponse = [];
            if ($stepsList) {
                foreach ($stepsList as $step) {
                    $stepsResponse[] = [
                        'id' => $step->getId(),
                        'title' => $step->getTitle(),
                        'date' => $step->getDateTime(),
                        'date' => $step->getDateTimeTo(),
                        'lat' => $step->getGeoPoint()->getLatitude(),
                        'lng' => $step->getGeoPoint()->getLongitude(),
                    ];
                }
            }

            $response = [
                'message' => 'load.step',
                'success' => true,
                'data' => $stepsResponse
            ];
        }

        return new JsonResponse($response);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loadGeoJson(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->journey = Journey::getById($data['id']);

        $response = [ 'message' => 'no.access', 'success' => false ];

        if($this->journeyAccess($this->journey)) {
            $stepsList = new Step\Listing();
            $stepsList->setCondition('journey__id = :id', [ 'id' => $this->journey->getId()]);
            $stepsList->setOrderKey(['dateTime', 'o_creationDate']);
            $stepsList->setOrder(['ASC', 'ASC']);
            $stepsList->load();

            $geoJson = [];

            $geoJson['point'] = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            $geoJson['line'] = [
                'type' => 'Feature',
                'properties' => [],
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => []
                ]
            ];

            if ($stepsList) {
                $objectArray = $stepsList->getObjects();
                foreach ($stepsList as $key => $step) {
                    $radius = 6;

                    // Add Line
                    $geoJson['line']['geometry']['coordinates'][] = [
                        $step->getGeoPoint()->getLongitude(),
                        $step->getGeoPoint()->getLatitude(),
                    ];


                    if ($step->getTransporation() instanceof TransportableType) {
                        if ($step->getTransporation()->getShowNextRoute()) {
                            $nextStep = $objectArray[$key+1];
                            if ($nextStep instanceof Step) {
                                $this->getDirection($step, $nextStep);
                                die;
                            }
                        }
                    }

                    // Set Radius
                    if ($step->getText() || $step->getText()) {
                        $radius = 8;
                    }

                    // Add symbol
                    $geoJson['point']['features'][] = [
                        'type'=> 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [
                                $step->getGeoPoint()->getLongitude(),
                                $step->getGeoPoint()->getLatitude()
                            ],
                        ],
                        'properties' => [
                            'id' => $step->getId(),
                            'title' => $step->getTitle(),
                            'dataFrom' => $step->getDateTime(),
                            'dateTo' => $step->getDateTimeTo(),
                            'radius' => $radius
                        ],
                    ];
                }
            }

            $response = [
                'message' => 'load.step',
                'success' => true,
                'data' => $geoJson
            ];
        }
        return new JsonResponse($response);
    }

    /**
     * @param Step $start
     * @param Step $end
     * @param string $type
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getDirection(Step $start, Step $end, string $type = 'mapbox/driving'):array
    {
        if (!$start instanceof Step && !$end instanceof Step) {
            return null;
        }

        if (!$start->getGeoPoint() instanceof Geopoint && !$end->getGeoPoint() instanceof Geopoint) {
            return null;
        }

        $client = new Client();
        $res = $client->request('GET', sprintf('https://api.mapbox.com/directions/v5/%s/%s?access_token=%s}',
            $type,
            sprintf('%s%%2C%s%%3B%s%%2C%s',
                $start->getGeoPoint()->getLatitude(),
                $start->getGeoPoint()->getLongitude(),
                $end->getGeoPoint()->getLatitude(),
                $end->getGeoPoint()->getLongitude()),
            $this->websiteConfig->get('mapToken')
            )
        );

        echo $res->getStatusCode();
        echo $res->getBody();

        return [];
    }

    /**
     * @param $journey
     * @param bool $guest
     * @return bool
     */
    private function journeyAccess($journey, $guest = false):bool
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
