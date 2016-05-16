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
use SYSK\AlertBundle\Form\WidgetSearchType;

/**
 * User History widget
 *
 * service id : sysk.history.widget
 */
class UserHistoryWidget extends UserWidget
{
    private $container;
    private $pageMax = 20;

    /**
     * @param EntityManager       $em              The entity manager.
     * @param SecurityContext     $securityContext SecurityContext is the main entry point of the Security component.
     * @param Session             $session         Session.
     * @param Twig_Environment    $twig            Twig.
     */
    public function __construct(    EntityManager $em, 
                                    SecurityContext $securityContext, 
                                    Session $session, 
                                    Twig_Environment $twig, 
                                    $templateName, 
                                    Container $container,
                                    $pageMax )
    {
        parent::__construct( $em, $securityContext, $session, $twig, $templateName );
        $this->pageMax = $pageMax;
        $this->container = $container;
    }

    /**
     * The widget uses ajax request to load the data
     * Params: The currently logged User Id
     * {@inheritDoc}
     */
    public function getData()
    {
        $paginationMax  = 1;
        $syskMessages   = array();
        // Retrieve the user based on the security Context
        $user = $this->securityContext->getToken()->getUser();
        $form = $this->container->get('form.factory')->create( new WidgetSearchType() );

        $syskUserRepository = $this->em->getRepository('SYSKAlertBundle:SyskUser');
        $syskMsgRepository  = $this->em->getRepository('SYSKAlertBundle:SyskMessage');

        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

        if( $syskUser )
        {
            $syskMessagesDB = $syskMsgRepository->findBy(array( "receiver"  => $syskUser,
                                                                "status"    => SyskMessage::STATUS_READ,
                                                                "deleted"   => false ),
                                                        array(  "createdAt" => "DESC" ),
                                                        $this->pageMax, 
                                                        0 );

            $allSyskMessagesDB  = $syskMsgRepository->findBy(array( "receiver"  => $syskUser,
                                                                    "status"    => SyskMessage::STATUS_READ,
                                                                    "deleted"   => false ) );

            foreach ( $syskMessagesDB as $messDB ) {
                $element = array();
                if( $messDB->getMessageType() == SyskMessage::TYPE_POSITIVE ){
                    $element["message"] = $messDB->getMessageText();
                    $element["type"] = "Feedback positif";
                }elseif( $messDB->getMessageType() == SyskMessage::TYPE_NEGATIVE ){
                    if( $messDB->getIrritantId() == NULL ) continue;
                    $element["message"] = $messDB->getIrritantId()->getIrritantMessage();
                    $element["type"] = "Something You Should Know";
                }else{
                    continue;
                }
                
                $element["created"] = $messDB->getCreatedAt()->format("d/m/Y");

                $syskMessages[] = $element;
            }

            if( count( $allSyskMessagesDB ) > $this->pageMax ){
                $paginationMax = floor( count($allSyskMessagesDB)/$this->pageMax) + 
                                 ( ( count($allSyskMessagesDB)%$this->pageMax == 0 )? 0 : 1 ) ;
            }
        }

        return array(
            "searchForm"    => $form->createView(),
            "syskMessages"  => $syskMessages,
            "empty_message" => "Aucun message trouvé.",
            "paginationMax" => $paginationMax,
            "actualPage"    => 1
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'userHistory';
    }
}
