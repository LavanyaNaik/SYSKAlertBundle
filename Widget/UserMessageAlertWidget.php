<?php

namespace SYSK\AlertBundle\Widget;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Twig_Environment;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;

use SYSK\AlertBundle\Widget\UserWidget;
use SYSK\AlertBundle\Entity\SyskMessage;

/**
 * User messaging widget
 *
 * service id : sysk_alert.widget.userMessage
 */
class UserMessageAlertWidget extends UserWidget
{
    /**
     * @param EntityManager       $em              The entity manager.
     * @param SecurityContext     $securityContext SecurityContext is the main entry point of the Security component.
     * @param Session             $session         Session.
     * @param Twig_Environment    $twig            Twig.
     */
    public function __construct( EntityManager $em, SecurityContext $securityContext, Session $session, Twig_Environment $twig, $templateName )
    {
        parent::__construct( $em, $securityContext, $session, $twig, $templateName );
    }

    /**
     * The widget uses ajax request to load the data
     * Params: The currently logged User Id
     * {@inheritDoc}
     */
    public function getData()
    {
        // Retrieve the user based on the security Context
        $user = $this->securityContext->getToken()->getUser();

        $hasNewMessages     = false;
        $messageCount       = 0;
        $syskUserRepository = $this->em->getRepository('SYSKAlertBundle:SyskUser');
        $syskMsgRepository  = $this->em->getRepository('SYSKAlertBundle:SyskMessage');

        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

        if( !$syskUser )
            return null;

        $syskMessages = $syskMsgRepository->findBy( array(  "receiver"  => $syskUser,
                                                            "status"    => SyskMessage::STATUS_NOTREAD,
                                                            "deleted"   => false ) );
        if ( count( $syskMessages ) > 0 ){
            $hasNewMessages = true;
            $messageCount   = count( $syskMessages );
        }

        return array(
            "hasNewMessages"    => $hasNewMessages,
            "message_count"     => $messageCount,
            "token_count"       => $syskUser->getTokenCount()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'userMessageAlert';
    }
}
