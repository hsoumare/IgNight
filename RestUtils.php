<?php
include_once "RestRequest.php";

class RestUtils
{

    public static function sendResponse($status = 200, $body, $content_type = 'text/html'){
		$status_header = 'HTTP/1.1 ' . $status . ' ' . RestUtils::getStatusCodeMessage($status);
		// set the status
		header($status_header);
		// set the content type
		header('Content-type: ' . $content_type);
			echo $body;
			exit;
	}




    public static function processRequest()
	{
		//print "In processRequest";
		$request_method = strtolower($_SERVER['REQUEST_METHOD']); // get or set
		$return_obj		= new RestRequest(); // return RestRequest
		// we'll store our data here
		$data			= array();
		//print $request_method;
		switch ($request_method)
		{
			// gets are easy...
			case 'get':
				$data = $_GET;
			    $msg = $_GET['msg']; // TODO Ensure that client populates msg field when making GET requests
                break;
			// so are posts
			case 'post':
				$data = $_POST;
				//print "Just before printing array";
				//print_r($data);
                $msg = $_POST['msg']; // TODO Ensure that client populates msg field when making POST requests
				break;
		
		}

		// store the method
		$return_obj->setMethod($request_method);

		// set the raw data, so we can access it if needed (there may be
		// other pieces to your requests)
		$return_obj->setRequestVars($data);
        $return_obj->setMsg($msg);

		//if(isset($data['data'])){ // translate the JSON to an Object for use however you want
			//$return_obj->setData(json_decode($data['data']));
		//}
        return $return_obj;
	}



    public static function getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}
}

?>