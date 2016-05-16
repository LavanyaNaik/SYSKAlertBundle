<?php

namespace SYSK\AlertBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use SYSK\AlertBundle\Entity\SyskIrritant;

/**
 * This command is called by request to create the base data for the application
 */
class CreateBaseIrritantsCommand extends ContainerAwareCommand
{
    protected $em;

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        parent::configure();
        $this
            ->setName('sysk:create:base-irritants')
            ->setDescription('Create the base irritants for the SYSK widget.');
    }

    /**
     * @param InputInterface  $input  The params the user gave
     * @param OutputInterface $output The output to display some things
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        try {
            $baseArray      = $this->retrieveBaseArray();
            $flushBase      = false;
            $irrRepository  = $em->getRepository('SYSKAlertBundle:SyskIrritant');

            foreach ( $baseArray as $baseIrritant ) {
                // Check for base irritant already in DB
                $existingIrritant = $irrRepository->findBy( array( "irritantToken" => $baseIrritant["token"] ) );

                if( count( $existingIrritant ) > 0 )
                    continue;

                // Save new irritants
                $flushBase      = true;
                $syskIrritant   = new SyskIrritant();

                $syskIrritant->setIrritantToken( $baseIrritant["token"] );
                $syskIrritant->setIrritantMessage( $baseIrritant["message"] );

                $em->persist( $syskIrritant );
            }

            if( $flushBase ){
                $em->flush();
                $output->writeln("<info>Saved Entries</info>");
            }
            
        } catch (\Exception $e) {
            $output->writeln("<info>".$e->getMessage()."</info>");
        }
    }

    public function retrieveBaseArray(){
        $baseArray = array();

        $element = array();
        $element["token"]   = "NOT_REPLY";
        $element["message"] = "As-tu entendu parler du bouton reply ? ";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NON_AVAILABLE";
        $element["message"] = "Tu es rarement disponible, dommage j'aurais parfois besoin de te voir";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "Un candidat dont j’ai cruellement besoin me dit qu’il t'a vu il y a un mois. Dommage : tes amis sont mes amis";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_FILLED_CVS";
        $element["message"] = "Tes candidats font de superbes CV sur DYB. Si seulement ils pouvaient remplir eux-mêmes la case commentaires & les tags";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "On te préfère avec la banane. Si on peut aider ...";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "Ne me laisse pas ta part des problèmes, je suis rassasié";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_MANNERS";
        $element["message"] = "\"Bonjour\" : n.m. terme de salutation courtois";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "Un des tes candidats s'est inscrit à \"perdu de vue\"";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_MANNERS";
        $element["message"] = "Certes Manager peut rimer avec Dictateur ... mais ça s’arrête la ...";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_MANNERS";
        $element["message"] = "Se répartir les comptes \"différent de\" Me répartir les comptes";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "Davidson est un sport collectif";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "Surprendre vaut mieux que survendre";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_QUALITY";
        $element["message"] = "Je ne suis pas un correcteur orthographique";
        $baseArray[] = $element;

        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "Il ne pique pas trop l'oursin ? (dans ta poche...)";
        $baseArray[] = $element;

        $element = array();
        $element["token"]   = "NOT_COMMUNICATION";
        $element["message"] = "Si tu me dis qu’il est topissime, embauche le";
        $baseArray[] = $element;

        return $baseArray;
    }
}
