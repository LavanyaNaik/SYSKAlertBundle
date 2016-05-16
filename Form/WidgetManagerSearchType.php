<?php

namespace SYSK\AlertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use SYSK\AlertBundle\Entity\SyskMessage;

class WidgetManagerSearchType extends AbstractType
{
    /** @var Router */
    protected $router;
    protected $container;
    protected $bridge;

    /**
     * @param Router          $router          The router.
     */
    public function __construct(Router $router, Container $container, $bridge)
    {
        $this->router       = $router;
        $this->container    = $container;
        $this->bridge       = $bridge;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $baseUserArray  = $this->container->get( "sysk.user.information" )->retrieveSYSKUsersInformation();

        $userChoices = array();
        foreach ($baseUserArray as $key => $bUser ) {
            $userChoices[$bUser["firstName"]." ".$bUser["lastName"]] = $bUser["firstName"]." ".$bUser["lastName"];
        }

    	$typeChoices = array();
    	$typeChoices[ SyskMessage::TYPE_POSITIVE ] = "Feedback positif";
    	$typeChoices[ SyskMessage::TYPE_NEGATIVE ] = "Something You Should Know";

        $builder->add(	'name', 	'choice', 	array(  
                            "required" 	=> FALSE,
                            "label" 	=> "Nom/Prénom",
                            "choices"   => $userChoices,
                            'attr'      => array(
                                'placeholder'     => 'Nom/Prénom',
                                'class'           => 'select-sysk-ajax',
                                "multiple"        => "multiple"
                            )
                    ));
        $builder->add(  'name_array',     'hidden',   array("required"  => FALSE));

        $builder->add(	'type', 	'choice',   	array(  "choices" 	=> $typeChoices,
                                                            "required" 	=> FALSE,
	        											    "label" 	=> "Type" ));

        $builder->add(	'startDt', 	'date', 	    array(  "required" 	=> FALSE,
                                                            "format"    => "d/M/y",
                                                            "widget"    => "single_text",
                                                            "attr"      => array( "class" => "filterSyskDate" )));

        $builder->add(	'endDt', 	'date', 	    array(  "required" 	=> FALSE,
                                                            "format"    => "d/M/y",
                                                            "widget"    => "single_text",
        												    "attr" 	    => array( "class" => "filterSyskDate" )));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sysk_alert_search_manager';
    }
}