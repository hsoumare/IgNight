<?php

require_once("phar://neo4jphp.phar");

use Everyman\Neo4j\Client,
    Everyman\Neo4j\Index\NodeIndex,
    Everyman\Neo4j\Index\RelationshipIndex;

class IgnightUser
{
	private static $client = null;
	private static $userIndex = null;
	private static $dnaIndex = null;

        function __construct() {
		try {
			$transport = new Everyman\Neo4j\Transport("localhost", 7474);
			self::$client = new Client($transport);
                        if (self::$client == null)
				echo "Failed to establish connection to DB";

			self::$dnaIndex = new RelationshipIndex(self::$client, 'dna');
			if (! self::$dnaIndex->save())
				echo "Failed to create DNA Index";

			self::$userIndex = new NodeIndex(self::$client, 'user');
			if (! self::$userIndex->save())
				echo "Failed to create User Index";
		} catch (Exception $e) {
			echo "Message: ".$e->getMessage();
		}
        }

	public function getNode($ID) {
		return self::$client->getNode($ID);
	}

	public function makeNode() {
		return self::$client->makeNode();
	}

	/* Add User to Database
	 *	- check if user already exists
	 *	- add to user Index
	 */
	public function addUser($firstName, $lastName, $userName, $password, $email, $dateOfBirth, $gender, $city, $state) {
		$match  = self::$userIndex->findOne('username', $userName);
		if ($match != null){
			return null;
		}else{
		$user = $this->makeNode();
		$user->setProperty("NodeType", "USER")
		     ->setProperty("firstName", $firstName)
		     ->setProperty("lastName", $lastName)
		     ->setProperty("userName", $userName)
		     ->setProperty("password", $password)
		     ->setProperty("email", $email)
		     ->setProperty("dateOfBirth", $dateOfBirth)
		     ->setProperty("gender", $gender)
		     ->setProperty("city", $city)
		     ->setProperty("state", $state)
		     ->save();
		self::$userIndex->add($user, 'username', $userName);
		Print "User ID Created: ".$user->getId()."\n";
		return $user->getId();
			}
	}

	public function removeUser($userID) {
		$user = $this->getNode($userID);
		$user->delete();
	}

	public function getUserInfo($userID) {
		$user = $this->getNode($userID);
		$userInfo = array();
		$userInfo['username'] = $user->getProperty("userName");
		$userInfo['venue1'] = $user->getTopVenuePos($userID, 0);
		$userInfo['venue2'] = $user->getTopVenuePos($userID, 1);
		$userInfo['venue3'] = $user->getTopVenuePos($userID, 2);
		return $userInfo;
	}

        public function login($username, $password) {
		$user = self::$userIndex->findOne('username', $username);
		if ($user == null)
			return null;
		if ($user->getProperty("password") == $password)
			return $user->getId();
		return null;
	}

	// Get UserID value from username otherwise return null
	public function getUserID($username) {
		$match  = self::$userIndex->findOne('username', $username);
		if ($match != null)
			return $match->getId();
		return null;
	}

	public function getProperty($userID, $property) {
		return $this->getNode($userID)->getProperty($property);
	}

	public function updateUserDNAPreferences($userID, $musicArray, $venueArray, $atmosphereArray, $spending) {
		$this->updateTopMusicPositions($userID, $musicArray);
		$this->updateTopVenuePositions($userID, $venueArray);
		$this->updateTopAtmospherePositions($userID, $atmosphereArray);
		$this->updateSpending($userID, $spending);
	}

	public function updateUserToUserDNA($userID1, $userID2, $DNAValue) {
		$user1 = $this->getNode($userID1);
		$user2 = $this->getNode($userID2);
		$user1Name = $user1->getProperty('userName');
		$user2Name = $user2->getProperty('userName');
		$matches = self::$dnaIndex->queryOne('value:$user1Name-$user2Name OR value:$user2Name-$user1Name');
		if ($matches == null) {
			$rel = $user1->realteTo($user2, 'DNA')->setProperty('value', $DNAValue)->save();
			self::$dnaIndex->add($rel, 'value', $user1Name.'-'.$user2Name);
		} else {
			$matches->setProperty('value', $DNAValue);
		}		
	}

	public function getUserToUserDNA($userID1, $userID2) {
		$user1 = $this->getNode($userID1);
                $user2 = $this->getNode($userID2);
                $user1Name = $user1->getProperty('userName');
                $user2Name = $user2->getProperty('userName');
                $matches = self::$dnaIndex->queryOne('value:$user1Name-$user2Name OR value:$user2Name-$user1Name');
		if ($matches == null)
			return 0;
		else
			return $matches->getProperty('value');
	}

	/* 	USER VENUE FUNCTIONS	*/
	/********************************/
	private function addToTopVenue($userID, $venueID, $pos) {
		return $this->getNode($userID)->relateTo(getNode(venueID), 'TOP_VENUES')
					->setProperty("pos", $pos)->save();
	}

	public function getTopVenues($userID) {
		$topVenueRels = $this->getNode($userID)->getRelationships(array('TOP_VENUES'), Relationship::DirectionOut);
		$topVenueIDs = array();
		foreach ($topVenueRels as $venueRel) {
			array_push($topVenueIDs, $venueRel->getEndNode()->getId());
		}
		return $topVenueIDs;
	}

	public function getTopVenuePos($userID, $pos) {
		$topVenueRels = $this->getNode($userID)->getRelationships(array('TOP_VENUES'), Relationship::DirectionOut);
		foreach ($topVenueRels as $venueRel) {
			if ($venueRel->getProperty('pos') == $pos)
				return $venueRel->getEndNode()->getId();
		}
		return null;
	}

	public function updateTopVenuePositions($userID, $venuePosArray) {
		$topVenueRels = $this->getNode($userID)->getRelationships(array('TOP_VENUES'), Relationship::DirectionOut);
		foreach($topVenueRels as $venueRel) {
			$venueRel->delete();
		}

		foreach($venuePosArray as $venuePos => $venueID) {
			$this->addTopTopVenue($userID, $venueID, $venuePos);
		}
	}
	/********************************/


	/* 	USER MUSIC FUNCTIONS	*/
	/********************************/
	private function addToTopMusic($userID, $musicID, $pos) {
                return $this->getNode($userID)->relateTo(getNode(musicID), 'TOP_MUSIC')
                                        ->setProperty("pos", $pos)->save();
        }

        public function getTopMusic($userID) {
                $topMusicRels = $this->getNode($userID)->getRelationships(array('TOP_MUSIC'), Relationship::DirectionOut);
                $topMusicIDs = array();
                foreach ($topMusicRels as $musicRel) {
                        array_push($topMusicIDs, $musicRel->getEndNode()->getId());
                }
                return $topMusicIDs;
        }

	public function getTopMusicSlice($userID, $count) {
                $topMusicRels = $this->getNode($userID)->getRelationships(array('TOP_MUSIC'), Relationship::DirectionOut);
		$topCountArray = array();
		foreach ($topMusicRels as $musicRel) {
			if ($musicRel->getProperty('pos') < $count)
				array_push($topCountArray, $musicRel->getEndNode()->getID());
		}
		return $topCountArray;
	}

	public function getTopMusicPos($userID, $pos) {
                $topMusicRels = $this->getNode($userID)->getRelationships(array('TOP_MUSIC'), Relationship::DirectionOut);
		foreach ($topMusicRels as $musicRel) {
			if ($musicRel->getProperty('pos') == $pos)
				return $musicRel->getEndNode()->getId();
		}
		return null;
	}

        public function updateTopMusicPositions($userID, $musicPosArray) {
                $topMusicRels = $this->getNode($userID)->getRelationships(array('TOP_MUSIC'), Relationship::DirectionOut);
                foreach($topMusicRels as $musicRel) {
                        $musicRel->delete();
                }

                foreach($musicPosArray as $musicPos => $musicID) {
                        $this->addTopTopMusic($userID, $musicID, $musicPos);
                }
        }
	/********************************/

	/* 	USER ATMOSPHERE FUNCTIONS	*/
	/****************************************/
	private function addToTopAtmosphere($userID, $atmosphereID, $pos) {
                return $this->getNode($userID)->relateTo(getNode(atmosphereID), 'TOP_ATMOSPHERES')
                                        ->setProperty("pos", $pos)->save();
        }

        public function getTopAtmospheres($userID) {
                $topAtmosphereRels = $this->getNode($userID)->getRelationships(array('TOP_ATMOSPHERES'), Relationship::DirectionOut);
                $topAtmosphereIDs = array();
                foreach ($topAtmosphereRels as $atmosphereRel) {
                        array_push($topAtmosphereIDs, $atmosphereRel->getEndNode()->getId());
                }
                return $topAtmosphereIDs;
        }

	public function getTopAtmospherePos($userID, $pos) {
                $topAtmosphereRels = $this->getNode($userID)->getRelationships(array('TOP_ATMOSPHERES'), Relationship::DirectionOut);
                foreach ($topAtmosphereRels as $atmosphereRel) {
			if ($atmosphereRel->getProperty('pos') == $pos)
				return $atmosphereRel->getEndNode()->getId();
		}
		return null;
	}

        public function updateTopAtmospherePositions($userID, $atmospherePosArray) {
                $topAtmosphereRels = $this->getNode($userID)->getRelationships(array('TOP_ATMOSPHERES'), Relationship::DirectionOut);
                foreach($topAtmosphereRels as $atmosphereRel) {
                        $atmosphereRel->delete();
                }

                foreach($atmospherePosArray as $atmospherePos => $atmosphereID) {
                        addTopTopAtmosphere($userID, $atmosphereID, $atmospherePos);
                }
        }
	/********************************/


	/* UPDATE SPENDING LIMIT VALUES */
	public function updateSpending($userID, $spendingVal) {
		return $this->getNode($userID)->setProperty("spendingLimit", $spendingVal)->save()->getId();
	}
	/********************************/

        public function updateUserInfo($userID, $userName, $firstName, $lastName, $gender) {
		return $this->getNode($userID)->setProperty("firstName", $firstName)
					->setProperty("lastName", $lastName)
					->setProperty("userName", $userName)
					->setProperty("gender", $gender)->save()->getId();
	}

}

?>
