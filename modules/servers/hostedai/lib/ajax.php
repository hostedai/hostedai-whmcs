<?php

// Set headers for AJAX response
header('Content-Type: application/json');

// Initialize WHMCS if not already done
if (!defined('WHMCS')) {
    $possiblePaths = [
        dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php',
        '../../../init.php',
        '../../../../init.php',
        dirname($_SERVER['DOCUMENT_ROOT']) . '/init.php',
        $_SERVER['DOCUMENT_ROOT'] . '/init.php'
    ];
    
    $whmcsInitialized = false;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $whmcsInitialized = true;
            break;
        }
    }
    
    if (!$whmcsInitialized) {
        echo json_encode([
            'success' => false, 
            'message' => 'WHMCS initialization failed',
            'debug' => 'Tried paths: ' . implode(', ', $possiblePaths)
        ]);
        exit;
    }
}

use WHMCS\Module\Server\HosteDai\Helper;
use WHMCS\Database\Capsule;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate_otl') {
        generateOTL();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function generateOTL() {
    try {
        $serviceId = $_POST['service_id'] ?? '';
        $userEmail = $_POST['user_email'] ?? '';
        $staticLoginUrl = $_POST['static_login_url'] ?? '';
        
        if (empty($serviceId) || empty($userEmail)) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required parameters',
                'fallback_url' => $staticLoginUrl
            ]);
            return;
        }
        
        // Get service details to retrieve server configuration
        $service = Capsule::table('tblhosting')->where('id', $serviceId)->first();
        if (!$service) {
            echo json_encode([
                'success' => false,
                'message' => 'Service not found',
                'fallback_url' => $staticLoginUrl
            ]);
            return;
        }
        
        // Get server details
        $server = Capsule::table('tblservers')->where('id', $service->server)->first();
        if (!$server) {
            echo json_encode([
                'success' => false,
                'message' => 'Server configuration not found',
                'fallback_url' => $staticLoginUrl
            ]);
            return;
        }
        
        // Create helper with server params
        $params = [
            'serverhostname' => $server->hostname,
            'serverpassword' => decrypt($server->password)
        ];
        
        $helper = new Helper($params);
        $otlResponse = $helper->createOneTimeLoginToken($userEmail);
        
        if ($otlResponse && $otlResponse['httpcode'] == 201 && isset($otlResponse['result']->url)) {
            echo json_encode([
                'success' => true,
                'login_url' => $otlResponse['result']->url,
                'message' => 'One-time login link generated successfully'
            ]);
        } else {
            // Log the error for debugging
            $errorMsg = 'OTL generation failed';
            if (isset($otlResponse['result']->message)) {
                $errorMsg .= ': ' . $otlResponse['result']->message;
            }
            logActivity('OTL Generation Failed', $errorMsg);
            
            echo json_encode([
                'success' => false,
                'message' => 'Unable to generate secure login link. Using standard login.',
                'fallback_url' => $staticLoginUrl
            ]);
        }
        
    } catch (Exception $e) {
        logActivity('OTL AJAX Error', $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Using standard login.',
            'fallback_url' => $staticLoginUrl
        ]);
    }
}