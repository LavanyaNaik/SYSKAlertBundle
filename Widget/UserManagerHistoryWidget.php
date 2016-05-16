<?php

namespace SYSK\AlertBundle\Widget;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Twig_Environment;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use SYSK\AlertBundle\Widget\UserWidget;
use SYSK\AlertBundle\Entity\SyskMessage;
use SYSK\AlertBundle\Form\WidgetManagerSearchType;

/**
 * User Manager History widget
 *
 * Service id : sysk.manager.history.widget
 * Description : Allows you to check messages for a selected number of users. User list must come from 
 *               external non-sysk service.
 */
class UserManagerHistoryWidget extends UserWidget
{
    private $container;
    private $addControls    = false; // TODO: Add controls to user same widget as main administration tool.
    private $pageMax        = 2; // TODO: parametrisize max per page number
    private $bridgeService  = NULL;

    /**
     * @param EntityManager       $em              The entity manager.
     * @param SecurityContext     $securityContext SecurityContext is the main entry point of the Security component.
     * @param Session             $session         Session.
     * @param Twig_Environment    $twig            Twig.
     * Extra Params
     * @param Container           $container       Service container, in order to call additional functions
     * @param String              $bridgeServiceName   Brigde custom service to link to user site
     */
    public function __construct(    EntityManager $em, 
                                    SecurityContext $securityContext, 
                                    Session $session, 
                                    Twig_Environment $twig, 
                                    $templateName,
                                    Container $container,
                                    $bridgeServiceName,
                                    $pageMax,
                                    $router )
    {
        parent::__construct( $em, $securityContext, $session, $twig, $templateName );

        $this->container            = $container;
        $this->bridgeServiceName    = $bridgeServiceName;
        $this->pageMax              = $pageMax;
        $this->router               = $router;
    }

    /**
     * The widget uses ajax request to load the data
     * {@inheritDoc}
     */
    public function getData()
    {
        $page           = 1;
        $paginationMax  = 1;
        $syskMessages   = array();

        try {
            // Retrieve the user based on the security Context
            $user = $this->securityContext->getToken()->getUser();
            $form = $this->container->get('form.factory')->create( 
                    new WidgetManagerSearchType( $this->router, $this->container, $this->bridgeService ));
            $bridgeService = $this->container->get( $this->bridgeServiceName );

            $syskUserRepository = $this->em->getRepository('SYSKAlertBundle:SyskUser');
            $syskMsgRepository  = $this->em->getRepository('SYSKAlertBundle:SyskMessage');

            $syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

            if( $syskUser )
            {
                $result     = array();
                $children   = $bridgeService->retrieveSYSKManagerUsers( $user->getId() );

                foreach ($children as $key => $child) {
                    $result = $this->buildChildEntry( $result, $child );
                }

                $extraHeaders = array();

                foreach ($result as $key => $child) {
                    // Retrieve all messages
                    $syskMessagesDB = $syskMsgRepository->findBy(array( "receiver"  => $child["syskUser"],
                                                                        "deleted"   => false ),
                                                                array(  "createdAt" => "DESC" ));
                    if( count( $syskMessagesDB ) <= 0 ){
                        continue;
                    }

                    // Retrieve all extra headers for columns
                    foreach( $child["extras"] as $xtrKey => $xtrValue ) {
                        if( !array_key_exists( $xtrKey, $extraHeaders ) ){
                            $extraHeaders[ $xtrKey ] = $xtrValue["label"];
                        }
                    }

                    foreach ( $syskMessagesDB as $msgKey => $msgValue )
                    {
                        $element = array();

                        $element["name"] = $child["firstName"]." ".$child["lastName"];

                        if( $msgValue->getMessageType() == SyskMessage::TYPE_POSITIVE ){
                            $element["message"] = $msgValue->getMessageText();
                            $element["type"]    = "Feedback positif";
                        }elseif( $msgValue->getMessageType() == SyskMessage::TYPE_NEGATIVE ){
                            if( $msgValue->getIrritantId() == NULL ) continue;
                            $element["message"] = $msgValue->getIrritantId()->getIrritantMessage();
                            $element["type"]    = "Something You Should Know";
                        }else{
                            continue;
                        }

                        $element["status"]  = ( $msgValue->getStatus() == SyskMessage::STATUS_READ )? "Lu" : "Non-lu";
                        $element["created"] = $msgValue->getCreatedAt()->format("d/m/Y");
                        $element["extras"]  = $child["extras"];

                        $syskMessages[] = $element;
                    }
                }
            }else{
                // NO existing user for the connected user
                $syskMessages[] = array();
            }
        } catch (\Exception $e) {
            $syskMessages[] = array();
        }

        if( count( $syskMessages ) > $this->pageMax ){
            $paginationMax = floor( count($syskMessages)/$this->pageMax) + 
                             ( ( count($syskMessages)%$this->pageMax == 0 )? 0 : 1 ) ;
        }

        $syskMessagesReturn = array();
        $index = 1;

        foreach ( $syskMessages as $message ) {
            if( $index > ($page-1)*$this->pageMax && $index <= ($page)*$this->pageMax ){
                $syskMessagesReturn[] = $message;
            }
            $index++;
        }

        return array(
            "searchForm"    => $form->createView(),
            "syskMessages"  => $syskMessagesReturn,
            "extraHeaders"  => $extraHeaders,
            "empty_message" => "Aucun message trouvé.",
            "paginationMax" => $paginationMax,
            "actualPage"    => 1
        );
    }

    private function buildChildEntry( $result, $child ){
        $syskUserRepository = $this->em->getRepository('SYSKAlertBundle:SyskUser');

        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $child["userId"] ) );

        if( $syskUser ){
            $element    = array();
            $element["syskUser"]    = $syskUser;
            $element["userId"]      = $child["userId"];
            $element["firstName"]   = $child["firstName"];
            $element["lastName"]    = $child["lastName"];
            $element["extras"]      = $child["extras"];

            $result[] = $element;

            if( count( $child["children"] > 0 ) ){
                foreach ( $child["children"] as $k => $c ) {
                    $result = $this->buildChildEntry( $result, $c );
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'userManagerHistory';
    }
}
