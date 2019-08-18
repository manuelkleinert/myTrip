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
        $this->journey = Journey::getById($data['id']);

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
            $step->setJourney($this->journey);
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

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function loadSteps(Request $request): JsonResponse
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
     * @throws \Exception
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

            $geoJson = [
                'type' => 'FeatureCollection',
                'features' => []
            ];

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

                    $geoJson['features'][] = [
                        'type' => 'line',
                        'source' => [
                            'type'=> 'geojson',
                            'data' => [
                                'type' => 'Feature',
                                'geometry' => [
                                    'type'=> 'LineString',
                                    'coordinates' => [
                                        [-122.48369693756104, 37.83381888486939],
                                        [-122.48348236083984, 37.83317489144141],
                                        [-122.48339653015138, 37.83270036637107],
                                        [-122.48356819152832, 37.832056363179625],
                                        [-122.48404026031496, 37.83114119107971],
                                        [-122.48404026031496, 37.83049717427869],
                                        [-122.48348236083984, 37.829920943955045],
                                        [-122.48356819152832, 37.82954808664175],
                                        [-122.48507022857666, 37.82944639795659],
                                        [-122.48610019683838, 37.82880236636284],
                                        [-122.48695850372314, 37.82931081282506],
                                        [-122.48700141906738, 37.83080223556934],
                                        [-122.48751640319824, 37.83168351665737],
                                        [-122.48803138732912, 37.832158048267786],
                                        [-122.48888969421387, 37.83297152392784],
                                        [-122.48987674713133, 37.83263257682617],
                                        [-122.49043464660643, 37.832937629287755],
                                        [-122.49125003814696, 37.832429207817725],
                                        [-122.49163627624512, 37.832564787218985],
                                        [-122.49223709106445, 37.83337825839438],
                                        [-122.49378204345702, 37.83368330777276]
                                    ]
                                ]
                            ]
                        ],
                        'layout' => [
                            'line-join' => 'round',
                            'line-cap'=> 'round',
                        ],
                        'paint' => [
                            'line-color'=> '#3887be',
                            'line-width'=> 5,
                            'line-opacity'=> 0.75,
                        ]
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
