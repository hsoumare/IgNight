<?php

require("neo4jphp.phar");

use Everyman\Neo4j\Client,
    Everyman\Neo4j\Index\NodeIndex,
    Everyman\Neo4j\Index\RelationshipIndex;

class IgnightVenue
{
	private static $client = null;
	private static $venueIndx = null;
	private static $upVoteIndx = null;
	private static $checkInIndx = null;

        function __construct() {
		try {
		 	$transport = new Everyman\Neo4j\Transport("localhost", 7474);
                        self::$client = new Client($transport);
                        if (self::$client == null)
                                echo "Failed to establish connection to DB";

			self::$venueIndx = new NodeIndex(self::$client, 'venue');
			self::$venueIndx->save();

			self::$upVoteIndx = new RelationshipIndex(self::$client, 'upVote');
			self::$upVoteIndx->save();

			self::$checkInIndx = new RelationshipIndex(self::$client, 'checkIn');
			self::$checkInIndx->save(); 

		} catch (Exception $e) {
			echo "Message: ".$e->getMessage();
		}
        }
	
	private function getNode($id) {
		return self::$client->getNode($id);
	}

	private function makeNode() {
		return self::$client->makeNode();
	}

	public function addVenue($venueName, $address, $number) {
		$match = self::$venueIndx->findOne('name', $venueName);
		if ($match != null)
			return null;

		$venue = $this->makeNode();
		$venue->setProperty("NodeType", "VENUE")
		      ->setProperty("name", $venueName)
		      ->setProperty("address", $address)
		      ->setProperty("number", $number)->save();

		self::$venueIndx->add($venue, 'name', $venueName);
		return $venue->getId();
	}

	public function upVoteVenue($venueID, $userID) {
		$venue = $this->getNode($venueID);
		$user = $this->getNode($userID);
		$venueName = $venue->getProperty('name');
		$userName = $user->getProperty('username');
		if ($venueName == null OR $userName == null)
			return;
		$match = self::$upVoteIndx->findOne('user', $venueName.'_'.$userName);
		if ($match == null) {
			$rel = $venue->relateTo($user, 'UP_VOTED');
			self::$upVoteIndx->add($rel, 'user', $venueName.'_'.$userName);
		}
	}

	public function removeUpVote($venueID, $userID) {
		$venue = $this->getNode($venueID);
                $user = $this->getNode($userID);
                $venueName = $venue->getProperty('name');
                $userName = $user->getProperty('username');
                if ($venueName == null OR $userName == null)
                        return;
                $match = self::$upVoteIndx->findOne('user', $venueName.'_'.$userName);
		if ($match != null)
			$match->delete();
	}

	public function getVenueInformation($venueID, $userID) {
		$venue = $this->getNode($venueID);
		$venueInfo = array();
		$venueInfo['name'] = $venue->getProperty('name');
		$venueInfo['address'] = $venue->getProperty('address');
		$venueInfo['number'] = $venue->getPropety('number');
		$venueInfo['userTotalDNA'] = $this->getTotalDNAAtVenueForUser($venueID, $userID);
		return $venueInfo;
	}

	public function getUpVoteUsers($venueID) {
		$venue = $this->getNode($venueID);
		$venueName = $venue->getProperty("name");
		$matches = self::$upVoteIndx->query('user:$venueName-*');
		$upVoteUsers = array();
		foreach ($matches as $match) {
			array_push($upVoteUsers, $match->getEndNode()->getId());
		}
		return $upVoteUsers;
	}

	public function checkIn($venueID, $userID) {
		$venue = $this->getNode($venueID);
                $user = $this->getNode($userID);
                $venueName = $venue->getProperty('name');
                $userName = $user->getProperty('username');
                if ($venueName == null OR $userName == null)
                        return;
		$match = self::$checkInIndx->findOne('user', $venueName.'_'.$userName);
		if ($match == null) {
			$rel = $venue->relateTo($user, 'CHECK_IN');
			self::$checkInIndx->add($rel, 'user', $venueName.'_'.$userName);
		}
	}

	public function removeCheckIn($venueID, $userID) {
                $venue = $this->getNode($venueID);
                $user = $this->getNode($userID);
                $venueName = $venue->getProperty('name');
                $userName = $user->getProperty('username');
                if ($venueName == null OR $userName == null)
                        return;
                $match = self::$checkInIndx->findOne('user', $venueName.'_'.$userName);
                if ($match != null)
                        $match->delete();
        }
	
	public function getCheckInUsers($venueID) {
                $venue = $this->getNode($venueID);
                $venueName = $venue->getProperty("name");
                $matches = self::$upVoteIndx->query('user:$venueName-*');
                $checkInUsers = array();
                foreach ($matches as $match) {
                        array_push($checkInUsers, $match->getEndNode()->getId());
                }
                return $checkInUsers;
        }

	public function getTotalDNAAtVenueForUser($venueID, $userID) {
		$upVoteUsers = $this->getUpVoteUsers($venueID);
		$checkInUsers = $this->getCheckInUsers($venueID);
		$count = 0;
		$IGUser = new IgnightUser();
		foreach($upVoteUsers as $user) {
			$count += $IGUser->getUserToUserDNA($user, $userID);
		}

		foreach($checkInUsers as $user) {
			$count += $IGUser->getUserToUserDNA($user, $userID);
		}

		return $count;
	}

	public function connectToVenue($venueID, $userID) {
		$venue = $this->getNode($venueID);
                $user = $this->getNode($userID);
		$venueName = $venue->getProperty('name');
                $userName = $user->getProperty('username');
                if ($venueName == null OR $userName == null)
                        return;
                $match = self::$checkInIndx->findOne('user', $venueName.'_'.$userName);
                if ($match != null) {
			$match->setProperty("connected", TRUE);
			return TRUE;;
		}
		return 	FALSE;
	}

	public function makeAvailable($venueID, $userID) {
		$venue = $this->getNode($venueID);
                $user = $this->getNode($userID);
                $venueName = $venue->getProperty('name');
                $userName = $user->getProperty('username');
                if ($venueName == null OR $userName == null)
                        return;
                $match = self::$checkInIndx->findOne('user', $venueName.'_'.$userName);
                if ($match != null) {
                        $match->setProperty("available", TRUE);
			return TRUE;
		}
		return FALSE;

		//TODO: publish to rest of Available Users 
	}
}

?>
