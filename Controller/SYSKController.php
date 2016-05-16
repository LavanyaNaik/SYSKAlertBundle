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
	    		$baseData 	= NULL;
	    		$irritantMessages 	= array();
	    		foreach ( $syskIrritantRepository->findByDeleted( false ) as $irr ) {
	    			$irritants[ $irr->getId() ] 		= $irr->getIrritantMessage();
	    			if( $baseData == NULL ){ $baseData = $irr->getId(); }
	    			$irritantMessages[ $irr->getId() ]	= $irr->getIrritantMessage();
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
	    					 ->add( 'positifComment', 'textarea', array( "required" 	=> true,
	    					 											 "label" 		=> "Donne un avis positif à un de tes collègues. (chaque SYSK te donne droit à 1 token)" ) )
	    					 ->add( 'negatifComment', 'choice', array( 	"choices" 		=> $irritants,
	    					 											"placeholder" 	=> FALSE,
	    					 											"empty_data" 	=> $baseData, 
	    					 											"label" 		=> "Une remarque et un truc énervant d'un de tes collègues (chaque SYSK te donne droit à 1 token)." ,
			    														"required" 		=> false,
			    														"multiple" 		=> false,
			    														"expanded" 		=> false ) )
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
	    		$baseData 		 = NULL;

	    		foreach ( $syskIrritantRepository->findByDeleted( false ) as $irr ) {
	    			$irritants[ $irr->getId() ] 		= $irr->getIrritantMessage();
	    			if( $baseData == NULL ){ $baseData = $irr->getId(); }
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
	    					 ->add( 'positifComment', 'textarea', array( "required" 	=> true,
	    					 											 "label" 		=> "Envoyer un commentaire positif vers un collaborateur. Plus, chaque SYSK vous donne 1 token!!!") )
	    					 ->add( 'negatifComment', 'choice', array( 	"choices" 		=> $irritants,
	    					 											"placeholder" 	=> FALSE,
	    					 											"empty_data" 	=> $baseData, 
	    					 											"label" 		=> "Irrité d'une actitude d'un collaborateur? Choissisez un message de la liste, et envoyer le. Plus, chaque SYSK vous donne 1 token!!!" ,
			    														"required" 		=> false,
			    														"multiple" 		=> false,
			    														"expanded" 		=> false ) )
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
	    				$message->setMessageText( nl2br($data[ "positifComment" ]) );
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
			       			if( $syskMessage->getIrritantId() == NULL )
			       				continue;
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
	        		$fom = new \DateTime( $now->format("y-m-01 00:00:00") );
	        		$lom = new \DateTime( date( "Y-m-t", strtotime( $now->format("y-m-01 00:00:00") ) )." 23:59:59" );
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
	        		$params["ratio"] = $pnRatio;
	        	}
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}

    	return new JsonResponse( $params );
    }

    // route : sysk_alert_retrieveInboxPage
    public function retrieveReadInboxPageAction( Request $request, $page ){
    	$em 		= $this->getDoctrine()->getManager();
    	$pageMax 	= $this->getParameter('sysk_base_table_max');
    	$params 	= array();
    	$syskMessages = array();
    	$paginationMax = 1;

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();
    		$form = $this->createForm("sysk_alert_search");
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

	            if ( $request->isMethod('post') )
	            {
	            	$form->submit( $request );
	            }

	            if( $form->isValid() )
	            {
	            	$data = $form->getData();

	            	$varType = $data["type"];
	            	$varStartDate 	= $data["startDt"];
	            	$varEndDate 	= $data["endDt"];

	            	if( ($varStartDate != NULL && $varEndDate != NULL  && $varStartDate <= $varEndDate ) || 
	            		($varStartDate == NULL && $varEndDate == NULL) )
	            	{
		            	foreach ( $syskMessagesDB as $messDB ) 
		            	{
			                $element = array();
			                if( $messDB->getMessageType() == SyskMessage::TYPE_POSITIVE ){
			                    $element["message"] = $messDB->getMessageText();
			                    $element["type"] = "Feedback positif";
			                }elseif( $messDB->getMessageType() == SyskMessage::TYPE_NEGATIVE ){
			                	if( $messDB->getIrritantId() === NULL )
			                		continue;
			                    $element["message"] = $messDB->getIrritantId()->getIrritantMessage();
			                    $element["type"] = "Something You Should Know";
			                }else{
			                    continue;
			                }
			                
			                if( $varType != NULL ){
			                	if( $messDB->getMessageType() != $varType ){
			                		continue;
			                	}
			                }

			                if( $varStartDate != NULL && $varEndDate != NULL ){
			                	$formStartDate 	= new \DateTime( $varStartDate->format("Y-m-d")." 00:00:00" );
								$formEndDate 	= new \DateTime( $varEndDate->format("Y-m-d")." 23:59:59" );

								if( $messDB->getCreatedAt() < $formStartDate ||
									$messDB->getCreatedAt() > $formEndDate ){
									continue;
								}
			                }elseif( $varStartDate == NULL && $varEndDate != NULL ){
			                	$formEndDate 	= new \DateTime( $varEndDate->format("Y-m-d")." 23:59:59" );
			                	if( $messDB->getCreatedAt() > $formEndDate ){
			                		continue;
			                	}
			                }elseif( $varStartDate != NULL && $varEndDate == NULL ){
			                	$formStartDate 	= new \DateTime( $varStartDate->format("Y-m-d")." 00:00:00" );
			                	if( $messDB->getCreatedAt() < $formStartDate ){
			                		continue;
			                	}
			                }

			                $element["created"] = $messDB->getCreatedAt()->format("d/m/Y");

			                $syskMessages[] = $element;
			            }

			            if( count( $allSyskMessagesDB ) > $pageMax ){
			                $paginationMax = floor( count($allSyskMessagesDB)/$pageMax) + 
			                                 ( ( count($allSyskMessagesDB)%$pageMax == 0 )? 0 : 1 ) ;
			            }

			            $templateName = 'SYSKAlertBundle:Widget:Block/sysk-historyTable-block.html.twig';
				        $options = array( 	"syskMessages"  => $syskMessages,
				            				"empty_message" => "Aucun message trouvé.",
				            				"paginationMax" => $paginationMax,
				            				"actualPage"    => $page );

				        $params["status"] 		= "success";
				        $params["responseHtml"] = $this->renderView( $templateName, $options );
				    }else{
	            		$params["status"] 	= "error";
	            		if( $varStartDate == NULL ){
				    		$params["responseHtml"] = "Filtre Invalide. Date de début requis.";
				    	}elseif( $varEndDate == NULL ) {
				    		$params["responseHtml"] = "Filtre Invalide. Date de fin requis.";
				    	}elseif( $varStartDate > $varEndDate ){
				    		$params["responseHtml"] = "Filtre Invalide. Date de fin doit être inférieure a la date de début.";
				    	}
	            	}
	            }else{
	            	$params["status"] 	= "error";
	            	$params["responseHtml"] = "Filtre Invalide";
	            }
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}

    	return new JsonResponse( $params );
    }

    // route : sysk_alert_retrieveManagerInboxPage
    public function retrieveManagerInboxPageAction( Request $request, $page ){
    	$em 		= $this->getDoctrine()->getManager();
    	$pageMax 	= $this->getParameter('sysk_base_table_max');
    	$paginationMax = 1;
    	$params 	= array();
    	$syskMessages = array();

    	// Retrieve the user based on the security Context
    	try {
    		$user = $this->get('security.context')->getToken()->getUser();
    		$form = $this->createForm("sysk_alert_search_manager");

	        $syskUserRepository = $em->getRepository('SYSKAlertBundle:SyskUser');
	        $syskMsgRepository  = $em->getRepository('SYSKAlertBundle:SyskMessage');
	        $bridgeServiceName 	= $this->getParameter('user_information_service');

	        $bridgeService 	= $this->get( $bridgeServiceName );
	        $syskUser 		= $syskUserRepository->findOneBy( array( "userId" => $user->getId() ) );

	        if( $syskUser )
	        {
	        	$result     = array();
                $children   = $bridgeService->retrieveSYSKManagerUsers( $user->getId() );

                foreach ($children as $key => $child) {
                    $result = $this->buildChildEntry( $result, $child );
                }

                $extraHeaders = array();

                if ( $request->isMethod('post') )
	            {
	            	$form->submit( $request );
	            }

	            if( $form->isValid() )
	            {
	            	$data = $form->getData();

	            	$varName = array();
	            	$names = json_decode( $data["name_array"] );
	            	if( $names && $names != "" ){
	            		foreach ( json_decode( $data["name_array"] ) as $key => $ret ) {
		            		$varName[] = strtoupper( $ret );
		            	}
	            	}
	            	
	            	$varType = $data["type"];
	            	$varStartDate 	= $data["startDt"];
	            	$varEndDate 	= $data["endDt"];
	            	
	            	if( ($varStartDate != NULL && $varEndDate != NULL  && $varStartDate <= $varEndDate ) || 
	            		($varStartDate == NULL && $varEndDate == NULL) )
	            	{
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
							    	if( $msgValue->getIrritantId() === NULL )
									continue;
							        $element["message"] = $msgValue->getIrritantId()->getIrritantMessage();
							        $element["type"]    = "Something You Should Know";
							    }else{
							        continue;
							    }

							    if( count($varName) > 0 ){
							    	if( in_array( strtoupper( $element["name"] ), $varName ) === FALSE ){
							    		continue;
							    	}
							    }

							    if( $varType != NULL ){
							    	if( $msgValue->getMessageType() != $varType ){
							    		continue;
							    	}
							    }

							    if( $varStartDate != NULL && $varEndDate != NULL ){
							    	$formStartDate 	= new \DateTime( $varStartDate->format("Y-m-d")." 00:00:00" );
									$formEndDate 	= new \DateTime( $varEndDate->format("Y-m-d")." 23:59:59" );

							    	if( $msgValue->getCreatedAt() < $formStartDate ||
							    		$msgValue->getCreatedAt() > $formEndDate ){
							    		continue;
							    	}
							    }elseif( $varStartDate == NULL && $varEndDate != NULL ){
									$formEndDate 	= new \DateTime( $varEndDate->format("Y-m-d")." 23:59:59" );

							    	if( $msgValue->getCreatedAt() > $formEndDate ){
							    		continue;
							    	}
							    }elseif( $varStartDate != NULL && $varEndDate == NULL ){
							    	$formStartDate 	= new \DateTime( $varStartDate->format("Y-m-d")." 00:00:00" );

							    	if( $msgValue->getCreatedAt() < $formStartDate ){
							    		continue;
							    	}
							    }

							    $element["status"]  = ( $msgValue->getStatus() == SyskMessage::STATUS_READ )? "Lu" : "Non-lu";
							    $element["created"] = $msgValue->getCreatedAt()->format("d/m/Y");
							    $element["extras"]  = $child["extras"];

							    $syskMessages[] = $element;
							}
						}

						if( count( $syskMessages ) > $pageMax ){
				            $paginationMax = floor( count($syskMessages)/$pageMax) + 
				                             ( ( count($syskMessages)%$pageMax == 0 )? 0 : 1 ) ;
				        }

				        $syskMessagesReturn = array();
				        $index = 1;

				        foreach ( $syskMessages as $message ) {
				            if( $index > ($page-1)*$pageMax && $index <= ($page)*$pageMax ){
				                $syskMessagesReturn[] = $message;
				            }
				            $index++;
				        }

		                $templateName = 'SYSKAlertBundle:Widget:Block/sysk-managerTable-block.html.twig';
				        $options = array( 	"syskMessages"  => $syskMessagesReturn,
				        					"extraHeaders"  => $extraHeaders,
				            				"empty_message" => "Aucun message trouvé.",
				            				"paginationMax" => $paginationMax,
				            				"actualPage"    => $page );

				        $params["status"] 		= "success";
				        $params["responseHtml"] = $this->renderView( $templateName, $options );
	            	}else{
	            		$params["status"] 	= "error";
	            		if( $varStartDate == NULL ){
				    		$params["responseHtml"] = "Filtre Invalide. Date de début requis.";
				    	}elseif( $varEndDate == NULL ) {
				    		$params["responseHtml"] = "Filtre Invalide. Date de fin requis.";
				    	}elseif( $varStartDate > $varEndDate ){
				    		$params["responseHtml"] = "Filtre Invalide. Date de fin doit être inférieure a la date de début.";
				    	}
	            	}
	            }else{
	            	$params["status"] 	= "error";
	            	$params["responseHtml"] = "Filtre Invalide";
	            }
	        }else{
	        	$params["status"] 		= "error";
		        $params["responseHtml"] = "Aucun utilisateur trouvé.";
	        }
    	} catch (\Exception $e) {
        	$params["status"] 	= "error";
        	$params["responseHtml"] = $this->renderView('SYSKAlertBundle:Messages:Default/index-message-error.html.twig',
        													array( "messages" => array( $e->getMessage() ) ) );
    	}

    	return new JsonResponse( $params );
    }

    private function buildChildEntry( $result, $child )
    {
    	$em = $this->getDoctrine()->getManager();
        $syskUserRepository = $em->getRepository('SYSKAlertBundle:SyskUser');

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
}
