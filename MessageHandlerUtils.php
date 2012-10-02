<?php

//write methods for handlePostNewUser(), handleGetSearchVenue() etc..
include "IgnightUser.php";

class MessageHandlerUtils
{

    public static function handlePostAndGetTest($method){
        $Body = '<html>'.'Successfully tested the following method' . $method . '</html>';
        RestUtils::sendResponse(200,$Body,'text/html');
    }
    
    
    public static function handlePostNewUser($dataArray){
		
    	print "In handle Post New User... \n	";
    	
    	$firstName = $dataArray['firstName'];
        $lastName = $dataArray['lastName'];
        $userName = $dataArray['username'];
        $password = $dataArray['password'];
        $email = $dataArray['email'];
        $dateOfBirth = $dataArray['dateOfBirth'];
        $gender = $dataArray['gender'];
        $city = $dataArray['city'];
        $state = $dataArray['state'];
        
        
        $newUser = new IgnightUser();
        $addUserStatus = $newUser->addUser($firstName, $lastName, $userName, $password, $email, $dateOfBirth, $gender, $city, $state);
		
        if ($addUserStatus == null){
        	$responseBody = "Username already exists \n";
        	RestUtils::sendResponse(304, $responseBody, $content_type = 'text/html');
		}else{
			$responseBody = "Successfully created user \n";
			RestUtils::sendResponse(200, $responseBody, $content_type = 'text/html');
		}
		
        print "Completed Handle Post New User";

    }
    
    public static function handleUpdateDNA(){

    }
    
    public static function handleGetSearchVenues(){

    }
    
    public static function handleGetRecentVenues(){

    }
    
    public static function handleGetNearVenues(){

    }
    
    public static function handleGetTrendingVenues(){

    }
    
    public static function handlePostDownVote(){

    }
    
    public static function handlePostUpVote(){

    }
    
    public static function handlePostRemoveTrendingVenue(){

    }
    
    public static function handleGetVenueInfo(){

    }
    
    public static function handlePostCheckIn(){

    }
    
    public static function handleGetConnect(){ //Pulls relevant info (if any) for initial connect screen...may not be needed

    }
    
    public static function handleGetBuzz(){

    }

    public static function handlePostConnectStatus(){

    }

    public static function handleGetConnectInvites(){

    }
    
    public static function handleGetAvailableConnections(){

    }

    public static function handlePostChangeMyAvailability(){

    }

    public static function handleGetConnectConversation(){

    }

    public static function handleGetUserInfo(){

    }

}
?>