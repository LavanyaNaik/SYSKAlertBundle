<?php

namespace SYSK\AlertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SYSK\AlertBundle\Entity\SyskMessage;

class WidgetSearchType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$typeChoices = array();
    	$typeChoices[ SyskMessage::TYPE_POSITIVE ] = "Feedback positif";
    	$typeChoices[ SyskMessage::TYPE_NEGATIVE ] = "Something You Should Know";

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
        return 'sysk_alert_search';
    }
}