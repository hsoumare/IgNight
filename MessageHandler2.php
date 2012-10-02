<?php

echo "test";
$request_method = strtolower($_SERVER['REQUEST_METHOD']); // get or set
		//$return_obj		= new RestRequest(); // return RestRequest
		// we'll store our data here
		$data= array();
	echo $request_method;
		switch ($request_method)
		{
			
			case 'get':
				$data = $_GET;
			    $msg = $_GET['msg']; // TODO Ensure that client populates msg field when making GET requests
                break;
			case 'post':
				echo "in_post";
				echo $msg;
				$data = $_POST;
                $msg = $_POST['msg']; // TODO Ensure that client populates msg field when making POST requests
				break;
		
		}

	
			

		

?>