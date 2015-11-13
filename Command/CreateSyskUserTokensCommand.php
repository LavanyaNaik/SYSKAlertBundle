<?php

namespace SYSK\AlertBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use SYSK\AlertBundle\Entity\SyskUser;

/**
 * This command is called by request to create the employer certification
 */
class CreateSyskUserTokensCommand extends ContainerAwareCommand
{
    protected $em;

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        parent::configure();
        $this
            ->setName('sysk:initialise:sysk-users')
            ->setDescription('Create the base SYSK users and reinitialise tokens.');
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
            $flushBase      = false;
            $retrievedIds   = array();

            $userRepository     = $em->getRepository( $this->getContainer()->getParameter('user_base_class') );
            $syskUserRepository = $em->getRepository( 'SYSKAlertBundle:SyskUser' );
            $serviceName        = $this->getContainer()->getParameter('user_information_service');
            $baseUserArray      = $this->getContainer()->get( $serviceName )->retrieveSYSKUsersInformation();

            $allUsers = $userRepository->findAll();
            foreach ($baseUserArray as $key => $bua) {
                $retrievedIds[] = $bua['id'];
            }

            foreach ( $allUsers as $baseUser ) {
                if( $retrievedIds && count( $retrievedIds ) > 0){
                    if( in_array( $baseUser->getId(), $retrievedIds ) ){
                         // Check for base irritant already in DB
                        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $baseUser->getId() ) );

                        if( !$syskUser )
                        {
                            //Entry does not exists
                            $syskUser = new SyskUser();
                            $syskUser->setUserId( $baseUser->getId() );
                        }

                        $syskUser->setTokenCount( 0 );
                        $flushBase = true;

                        $em->persist( $syskUser );
                    }
                }else{
                    // Check for base irritant already in DB
                    $syskUser = $syskUserRepository->findOneBy( array( "userId" => $baseUser->getId() ) );

                    if( !$syskUser )
                    {
                        //Entry does not exists
                        $syskUser = new SyskUser();
                        $syskUser->setUserId( $baseUser->getId() );
                    }

                    $syskUser->setTokenCount( 0 );
                    $flushBase = true;

                    $em->persist( $syskUser );
                }
            }

            if( $flushBase ){
                $em->flush();
                $output->writeln("<info>Saved Entries</info>");
            }
            
        } catch (\Exception $e) {
            $output->writeln("<info>".$e->getMessage()."</info>");
        }
    }
}
