<?php


include_once "RestUtils.php";
include_once "MessageHandlerUtils.php";
$data = RestUtils::processRequest();

switch(strtolower($data->getMethod()))
{
    case 'get':
	    switch(strtolower($data->getMsg()))
        {
            case 'testGet':
                MessageHandlerUtils::handlePostAndGetTest('get');
                break;
            case 'getSearchVenues':
                MessageHandlerUtils::handleGetSearchVenues();
                break;
            case 'getRecentVenues':
                MessageHandlerUtils::handleGetRecentVenues();
                break;
            case 'getNearVenues':
                MessageHandlerUtils::handleGetNearVenues();
                break;
            case 'getTrendingVenues':
                MessageHandlerUtils::handleGetTrendingVenues();
                break;
            case 'getVenueInfo':
                MessageHandlerUtils::handleGetVenueInfo();
                break;
            case 'getConnect':
                MessageHandlerUtils::handleGetConnect();
                break;
            case 'getBuzz':
                MessageHandlerUtils::handleGetBuzz();
                break;
            case 'getConnectInvites':
                MessageHandlerUtils::handleGetConnectInvites();
                break;
            case 'getAvailableConnections':
                MessageHandlerUtils::handleGetAvailableConnections();
                break;
            case 'getConnectConversation':
                MessageHandlerUtils::handleGetConnectConversation();
                break;
            case 'getUserInfo':
                MessageHandlerUtils::handleGetUserInfo();
                break;
	    }
        
	case 'post':
	    switch(strtolower($data->getMsg()))
        {
            case 'testPost':
            	//print "in testPost";
                MessageHandlerUtils::handlePostAndGetTest('post');
                break;
            case 'postNewUser':   
                MessageHandlerUtils::handlePostNewUser($data->getRequestVars());
                break;
            case 'postUpdateDNA':
                MessageHandlerUtils::handlePostUpdateDNA();
                break;
            case 'postUpVote':
                MessageHandlerUtils::handlePostUpVote();
                break;
            case 'postDownVote':
                MessageHandlerUtils::handlePostDownVote();
                break;
            case 'postRemoveTrendingVenue':
                MessageHandlerUtils::handlePostRemoveTrendingVenue();
                break;
            case 'postCheckIn':
                MessageHandlerUtils::handlePostCheckIn();
                break;
            case 'postConnectStatus':
                handlePostConnectStatus();
                break;
            case 'postChangeMyAvailability':
                MessageHandlerUtils::handlePostChangeMyAvailability();
                break;
        }
        
} 
?>