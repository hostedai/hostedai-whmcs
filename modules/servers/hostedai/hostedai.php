<?php

use WHMCS\Module\Server\HosteDai\Helper;

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function hostedai_MetaData()
{
    return array(
        'DisplayName' => 'hosted·ai',
        'APIVersion' => '1.0', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
    );
}


function hostedai_ConfigOptions(array $params)
{   

    global $whmcs;
    $helper = new Helper();
    $pid = $whmcs->get_req_var("id");

    /** Get the API to fetch the pricing policy items */
    $getPricingPolicy = $helper->getPolicyItems('pricing-policy');
    $pricingOptions = [];
    if(isset($getPricingPolicy))
    {
        $pricingOptions[] = 'Select Option';
        foreach ($getPricingPolicy['result'] as $key => $value) {
            $pricingOptions[$value->policy->id] = $value->policy->name;
        }
    }

    /** Get the API to fetch the resource policy items */
    $getResourcePolicy = $helper->getPolicyItems('resource-policy');
    $resourceOptions = [];
    if(isset($getResourcePolicy))
    {
        $resourceOptions[] = 'Select Option';
        foreach ($getResourcePolicy['result'] as $key => $value) {
            $resourceOptions[$value->id] = $value->name;
        }
    }

     /** Get the API to fetch the Service policy items */
    $getServicePolicy = $helper->getPolicyItems('policy/service');
    $serviceOptions = [];
    if (isset($getServicePolicy['result']) && is_array($getServicePolicy['result'])) {

        $serviceOptions[] = 'Select Option';
    
        foreach ($getServicePolicy['result'] as $policy) 
        {
            $serviceOptions[$policy->id] = $policy->name;
            // if (isset($policy->objects) && is_array($policy->objects)) {
            //     foreach ($policy->objects as $object) {
            //         $serviceOptions[$object->id] = $object->name;
            //     }
            // }
        }
    }

    /** Get the API to fetch the Instance policy items */
    $getInstancePolicy = $helper->getPolicyItems('policy/instance-type');
    $instanceOptions = [];
    if (isset($getInstancePolicy['result']) && is_array($getInstancePolicy['result'])) 
    {

        $instanceOptions[] = 'Select Option';
        foreach ($getInstancePolicy['result'] as $policy) {
            $instanceOptions[$policy->id] = $policy->name;
            // if (isset($policy->objects) && is_array($policy->objects)) {
            //     foreach ($policy->objects as $object) {
            //         $instanceOptions[$object->id] = $object->name;
            //     }
            // }
        }
    }

    /** Get the API to fetch the Image policy items */
    $getImagePolicy = $helper->getPolicyItems('policy/image');
    $imageOptions = [];
    if (isset($getImagePolicy['result']) && is_array($getImagePolicy['result'])) 
    {

        $imageOptions[] = 'Select Option';
        foreach ($getImagePolicy['result'] as $policy) {
            $imageOptions[$policy->id] = $policy->name;
            // if (isset($policy->objects) && is_array($policy->objects)) {
            //     foreach ($policy->objects as $object) {
            //         $imageOptions[$object->id] = $object->name;
            //     }
            // }
        }
    }

    /** Get the API to fetch the Role items */
    $getRoleData = $helper->getPolicyItems('roles');
  
    $roleOptions = [];
    if (isset($getRoleData['result'])) 
    {

        $roleOptions[] = 'Select Option';
        foreach ($getRoleData['result']->roles as $policy) {
            $roleOptions[$policy->id] = $policy->label;
        }
    }

     /** create the custom fields */
    $customfieldarray = [
        'team_id' =>
        [
            'type' => 'product',
            'fieldname' => 'team_id|Team Id',
            'relid' => $pid,
            'fieldtype' => 'text',
            'description' => '',
            'adminonly' => 'on',
            'sortorder' => '1',
        ],
    ];
    $helper->createHostedaiCustomFields($customfieldarray);

    return array(

        'pricing_policy' => array(
            'FriendlyName' => 'Pricing Policy',
            'Type' => 'dropdown',
            'Size' => '25',
            'Options' => $pricingOptions,
            'Description' => '',
        ),
        'resource_policy' => array(
            'FriendlyName' => 'Resources Policy',
            'Type' => 'dropdown',
            'Size' => '25',
            'Options' => $resourceOptions,
            'Description' => '',
        ),
        'service_policy' => array(
            'FriendlyName' => 'Service Policy',
            'Type' => 'dropdown',
            'Size' => '25',
            'Options' => $serviceOptions,
            'Description' => '',
        ),
        'instance_type_policy' => array(
            'FriendlyName' => 'Instance Type Policy',
            'Type' => 'dropdown',
            'Size' => '25',
            'Options' => $instanceOptions,
            'Description' => '',
        ),
        'image_policy' => array(
            'FriendlyName' => 'Image Policy',
            'Type' => 'dropdown',
            'Size' => '25',
            'Options' => $imageOptions,
            'Description' => '',
        ),
        'role' => array(
            'FriendlyName' => 'Role',
            'Type' => 'dropdown',
            'Size' => '25',
            'Options' => $roleOptions,
            'Description' => '',
        ),
        'color' => array(
            'FriendlyName' => 'Color',
            'Type' => 'dropdown',
            'Size' => '25',
            'Options' => array(
                '#414141' => 'black',
                '#305EFB' => 'blue',
                '#104822' => 'green',
                '#FF5738' => 'orange',
                '#FFC352' => 'yellow',
            ),
            'Description' => '',
        ),
        'loginUrl' => array(
            'FriendlyName' => 'Login URL',
            'Type' => 'text',
            'Size' => '255',
        ),
        'suspentionDays' => array(
            'FriendlyName' => 'No. of Suspension Days',
            'Type' => 'text',
            'Size' => '25',
        ),
        'termminationDays' => array(
            'FriendlyName' => 'No. of Termination Days',
            'Type' => 'text',
            'Size' => '25',
        ),

    );

}


/**
 * Test connection with the given server parameters.
 *
 * Allows an admin user to verify that an API connection can be
 * successfully made with the given configuration parameters for a
 * server.
 *
 * When defined in a module, a Test Connection button will appear
 * alongside the Server Type dropdown when adding or editing an
 * existing server.
 */
function hostedai_TestConnection(array $params)
{
    
    try {

        $helper = new Helper($params);
        $getPricingPolicy = $helper->getPolicyItems('pricing-policy');
        if($getPricingPolicy['httpcode'] == 200){
         
            $success = true;
        }
        else{
            $errorMsg = $getPricingPolicy['result']->message;
        }
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'openprovider_plesk_license',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

function hostedai_CreateAccount(array $params)
{
    try {
        global $whmcs;
        $helper = new Helper($params);

        $serviceId = $params['serviceid'];
        $pid = $params['pid'];
        $teamId = $params['customfields']['team_id'];
        $userId = $params['userid'];

        $pricingPolicyID = $params['configoption1'];
        $resourcePolicyID =  $params['configoption2'];
        $servicePolicyID =  $params['configoption3'];
        $instancePolicyID =  $params['configoption4'];
        $imagePolicyID =  $params['configoption5'];
        $roleID =  $params['configoption6'];
        $color =  $params['configoption7'];

        $email =  $params['clientsdetails']['email'];
        $name =  $params['clientsdetails']['fullname'];

        $postData = [
            'color' => $color ?? '#414141',
            'description' => '',
            'image_policy_id' => $imagePolicyID ?? '',
            "instance_type_policy_id" => $instancePolicyID ?? '',
            'members' => [
                [
                    'email' => $email ?? '',
                    'name' => $name ?? '',
                    'role' => $roleID ?? '',
                ]
            ],
            'name' => $name ?? '',
            'pricing_policy_id' => $pricingPolicyID ?? '',
            'resource_policy_id' => $resourcePolicyID ?? '',
            'service_policy_id' => $servicePolicyID ?? '',
        ];
        

        if(isset($teamId) && $teamId != '')
        {

        }else{
            
            $getResponse = $helper->createHostedaiTeam($postData);
            
            if($getResponse['httpcode'] == 200)
            {

                if (isset($getResponse['result']->id)) {
                    $teamId = $getResponse['result']->id;
                    
                    $fields = ["team_id" => $teamId];
                    $helper->insert_hostedai_custom_fields_value($serviceId, $pid, $fields);
                    
                    // 
                    $billingCycle = $params['model']->billingcycle;
                    if ($billingCycle === 'One Time') {
                        $helper->insert_teamDetail($userId, $serviceId, $pid, $teamId, 'insert');
                    }
                }
                
            }else{
                return $getResponse['result']->message;
            }
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'hostedai',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


/**
 * Suspend an instance of a product/service.
 *
 * Called when a suspension is requested. This is invoked automatically by WHMCS
 * when a product becomes overdue on payment or can be called manually by admin
 * user.
 */
function hostedai_SuspendAccount(array $params)
{
    try {
        $helper = new Helper($params);
        $team_id = $params['customfields']['team_id'];

        $getResponse = $helper->suspendHostedaiTeam($team_id);

        if($getResponse['httpcode'] == 200)
        {
            return 'success';   
        }else{
            return 'error';
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'hostedai',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}


/**
 * Un-suspend instance of a product/service.
 *
 * Called when an un-suspension is requested. This is invoked
 * automatically upon payment of an overdue invoice for a product, or
 * can be called manually by admin user.
 */
function hostedai_UnsuspendAccount(array $params)
{
    try {

        $helper = new Helper($params);
        $team_id = $params['customfields']['team_id'];

        $getResponse = $helper->unsuspendHostedaiTeam($team_id);

        if($getResponse['httpcode'] == 200)
        {
            return 'success';   
        }else{
            return 'error';
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'hostedai',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

}


/**
 * Terminate instance of a product/service.
 *
 * Called when a termination is requested. This can be invoked automatically for
 * overdue products if enabled, or requested manually by an admin user.
 */
function hostedai_TerminateAccount(array $params)
{
    try {

        $helper = new Helper($params);
        $serviceId = $params['serviceid'];
        $pid = $params['pid'];
        $team_id = $params['customfields']['team_id'];

        // Terminate
        $getResponse = $helper->terminateHostedaiTeam($team_id);

        if($getResponse['httpcode'] == 200)
        {
            $fields = ["team_id" => ''];
            $helper->insert_hostedai_custom_fields_value($serviceId, $pid, $fields);
            $helper->delete_teamDetail($serviceId, $pid);
            return 'success';   
        }else{
            return 'error';
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'hostedai',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }
}


/**
 * Upgrade or downgrade an instance of a product/service.
 *
 * Called to apply any change in product assignment or parameters. It
 * is called to provision upgrade or downgrade orders, as well as being
 * able to be invoked manually by an admin user.

 */
function hostedai_ChangePackage(array $params)
{
    try {
        $helper = new Helper($params);
        $pricing_policy_id = $params['configoption1'];
        $resource_policy_id = $params['configoption2'];
        $teamId = $params['customfields']['team_id'];

        $changePackage = $helper->changeHostedaiTeamPackage($pricing_policy_id, $resource_policy_id, $teamId); 
        if($changePackage['status'] == 'success') {
            return 'success';
        } else {
            return $changePackage['message'];
        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'hostedai',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

}




/**
 * Admin services tab additional fields.
 *
 * Define additional rows and fields to be displayed in the admin area service
 * information and management page within the clients profile.

 */
function hostedai_AdminServicesTabFields(array $params)
{
    try {
        global $CONFIG;
        global $whmcs;

        $loginURL = !empty($params['configoption8']) ? $params['configoption8'] : '#';
        $helper = new Helper($params);

        $assets = $CONFIG['SystemURL'] . "/modules/servers/hostedai/assets";

        $language = $CONFIG['Language'];
        $langfilename = __DIR__ . '/lang/' . $language . '.php';
        if (file_exists($langfilename)) {
            require($langfilename);
        } else {
            require(__DIR__ . '/lang/english.php');
        }

        $key = $params['customfields']['team_id'];

        if($key != '') {
            $getTeamdata = $helper->getTeamDetail($key);

            // Get team data
            if($getTeamdata['httpcode'] == 200)
            {
    
                if($getTeamdata['result']->team->is_suspended == true) {
                    $is_suspend = "<span class='btn btn-danger'>Suspended</span>";
                } else {
                    $is_suspend = "<span class='btn btn-success'>Active</span>";
                }
    
    
                $getTeamMembers = $helper->getTeamMembers($key);
    
                if($getTeamMembers['httpcode'] == 200) {
                    
                    $members = $getTeamMembers['result']->members;
                    foreach ($members as $member) {
                        $teamEmail = $member->user->email ?? '';
                        $teamStatus = $member->status ?? '';
                        $teamRole = $member->role->label ?? '';
        
                        $teamMemberHTML .= '<tbody>
                                <tr>
                                    <td>' . $teamEmail . '</td>
                                    <td>' . ucfirst($teamStatus) . '</td>
                                    <td>' . $teamRole . '</td>
                                </tr>
                            </tbody>';
                    }
        
                    // Get resource overview
                    $getResourceOverview = $helper->getResourceOverview($key); 
                    
                    if($getResourceOverview['httpcode'] == 200) {
                        $resourceOverviewData = $getResourceOverview['result'];
        
                        $resourceHTML = '';
            
                        foreach ($resourceOverviewData as $resourceType => $resource) {

                            $used = $resource->used;
                            $available = $resource->available;

                            if($available > 0) {
                                $percentage = ($used/$available)*100;
                            } else {
                                $percentage = 0;
                            }
            
                            if ($resourceType == 'cores') {
                                $unit = 'Cores';
                            } elseif ($resourceType == 'gpus') {
                                $unit = 'No. of cards';
                            } else {
                                $unit = 'GB';
                            }

                            if($available == -1) {
                                $aval = 'Unlimited';
                                $used = $resource->used . " " . $unit.  ' (∞)';
                            } else {
                                $aval = $available . " " . $unit;
                                $used = $resource->used . " " . $unit . " (".$percentage."%)";
                            }
                            
                            $imagePath = $CONFIG['SystemURL'] . "/modules/servers/hostedai/assets/images/".$resourceType.".svg";
            
                            $resourceHTML .= '<div class="col-lg-6 mt-2">
                                    <div class="overview-card">
                                        <div class="overview-card-header">
                                            <img src="'. $imagePath .'" alt="'. $resourceType .'">
                                            <h3>'.strtoupper($resourceType).'</h3>
                                        </div>
                                        <div class="overview-card-detail">
                                            <p>'.$used.'</p>
                                            <p>'.$aval.'</p>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="'.$percentage.'" aria-valuemin="'.$percentage.'" aria-valuemax="'.$percentage.'" style="width:'.$percentage.'%">'.$percentage.'%</div>
                                        </div>
                                    </div>
                                </div>';
                        }
                        $assets = $CONFIG['SystemURL'] . "/modules/servers/hostedai/assets";
                        $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 3);
                
                        $informationHtml = '
                        <link href="' . $assets . '/css/style.css?v=' . $random . '" rel="stylesheet">
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/validator/13.7.0/validator.min.js"></script>
                        <script src="' . $assets . '/js/custom.js?v=' . $random . '"></script>
                
                        <table class="ad_on_table_dash table table-striped" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tbody>
                                <tr>
                                    <td style="width:50%" class="hading-td">
                                        <div class="hosting-information">
                                            <div class="panel panel-primary">
                                                <div class="panel-heading"><p>Resource Overview</p> <p>'.$is_suspend.'</p> </div>
                                                <div class="panel-body overview-main">
                                                    <div class="row">
                                                        '.$resourceHTML.'
                                                    </div>
                                                </div>
                                            </div>
                
                                            <div class="panel panel-success">
                                                <div class="panel-heading">Team Members List</div>
                                                <div class="panel-body">
                                                    <table class="table table-bordered">
                                                        <thead class="members-list-head">
                                                            <tr>
                                                                <th scope="col">Email</th>
                                                                <th scope="col">Role</th>
                                                                <th scope="col">Status</th>
                                                            </tr>
                                                        </thead>
                                                            '.$teamMemberHTML.'
                                                    </table>
                
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>';
                
                        return [
                            " Hosting Information" => $informationHtml,
                        ];
        
                    }
    
                }
    
    
    
            }

        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'hostedai',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
    }
    return array();
}

/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 
 */
function hostedai_ClientArea(array $params)
{
    
    try {
        
        global $CONFIG;
        global $whmcs;
        $helper = new Helper($params);

        $loginURL = !empty($params['configoption8']) ? $params['configoption8'] : '#';

        $assets = $CONFIG['SystemURL'] . "/modules/servers/hostedai/assets";

        $language = $CONFIG['Language'];
        $langfilename = __DIR__ . '/lang/' . $language . '.php';
        if (file_exists($langfilename)) {
            require($langfilename);
        } else {
            require(__DIR__ . '/lang/english.php');
        }

        $key = $params['customfields']['team_id'];
        if($key != '') {
            $getTeamdata = $helper->getTeamDetail($key);

            $getTeamdata = $helper->getTeamDetail($key);
    
            if($getTeamdata && $getTeamdata['httpcode'] == 200)
            {
                $getTeamMembers = $helper->getTeamMembers($key);
    
                $responseData  = [
                    'name' => $getTeamdata['result']->team->name,
                    'teamId' =>$key,
                ];
    
                $getResourceOverview = $helper->getResourceOverview($key); 
                
                $templateFile = 'templates/manage.tpl';
    
                if($getResourceOverview['httpcode'] == 200) {
    
                    $resourceOverviewData = $getResourceOverview['result'];

                    foreach ($resourceOverviewData as $key => $resource) {
                        if (isset($resource->available) && $resource->available > 0) {
                            $resource->percent = ($resource->used / $resource->available) * 100;
                        } else {
                            $resource->percent = 0;
                        }
                    }
            
                    return array(
                        'templatefile' => $templateFile,
                        'templateVariables' => array(
                            'responseData' => $responseData,
                            'teammembers' => $getTeamMembers ? $getTeamMembers['result']->members : '',
                            'resourcesData' => $resourceOverviewData,
                            'loginURL' => $loginURL,
                            'serviceId' => $params['serviceid'],
                            'userEmail' => $params['clientsdetails']['email'],
                            'assets' => $assets,
                            'LANG' => $_ADDONLANG
                        ),
                    );
    
                }
                
            }

        }

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'hostedai',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}
