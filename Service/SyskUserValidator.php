<?php

namespace SYSK\AlertBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * SyskUser Validator
 *
 * @author Davidson
 */
class SyskUserValidator
{
    /** @var EntityManager */
    protected $em;
    /** @var SecurityContext */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param EntityManager       $em              The entity manager.
     * @param SecurityContext     $securityContext SecurityContext is the main entry point of the Security component.
     */
    public function __construct( EntityManager $em, SecurityContext $securityContext )
    {
        $this->em = $em;
        $this->securityContext = $securityContext;
    }

    /**
     * Validate existing SYSK User
     *
     * @return array
     */
    public function validateSYSKUser()
    {
        // Retrieve the user based on the security Context
        $user = $this->securityContext->getToken()->getUser();
        $syskUserRepository = $this->em->getRepository('SYSKAlertBundle:SyskUser');

        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

        if( !$syskUser )
            return false;
        else
            return true;
    }
}
