<?php
namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\Journey;
use Pimcore\Model\DataObject\MembersUser;
use Pimcore\Model\DataObject\TransportableType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        if ($this->loginUser instanceof MembersUser) {
            $journeyList = new Journey\Listing();
            $journeyList->setCondition('owner__id = :userId OR share LIKE \'%,:userId,%\'', [
                'userId' => $this->loginUser->getId()
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
            ]);
        }

        return $this->renderTemplate('MyTrip/map.html.twig');
    }

  /**
   * @return JsonResponse
   */
    public function addStep(): JsonResponse
    {

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
}
