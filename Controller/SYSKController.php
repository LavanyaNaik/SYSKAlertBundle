<?php

namespace SYSK\AlertBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

use SYSK\AlertBundle\Entity\SyskMessage;


class SYSKController extends Controller
{
	public function indexOutboxAction( Request $request )
    {
    	$em = $this->getDoctrine()->getManager();
    	$params = array();
    	$params["widgetTitle"]  = "SYSK: Envoyer un Message";

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();

    		$serviceName 	= $this->getParameter('user_information_service');
    		$baseUserArray 	= $this->get( $serviceName )->retrieveSYSKUsersInformation();

    		$syskUserRepository 	= $em->getRepository('SYSKAlertBundle:SyskUser');
    		$syskIrritantRepository = $em->getRepository('SYSKAlertBundle:SyskIrritant');

    		$syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

	        if( !$syskUser ){
	        	$params["status"] 	= "error";
	        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
	        													array( "messages" => array( "Aucun utilisateur trouvé." ) ) );
	        }else{
	        	$syskUsersArr = array();
	    		foreach ( $baseUserArray as $key => $baseUser ) {
	    			if( $baseUser["id"] == $syskUser->getUserId() )
	    				continue;

	    			$srvUser = $syskUserRepository->findOneBy( array( "userId" => $baseUser["id"] ) );
	    			if( !$srvUser )
	    				continue;
	    			$element = array();

	    			$syskUsersArr[ $srvUser->getId() ] = $baseUser["lastName"]." ".$baseUser["firstName"];
	    		}

	    		$typesArray = array();
	    		$typesArray[ SyskMessage::TYPE_POSITIVE ] = "Donner un feedback positif";
	    		$typesArray[ SyskMessage::TYPE_NEGATIVE ] = "Something You Should Know";

	    		$irritants 	= array();
	    		$irritantMessages 	= array();
	    		foreach ( $syskIrritantRepository->findByDeleted( false ) as $irr ) {
	    			$irritants[ $irr->getId() ] = $irr->getIrritantMessage();
	    			$irritantMessages[ $irr->getId() ] = $irr->getIrritantMessage();
	    		}


	    		$form = $this->createFormBuilder()
	    					 ->add( 'syskUser', 'choice', array( 	"choices" 	=> $syskUsersArr, 
			    													"required" 	=> true,
			    													"multiple" 	=> false,
			    													"expanded" 	=> true,
			    													"attr" 		=> array( 
			    															"onChange" => 'syskSenderNextStep( "syskUserChoice", "syskTypeChoice")'
			    																) ) )
	    					 ->add( 'syskType', 'choice', array( 	"choices" 	=> $typesArray, 
			    													"required" 	=> true,
			    													"multiple" 	=> false,
			    													"expanded" 	=> true,
			    													"data" 		=> SyskMessage::TYPE_POSITIVE
			    													) )
	    					 ->add( 'positifComment', 'textarea', array( "required" => true,
	    					 											 "label" 	=> "Donne un avis positif à un de tes collègues. (chaque SYSK te donne droit à 1 token)" ) )
	    					 ->add( 'negatifComment', 'choice', array( 	"choices" 	=> $irritants, 
	    					 											"label" 	=> "Une remarque et un truc énervant d'un de tes collègues (chaque SYSK te donne droit à 1 token)." ,
			    														"required" 	=> false,
			    														"multiple" 	=> false,
			    														"expanded" 	=> false ) )
	    					 ->getForm();

    			$params["status"] 		= "success";
				$params["tokenCount"] 	= $syskUser->getTokenCount();
				$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Outbox/index-sender-base.html.twig', 
															array( 	"syskUsers" => $baseUserArray,
																	"syskUser" 	=> $syskUser,
																	"irritants" => $irritantMessages,
																	"form" 		=> $form->createView() ) );
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}
        
        return new JsonResponse( $params );
    }

    public function sendSYSKMessageAction( Request $request ){
    	$em = $this->getDoctrine()->getManager();
    	$params = array();
    	$params["widgetTitle"]  = "SYSK: Envoyer un Message";

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();

    		$syskUserRepository 	= $em->getRepository('SYSKAlertBundle:SyskUser');
    		$syskIrritantRepository = $em->getRepository('SYSKAlertBundle:SyskIrritant');

    		$syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

	        if( !$syskUser ){
	        	$params["status"] 	= "error";
	        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
	        													array( "messages" => array( "Aucun utilisateur trouvé." ) ) );
	        }else{
	        	$serviceName 	= $this->getParameter('user_information_service');
    			$baseUserArray 	= $this->get( $serviceName )->retrieveSYSKUsersInformation();

    			$syskUsersArr 		= array();
    			$syskUsersObjects 	= array();

	    		foreach ( $baseUserArray as $key => $baseUser ) {
	    			if( $baseUser["id"] == $syskUser->getUserId() )
	    				continue;

	    			$srvUser = $syskUserRepository->findOneBy( array( "userId" => $baseUser["id"] ) );
	    			if( !$srvUser )
	    				continue;
	    			$element = array();

	    			$syskUsersArr[ $srvUser->getId() ] 		= $baseUser["firstName"]." ".$baseUser["lastName"];
	    			$syskUsersObjects[ $srvUser->getId() ] 	= $srvUser;
	    		}

	    		$typesArray = array();
	    		$typesArray[ SyskMessage::TYPE_POSITIVE ] = "Donner un feedback positif";
	    		$typesArray[ SyskMessage::TYPE_NEGATIVE ] = "Something You Should Know";

	    		$irritants 		 = array();
	    		$irritantObjects = array();
	    		foreach ( $syskIrritantRepository->findByDeleted( false ) as $irr ) {
	    			$irritants[ $irr->getId() ] 		= $irr->getIrritantMessage();
	    			$irritantObjects[ $irr->getId() ] 	= $irr;
	    		}

	    		$form = $this->createFormBuilder()
	    					 ->add( 'syskUser', 'choice', array( 	"choices" 	=> $syskUsersArr, 
			    													"required" 	=> true,
			    													"multiple" 	=> false,
			    													"expanded" 	=> true,
			    													"attr" 		=> array( 
			    															"onChange" => 'syskSenderNextStep( "syskUserChoice", "syskTypeChoice")'
			    																) ) )
	    					 ->add( 'syskType', 'choice', array( 	"choices" 	=> $typesArray, 
			    													"required" 	=> true,
			    													"multiple" 	=> false,
			    													"expanded" 	=> true,
			    													"data" 		=> SyskMessage::TYPE_POSITIVE
			    													) )
	    					 ->add( 'positifComment', 'textarea', array( "required" => true,
	    					 											 "label" 	=> "Envoyer un commentaire positif vers un collaborateur. Plus, chaque SYSK vous donne 1 token!!!") )
	    					 ->add( 'negatifComment', 'choice', array( 	"choices" 	=> $irritants, 
	    					 											"label" 	=> "Irrité d'une actitude d'un collaborateur? Choissisez un message de la liste, et envoyer le. Plus, chaque SYSK vous donne 1 token!!!" ,
			    														"required" 	=> false,
			    														"multiple" 	=> false,
			    														"expanded" 	=> false ) )
	    					 ->getForm();

	    		$form->submit( $request );

	    		if( $form->isValid() ){
	    			$data = $form->getData();

	    			// Create Message 
	    			$message = new SyskMessage();
	    			$message->setSender( $syskUser );
	    			$message->setReceiver( $syskUsersObjects[ $data[ "syskUser" ] ] );
	    			$typeFlag = 0;

	    			if( $data[ "syskType" ] == SyskMessage::TYPE_NEGATIVE ){
	    				$message->setMessageType( SyskMessage::TYPE_NEGATIVE );
	    				$message->setIrritantId( $irritantObjects[ $data[ "negatifComment" ] ] );
	    				$typeFlag--;
	    			}else{
	    				$message->setMessageType( SyskMessage::TYPE_POSITIVE );
	    				$message->setMessageText( $data[ "positifComment" ] );
	    				$typeFlag++;
	    			}

	    			// Add tokens and ration
	    			$syskUser->setTokenCount( $syskUser->getTokenCount() + 1 );

	    			$em->persist( $message );
	    			$em->persist( $syskUser );
	    			$em->flush();
	    		}

    			$params["status"] 		= "success";
    			$params["userTokens"] 	= $syskUser->getTokenCount();
				$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Outbox/index-sender-messageSent.html.twig',
        													array( "receiverName" => $syskUsersArr[ $data[ "syskUser" ] ] ) );


	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}
        
        return new JsonResponse( $params );
    }

    public function indexInboxAction()
    {
    	$em 	= $this->getDoctrine()->getManager();
    	$params = array();
    	$params["widgetTitle"]  = "SYSK: Inbox";

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();
	        $syskUserRepository = $em->getRepository('SYSKAlertBundle:SyskUser');
	        $syskMsgRepository  = $em->getRepository('SYSKAlertBundle:SyskMessage');

	        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

	        if( !$syskUser ){
	        	$params["status"] 	= "error";
	        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
	        													array( "messages" => array( "Aucun utilisateur trouvé." ) ) );
	        }else{
	        	
		        $params["status"] 		= "success";
		        $params["tokenCount"] 	= $syskUser->getTokenCount();

		        if( $syskUser->getTokenCount() <= 0 ){
		        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-insuffissientTokens.html.twig' );
		        }elseif ( $syskUser->getTokenCount() >= 0 ) {
		        	$params["responseHtml"] = $this->renderView( 'SYSKAlertBundle:Messages:Default/index-message-confirmation.html.twig', 
		        													array( "tokens" => $syskUser->getTokenCount() ) );
		        }
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}
        
        return new JsonResponse( $params );
    }

    public function getRandomSYSKMessageAction(){
    	$em 	= $this->getDoctrine()->getManager();
    	$params = array();
    	$params["widgetTitle"]  = "SYSK: Inbox";

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();
	        $syskUserRepository = $em->getRepository('SYSKAlertBundle:SyskUser');
	        $syskMsgRepository  = $em->getRepository('SYSKAlertBundle:SyskMessage');

	        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

	        if( !$syskUser ){
	        	$params["status"] 	= "error";
	        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
	        													array( "messages" => array( "Aucun utilisateur trouvé." ) ) );
	        }else{
	        	$retrieveMessage = false;
		        // Get all SYSK messages
		        $syskMsgs = $syskMsgRepository->findBy( array( 	"receiver" 	=> $syskUser,
		        												"status" 	=> SyskMessage::STATUS_NOTREAD,
		        												"deleted"   => FALSE
		        										));
		        $syskMsgsCount = count( $syskMsgs );

		        // Retrive random index if exists
		        if( $syskMsgsCount <= 0 ){
					$params["status"] 	= "error";
					$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
																array( "messages" => array( "Aucune Message Disponible. Aucun token dépensé" ) ) );
		        }elseif ( $syskMsgsCount == 1 ){
		        	$idx = 1;
		        	$retrieveMessage = true;
		       	}elseif ( $syskMsgsCount > 1 ){
		       		$idx = rand( 1 , $syskMsgsCount );
		       		$retrieveMessage = true;
		       	}

		       	if( $retrieveMessage ){
		       		$params["status"] 	= "success";
		       		$syskMessage = $syskMsgs[ $idx - 1 ];

		       		// Change Selected Message Status
		       		$syskMessage->setStatus( SyskMessage::STATUS_READ );
		       		$em->persist( $syskMessage );

		       		// Remove one token
		       		$syskUser->setTokenCount( $syskUser->getTokenCount()-1 );
		       		$em->persist( $syskUser );
		       		$em->flush();

		       		// Remove 1 message from counter
		       		$syskMsgsCount = $syskMsgsCount-1;

		       		if( $syskMsgsCount <= 0 ){
		       			// No new messages
		       			$html = $this->renderView('SYSKAlertBundle:Widget:Block/sysk-nonereceived-block.html.twig');
		       		}else{
		       			// New messages
		       			$html = $this->renderView('SYSKAlertBundle:Widget:Block/sysk-received-block.html.twig');
		       		}

		       		// Retrieve User Tokens & alert button
		       		$params["userTokens"] 	= $syskUser->getTokenCount();
		       		$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Inbox/index-received-random.html.twig', 
		       									array( "message" => $syskMessage ) );
		       		$params["receivedHtml"] = $html;
		       	}
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}

    	return new JsonResponse( $params );
    }

    public function getAllSYSKMessagesAction(){
    	$em 	= $this->getDoctrine()->getManager();
    	$params = array();
    	$params["widgetTitle"]  = "SYSK: Inbox";

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();
	        $syskUserRepository = $em->getRepository('SYSKAlertBundle:SyskUser');
	        $syskMsgRepository  = $em->getRepository('SYSKAlertBundle:SyskMessage');

	        $syskUser = $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

	        if( !$syskUser ){
	        	$params["status"] 	= "error";
	        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
	        													array( "messages" => array( "Aucun utilisateur trouvé." ) ) );
	        }else{
	        	$retrieveMessage = false;
		        // Get all SYSK messages
		        $syskMsgs = $syskMsgRepository->findBy( array( 	"receiver" 	=> $syskUser,
		        												"status" 	=> SyskMessage::STATUS_NOTREAD,
		        												"deleted"   => FALSE
		        										));
		        $syskMsgsCount = count( $syskMsgs );

		        // Retrive random index if exists
		        if( $syskMsgsCount <= 0 ){
					$params["status"] 	= "error";
					$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
																array( "messages" => array( "Aucune Message Disponible. Aucun token dépensé" ) ) );
		        }elseif ( $syskMsgsCount >= 1 ){
		       		$retrieveMessage = true;
		       	}

		       	if( $retrieveMessage ){
		       		$params["status"] 	= "success";
		       		$arrayMessages = array();
		       		$arrayMessages[ SyskMessage::TYPE_POSITIVE ] = array();
		       		$arrayMessages[ SyskMessage::TYPE_NEGATIVE ] = array();

		       		foreach ($syskMsgs as $syskMessage ) {
		       			// Change Selected Message Status
			       		$syskMessage->setStatus( SyskMessage::STATUS_READ );
			       		$em->persist( $syskMessage );

			       		// Order selected message
			       		if( $syskMessage->getMessageType() == SyskMessage::TYPE_POSITIVE ){
			       			$arrayMessages[ SyskMessage::TYPE_POSITIVE ][] = $syskMessage;
			       		}else{
			       			$arrayMessages[ SyskMessage::TYPE_NEGATIVE ][] = $syskMessage;
			       		}
		       		}

		       		// Remove 3 tokens
		       		$syskUser->setTokenCount( $syskUser->getTokenCount()-3 );
		       		$em->persist( $syskUser );
		       		$em->flush();

		       		// Retrieve User Tokens & alert button
		       		$params["userTokens"] 	= $syskUser->getTokenCount();
		       		$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Inbox/index-received-all.html.twig', 
		       									array( "arrayMessages" => $arrayMessages ) );
		       		$params["receivedHtml"] = $this->renderView('SYSKAlertBundle:Widget:Block/sysk-nonereceived-block.html.twig');
		       	}
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}

    	return new JsonResponse( $params );
    }

    public function validateSYSKReceiverRequestAction( Request $request ){
    	$em 	= $this->getDoctrine()->getManager();
    	$params = array();

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();
	        $syskUserRepository = $em->getRepository('SYSKAlertBundle:SyskUser');
	        $syskMsgRepository  = $em->getRepository('SYSKAlertBundle:SyskMessage');

	        $syskUser 		= $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );
	        $syskReceiver 	= $syskUserRepository->find( $request->get( "requestedId" ) );

	        if( !$syskUser || !$syskReceiver ){
	        	$params["status"] 	= "error";
	        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
	        													array( "messages" => array( "Aucun utilisateur trouvé." ) ) );
	        }else{
	        	$params["status"] 	= "success";

	        	$syskMsgs = $syskMsgRepository->findBy( array( 	"sender" 	=> $syskUser,	
	        													"receiver" 	=> $syskReceiver,
		        												"deleted"   => FALSE
		        										));
	        	if( count( $syskMsgs ) == 0 ){
	        		$params["allowed"] 	= false;
	        	}else{
	        		$now = new \DateTime( "NOW" );
	        		$fom = new \DateTime( $now->format("y-m-01") );
	        		$lom = new \DateTime( $now->format("y-m-t") );
	        		$pnRatio = 0;

	        		foreach ($syskMsgs as $key => $message ) {
	        			if( $message->getCreatedAt() >= $fom && $message->getCreatedAt() <= $lom ){
	        				if( $message->getMessageType() == SyskMessage::TYPE_POSITIVE ){
	        					$pnRatio++;
	        				}else{
	        					$pnRatio--;
	        				}
	        			}
	        		}

	        		if( $pnRatio > 0 ){
	        			$params["allowed"] 	= true;
	        		}else{
	        			$params["allowed"] 	= false;
	        		}
	        	}
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}

    	return new JsonResponse( $params );
    }

    public function retrieveReadInboxPageAction( Request $request, $page ){
    	$em 		= $this->getDoctrine()->getManager();
    	$pageMax 	= 20;
    	$params 	= array();
    	$syskMessages = array();

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();
	        $syskUserRepository = $em->getRepository('SYSKAlertBundle:SyskUser');
	        $syskMsgRepository  = $em->getRepository('SYSKAlertBundle:SyskMessage');

	        $syskUser 		= $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

	        if( $syskUser )
	        {
	            $syskMessagesDB = $syskMsgRepository->findBy(array( "receiver"  => $syskUser,
	                                                                "status"    => SyskMessage::STATUS_READ,
	                                                                "deleted"   => false ),
	                                                        array(  "createdAt" => "DESC" ),
	                                                        $pageMax, 
	                                                        ($page-1)*$pageMax );

	            $allSyskMessagesDB  = $syskMsgRepository->findBy(array( "receiver"  => $syskUser,
	                                                                    "status"    => SyskMessage::STATUS_READ,
	                                                                    "deleted"   => false ) );

	            foreach ( $syskMessagesDB as $messDB ) {
	                $element = array();
	                if( $messDB->getMessageType() == SyskMessage::TYPE_POSITIVE ){
	                    $element["message"] = $messDB->getMessageText();
	                    $element["type"] = "Feedback positif";
	                }elseif( $messDB->getMessageType() == SyskMessage::TYPE_NEGATIVE ){
	                    $element["message"] = $messDB->getIrritantId()->getIrritantMessage();
	                    $element["type"] = "Something You Should Know";
	                }else{
	                    continue;
	                }
	                
	                $element["created"] = $messDB->getCreatedAt()->format("d/m/Y");

	                $syskMessages[] = $element;
	            }

	            if( count( $allSyskMessagesDB ) > $pageMax ){
	                $paginationMax = floor( count($allSyskMessagesDB)/$pageMax) + 
	                                 ( ( count($allSyskMessagesDB)%$pageMax == 0 )? 0 : 1 ) ;
	            }
	        }

	        $templateName = 'SYSKAlertBundle:Widget:Block/sysk-historyTable-block.html.twig';
	        $options = array( 	"syskMessages"  => $syskMessages,
	            				"empty_message" => "Aucun message trouvé.",
	            				"paginationMax" => $paginationMax,
	            				"actualPage"    => $page );

	        $params["status"] 		= "success";
	        $params["responseHtml"] = $this->renderView( $templateName, $options );
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}

    	return new JsonResponse( $params );
    }
}
