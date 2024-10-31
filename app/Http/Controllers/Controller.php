<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function sendResponse($result, $message, $code = 200, $additionalFields = [])
    {
        $data = $result['data'] ?? $result;  
    
        $response = [
            'success' => true,
            'data'    => $data,  
            'message' => $message,
        ];
    
        if (isset($result['links']) && isset($result['meta'])) {
            $response['links'] = $result['links'];
            $response['meta'] = $result['meta'];
        }
        $response = array_merge($response, $additionalFields);
        return response()->json($response, $code);
    }
    

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
