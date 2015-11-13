<?php

namespace SYSK\AlertBundle\Widget;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig_Environment;
use Liuggio\ExcelBundle\Service\ExcelContainer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * UserWidget
 */
abstract class UserWidget
{
    /** @var EntityManager */
    protected $em;
    /** @var SecurityContext */
    protected $securityContext;
    /** @var Session */
    protected $session;
    /** @var Twig_Environment */
    protected $twig;
    /** @var String Template Name */
    protected $templateName;

    /**
     * @param EntityManager       $em              The entity manager.
     * @param SecurityContext     $securityContext SecurityContext is the main entry point of the Security component.
     * @param Session             $session         Session.
     * @param Twig_Environment    $twig            Twig.
     * @param string              $templateName    The template name for the widget.
     */
    public function __construct(EntityManager $em, SecurityContext $securityContext, Session $session, Twig_Environment $twig, $templateName)
    {
        $this->em              = $em;
        $this->securityContext = $securityContext;
        $this->session         = $session;
        $this->twig            = $twig;
        $this->templateName    = $templateName;
    }

    /**
     * {@inheritDoc}
     */
    public function getHtml()
    {
        $data = $this->getData();

        if( $data ){
            return $this->twig->render($this->templateName, $data);
        }else{
            return "";
        }
    }
}
