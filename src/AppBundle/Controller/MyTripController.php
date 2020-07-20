<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\JourneyType;
use AppBundle\Form\Type\StepEditType;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
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
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

class MyTripController extends FrontendController
{
    /**
     * @var object|string
     */
    private $loginUser;

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
     */
    public function __construct()
    {
        $this->websiteConfig = Config::getWebsiteConfig();
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function mapAction(Request $request): Response
    {
        $this->loginUser = $this->getUser();
        $this->get('coreshop.seo.presentation')->updateSeoMetadata($this->document);
        $this->journey = Journey::getById($request->get('id'));

        if ($this->loginUser instanceof MembersUser) {

            $createJourneyForm = $this->createForm(JourneyType::class);
            $stepEditForm = $this->createForm(StepEditType::class);

            $createJourneyForm = $createJourneyForm->handleRequest($request);
            if ($createJourneyForm->isSubmitted() && $createJourneyForm->isValid()) {
                $data = $createJourneyForm->getData();

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
                'createJourneyForm' => $createJourneyForm->createView(),
                'editStepForm' => $stepEditForm->createView(),
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
     * @throws GuzzleException
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

                if ($data['dateFrom'] && $data['timeFrom']) {
                    $dateFrom = Carbon::createFromFormat('d.m.Y H:i',
                        sprintf('%s %s', $data['dateFrom'], $data['timeFrom']));
                }

                if ($data['dateTo'] && $data['timeTo']) {
                    $dateTo = Carbon::createFromFormat('d.m.Y H:i',
                        sprintf('%s %s', $data['dateTo'], $data['timeTo']));
                }

                $step->setDateTime($dateFrom);
                $step->setDateTimeTo($dateTo);
                $step->save();

                $this->updateRoutes($this->journey);

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
     * @throws GuzzleException
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
                $this->updateRoutes($this->journey);
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
                        'dateFrom' => $step->getDateTime() instanceof Carbon ? $step->getDateTime()->format('d.m.Y') : '',
                        'dateTo' => $step->getDateTimeTo() instanceof Carbon ? $step->getDateTimeTo()->format('d.m.Y') : '',
                        'timeFrom' => $step->getDateTime() instanceof Carbon ? $step->getDateTime()->format('H:i') : '',
                        'timeTo' => $step->getDateTimeTo() instanceof Carbon ? $step->getDateTimeTo()->format('H:i') : '',
                        'distance' => $this->roundDistance($step->getDistance()),
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
    public function getNextDate(Request $request) {
        $lastStep = null;
        $response = ['message' => 'error.date.not.found', 'success' => false];

        $data = json_decode($request->getContent(), true);
        $this->journey = Journey::getById($data['id']);

        if($this->journeyAccess($this->journey)) {
            $stepList = new Step\Listing();
            $stepList->setLimit(1);
            $stepList->setCondition('dateTime IS NOT NULL OR dateTimeTo IS NOT NULL AND journey__id = :id', [
                'id' => $data['id']
            ]);
            $stepList->setOrderKey(['dateTimeTo', 'dateTime', 'o_id']);
            $stepList->setOrder(['DESC', 'DESC', 'DESC']);

            if ($stepList->getCount()) {
                $lastStep = $stepList->load()[0];
            }

            if ($lastStep instanceof Step) {
                if ($lastStep->getDateTimeTo() instanceof Carbon) {
                    $response = [
                        'date' => $lastStep->getDateTimeTo()->addDay(1)->format('d.m.Y'),
                        'time' => $lastStep->getDateTimeTo()->format('H:i'),
                        'message' => 'success.date.found',
                        'success' => true
                    ];
                } else if ($lastStep->getDateTime() instanceof Carbon) {
                    $response = [
                        'date' => $lastStep->getDateTime()->addDay(1)->format('d.m.Y'),
                        'time' => $lastStep->getDateTime()->format('H:i'),
                        'message' => 'success.date.found',
                        'success' => true
                    ];
                }
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
            $stepsResponse = [];
            if ($stepList = $this->getStepsByJourney($this->journey)) {
                foreach ($stepList as $step) {
                    $stepsResponse[] = [
                        'id' => $step->getId(),
                        'title' => $step->getTitle(),
                        'date' => $step->getDateTime(),
                        'dateTo' => $step->getDateTimeTo(),
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
     * @throws Exception
     */
    public function loadGeoJson(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->journey = Journey::getById($data['id']);
        $response = [ 'message' => 'no.access', 'success' => false ];

        if(!$this->journeyAccess($this->journey)) {
            return new JsonResponse($response);
        }

        if($stepList = $this->getStepsByJourney($this->journey)){
            $geoJson = [];

            $geoJson['point'] = [
                'type' => 'FeatureCollection',
                'features' => []
            ];

            $geoJson['line'] = [
                'type' => 'FeatureCollection',
                'features' => []
            ];

            if ($stepList) {
                foreach ($stepList as $key => $step) {
                    $nextStep = $stepList[$key+1];
                    $radius = 6;

                    // Add Route
                    if ($nextStep instanceof Step) {
                        $lineGeometryCoordinates = [];

                        // Add Line
                        $lineGeometryCoordinates[] = [
                            $step->getGeoPoint()->getLongitude(),
                            $step->getGeoPoint()->getLatitude()
                        ];

                        if ($step->getTransporation() instanceof TransportableType
                            && $step->getGeoRoute()
                            && $step->getTransporation()->getShowNextRoute()
                        ) {
                            foreach ($step->getGeoRoute() as $point) {
                                $lineGeometryCoordinates[] = [
                                    $point->getLongitude(),
                                    $point->getLatitude()
                                ];
                            }
                        }

                        $lineGeometryCoordinates[] = [
                            $nextStep->getGeoPoint()->getLongitude(),
                            $nextStep->getGeoPoint()->getLatitude()
                        ];

                        $geoJson['line']['features'][] = [
                            'type' => 'Feature',
                            'geometry' => [
                                'type' => 'LineString',
                                'coordinates' => $lineGeometryCoordinates
                            ],
                            'properties' => [
                                'id' => $step->getId(),
                                'color' => '#ff0000',
                                'title' => $step->getTitle(),
                                'distance' => $this->roundDistance($step->getDistance())
                            ]
                        ];
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
                'geoJson' => $geoJson,
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * @param Step $start
     * @param Step $end
     * @param string $type
     * @return array|null
     */
    private function getDirection(Step $start, Step $end, string $type = 'mapbox/driving'): ?array
    {
        if (!$start instanceof Step && !$end instanceof Step) {
            return null;
        }

        if (!$start->getGeoPoint() instanceof Geopoint && !$end->getGeoPoint() instanceof Geopoint) {
            return null;
        }

        $client = new Client();

        try {
            $res = $client->request('GET',
                sprintf('https://api.mapbox.com/directions/v5/%s/%s?geometries=geojson&access_token=%s',
                    $type,
                    sprintf('%s,%s;%s,%s',
                        $start->getGeoPoint()->getLongitude(),
                        $start->getGeoPoint()->getLatitude(),
                        $end->getGeoPoint()->getLongitude(),
                        $end->getGeoPoint()->getLatitude()),
                    $this->websiteConfig->get('mapToken')
                )
            );

            if ($res->getStatusCode() === 200) {
                $result = (string) $res->getBody();
                $data = json_decode($result, true);

                if ($data['routes']) {
                    return reset($data['routes']);
                }
            }
        }
        catch (ClientException $e) {
            $response = $e->getResponse();
        }

        return [];
    }

    /**
     * @param $journey
     * @return bool
     * @throws GuzzleException
     */
    private function updateRoutes($journey) {
        if(!$this->journeyAccess($journey)) {
            return false;
        }

        if($stepList = $this->getStepsByJourney($journey)){
            foreach ($stepList as $key => $step) {
                $nextStep = $stepList[$key+1];
                if ($step instanceof Step
                    && $nextStep instanceof Step
                    && $step->getTransporation() instanceof TransportableType
                    && $step->getTransporation()->getShowNextRoute()
                )
                {
                    $route = $this->getDirection($step, $nextStep);

                    if ($route && $route['distance'] && $route['geometry']['coordinates']) {
                        $data = [];
                        foreach ($route['geometry']['coordinates'] as $routePoint) {
                            $data[] = new Geopoint($routePoint[0],$routePoint[1]);
                        }
                        $step->setDistance($route['distance']);
                        $step->setGeoRoute($data);
                        $step->save();
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param Journey $journey
     * @return array
     * @throws Exception
     */
    private function getStepsByJourney(Journey $journey): ?array
    {
        if (!$journey instanceof Journey) {
            return null;
        }

        $stepList = new Step\Listing();
        $stepList->setCondition('journey__id = :id', ['id' => $journey->getId()]);
        $stepList->setOrderKey(['dateTimeTo', 'dateTime', 'o_id']);
        $stepList->setOrder(['ASC', 'ASC', 'ASC']);

        if ($stepList->getCount() === 0) {
            return null;
        }

        return $stepList->getObjects();
    }

    private function roundDistance($distance) {
        if ($distance) {
            return round($distance/1000, 2);
        }
        return $distance;
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
