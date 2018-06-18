<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

  require_once APPPATH . '/libraries/JWT_Controller.php';
  use Restserver\Libraries\REST_Controller;

    class Server extends REST_Controller{


        function __construct()
        {
            // Construct the parent class
            parent::__construct();

            

            // Configure limits on our controller methods
            // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
            $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
            $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
            $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
            
            date_default_timezone_set("Africa/Lagos");
            $this->load->model('Kyc_Model');
            $this->key = '@1!Onlin3B**&ossBNia';

        }

        //User Login
        public function login_post()
        {

            $pass = $this->post('pass');
            $user = $this->post('user');
            $data = array(
                'user' => $user,
                'pass' => $pass
            );

            $login = $this->Kyc_Model->login($data);
            if($login === 'false'){

                
                $this->set_response([
                    'status' => FALSE,
                    'message' => 'User Does Not Exist'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
                
                

            }

            elseif($login['pass'] === true){

                
                $token = array();
                $token['salt']['expire_time'] = time() + (60 * 30);
                $token['salt']['id'] = $login['data']->AGENT_ID;
                $token['salt']['user'] = $login['data']->AGENT_USERNAME;
                $token['salt']['admin'] = true;
                $token['salt']['name'] = $login['data']->AGENT_NAME;
                $token['salt']['email'] = $login['data']->AGENT_EMAIL;
                $token['salt']['phone'] = $login['data']->AGENT_PHONE;
                $reply['salt']['admin'] = false;
                

                if($login['data']->AGENT_ADMIN == true){
                    
                    $reply['admin'] = true;
                }
                else{

                    $reply['admin'] = false;

                }

//                $token['salt']['is_admin'] = $reply['admin'];
                //$token['salt']['token'] = $token;
                $token['encoded']['logged'] = true;
                $token['encoded']['is_admin'] = $reply['admin'];
                
                $token['encoded']['secure'] = JWT::encode($token['salt'], ($this->key));
                //$token['encoded']['insecure'] = JWT::decode($token['encoded']['secure'], ($this->key));
                
                
                $this->set_response($token['encoded'], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code   


            }
            else{

                $this->set_response([
                    'status' => FALSE,
                    'message' => 'Incorrect Password'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
                
                
            }

        }


        //Get Agents
        public function getAgents_post()
        {
            $token = ($this->post('token'));
            
            $token = JWT::decode($token, ($this->key));
            
            $id['company'] = $token->id;
            $id['id'] = $this->post('id');
            if($token){

            
                $admin = $token->admin;

                

                if($id['id']){

                    if( $admin === true ){
                    

                        if($id !== NULL){
                            //$pass = $this->generatePass();
                            $getAgent = $this->Kyc_Model->getAgent($id);
                            //$getAgent['pass'] = $pass; 

                            if($getAgent){

                                
                                $this->set_response($getAgent, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code   
                                
                            }
                            else{

                                $this->set_response([
                                    'status' => FALSE,
                                    'message' => 'Agent Does Not Exist'
                                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
                                
                            }
                        }

                        else{

                            // Set the response and exit
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Unknown User'
                        ], REST_Controller::HTTP_NOT_FOUND);
                        }
                    }

                    else{

                        $this->set_response([
                            'status' => FALSE,
                            'message' => 'Unauthorized Call To Endpoint'
                        ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
                        

                    }

                }
                else{

                    $getAgents = $this->Kyc_Model->getAgents($id['company']);

                        if($getAgents){

                            $this->set_response($getAgents, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code   
                            
                        }
                        else{

                            $this->set_response([
                                'status' => FALSE,
                                'message' => 'Bad Request'
                            ], REST_Controller::HTTP_BAD_REQUEST); // NOT_FOUND (404) being the HTTP response code
                            
                        }
                }
            }
            else{

                $this->set_response([
                                'status' => FALSE,
                                'message' => 'Invalid Token Sent'
                            ], REST_Controller::HTTP_BAD_REQUEST); // NOT_FOUND (404) being the HTTP response code
                            
                

            }
                
        }

        
        public function index_get()
        {
            
            
        }

        //Generates Pass
        public function generatePass($lenght = 4)
        {
            return strtoupper(substr(md5(uniqid(random_bytes(22), true)), 0, $lenght).rand(22, 99));
        }

        //Create Agent
        public function addAgent_post()
        {
            
            if(!empty($this->post('name')) && !empty($this->post('address')) && !empty($this->post('email')) && !empty($this->post('company')) && !empty($this->post('token'))){

                $token = ($this->post('token'));
            
                $token = JWT::decode($token, ($this->key));
                
                $admin = $token->admin;
                if($admin === true){

                        
                    $pass = $this->Server->generatePass();
                    $username = strtok($this->post('name'), ' ') . '.' . rand(100, 999);
                    $salt = rand(1000,9999);
                    $request = array(
                    'AGENT_NAME' =>  $this->post('name'),
                    'AGENT_ADDRESS' => $this->post('address'),
                    'AGENT_EMAIL' => $this->post('email'),
                    'AGENT_PHONE' => $this->post('phone'),
                    'AGENT_SALT' => $salt,
                    'AGENT_PASS' => password_hash($pass, PASSWORD_DEFAULT),
                    'AGENT_USERNAME' => $username,
                    'CREATED_ON' => date("Y-m-d H:i:s"),
                    'PLATFORM_ID' => $this->post('company')
                    );

                    //$userDetails = $this->post('company');
                    $pid = $this->post('platform');
                    $uid = $this->post('user');
                    //$token = $this->post('token');
                    //$token = array();
                    //$token['id'] = $id;
                    //$token['name'] = 'David';
                    //$encoded = JWT::encode($token, base64_encode('@1!Onlin3BossBNia')); echo '<br />';
                    //$token2 = JWT::decode($encoded, base64_encode('@1!Onlin3BossBNia'));
                    
                    

                        $this->load->library('email');

                        $this->email->from('kyc@creditclan.com', 'Platform Administrator');
                        $this->email->to($this->post('email'));
                        
                        $this->email->subject('Registration Details');
                        $this->email->message('Your registration details are as follows. Username:'.$username.' Password:'.$pass.' ');

                        if($this->email->send()){

                            
                            if($this->Kyc_Model->createAgent($request)){
                
                                $this->response('Agent Created Successfully', REST_Controller::HTTP_CREATED);
                            }
                            else{
                
                                $this->response('Unable to create Agent', REST_Controller::HTTP_NO_CONTENT);
                            }
                        }
                        else{

                            $this->response('Unable to send sctivation mail to agent please try again later', REST_Controller::HTTP_NO_CONTENT);
                        }
                    
                }
                else{

                    $this->response('Unauthorized request', REST_Controller::HTTP_UNAUTHORIZED);
                }

            }
            else{

                $this->response('All fields are Required', REST_Controller::HTTP_NO_CONTENT);
            }
            
        }


        //Agent password Update
        public function agentUpdateP_post()
        {

            $id = $this->post('id');
            if($this->post('id') && $this->post('pass')){

                if($this->post('id') !== NULL){

                    if(mb_strlen($this->post('pass')) < 8){

                        $this->set_response([
                            'status' => FALSE,
                            'message' => 'Password Field Must contain 8 or more characters'
                        ], REST_Controller::HTTP_BAD_REQUEST); 
    
                    }
                    else{

                        $data = array(
                            'id' => $this->post('id'),
                            'password' => $this->post('pass')
                        );
                        if($this->Kyc_Model->updatePass($data)){

                            $this->set_response([
                                'status' => TRUE,
                                'message' => 'Password updated Successfully'
                            ], REST_Controller::HTTP_OK); 

                        }
                        else{

                            $this->set_response([
                                'status' => FALSE,
                                'message' => 'Password update failed'
                            ], REST_Controller::HTTP_NO_CONTENT); 
        
                        }
                    }

                }
                else{

                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'Missing user token'
                    ], REST_Controller::HTTP_BAD_REQUEST); // NOT_FOUND (404) being the HTTP response code

                }
            }
            else{

                $this->set_response([
                    'status' => FALSE,
                    'message' => 'Bad Request, Required Parameters missing'
                ], REST_Controller::HTTP_BAD_REQUEST); // NOT_FOUND (404) being the HTTP response code

            }
        }

        //Ban Agent
        public function banAgent_post()
        {

            if($this->post('id') !== NULL){
                
                $id = $this->post('id');
                $data = array(
                    'id' => $id,
                    'status' => 0
                );
                if($this->Kyc_Model->banAgent($data)){
                    $this->set_response([
                        'status' => TRUE,
                        'message' => 'Agent banned Successfully'
                    ], REST_Controller::HTTP_OK); 

                }
                else{

                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'Agent ban failed'
                    ], REST_Controller::HTTP_NO_CONTENT); 

                }

            }

        }

        //Delete Agent
        public function deleteAgent_post()
        {

            $id = $this->post('id');
            $company = $this->post('company');
            $data = array(
                'id' => $id,
                'company' => $company
            );
            if($id && $company){

                if($this->Kyc_Model->deleteAgent($data)){

                    $this->set_response([
                        'status' => TRUE,
                        'message' => 'Agent deleted Successfully'
                    ], REST_Controller::HTTP_OK); 


                }
                else{

                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'Agent delete failed'
                    ], REST_Controller::HTTP_NO_CONTENT); 

                }

            }
            else{

                $this->set_response([
                    'status' => FALSE,
                    'message' => 'Bad Request'
                ], REST_Controller::HTTP_NO_CONTENT); 

            }
        }


        //Activate Banned Agent
        public function activateAgent_post()
        {
            if($this->post('id') !== NULL){
                
                $id = $this->post('id');
                $data = array(
                    'id' => $id,
                    'status' => 1
                );
                if($this->Kyc_Model->activateAgent($data)){
                    $this->set_response([
                        'status' => TRUE,
                        'message' => 'Agent Activated Successfully'
                    ], REST_Controller::HTTP_OK); 

                }
                else{

                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'Agent Activation failed'
                    ], REST_Controller::HTTP_NO_CONTENT); 

                }

            }
        }

        public function addRequest_post()
        {
            
            if($pid && $uid && !empty($pid) && !empty($uid)){

                $details = array(
                    'user' => $uid,
                    'platform' => $pid
                );

                $request = array(
                    'PLATFORM_ID' => $pid,
                    'PEOPLE_ID' => $uid,
                    'REQUEST_ID' => rand(1000, 9999),
                    'DATE_ADDED' => date("Y-m-d H:i:s")
                );

                $data['request'] = $request;
                $data['details'] = $details;

                if($this->Kyc_Model->addRequest($data)){
                    
                    $this->set_response([
                        'status' => TRUE,
                        'message' => 'Request Sent Successfully'
                    ], REST_Controller::HTTP_OK); 

                }
                else{

                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'Request Already Exists'
                    ], REST_Controller::HTTP_BAD_REQUEST); 
    
                }
            }
            else{

                $this->set_response([
                    'status' => FALSE,
                    'message' => 'Invalid Request'
                ], REST_Controller::HTTP_BAD_REQUEST); 

            }
        }


        public function assignAgent_post()
        {
            $user = $this->post('user');
            $token = $this->post('token');
            $task = $this->post('request');

            if($user && $token && $task){

                $token = ($this->post('token'));
                //echo $token;
                $token = JWT::decode($token, ($this->key));
                //print_r($token);
                $admin = $token->admin;
                if($admin === true){

                    $request['request'] = $task;
                    $request['data'] = array(

                        'KYC_REQUEST_ID' => $task,
                        'AGENT_ASSIGNED' => $user,
                        'PLATFORM_ID' => $token->id,
                        'ASSIGNED_DATE' => date("Y-m-d H:i:s")
                    );

                    if($this->Kyc_Model->assignRequest($request)){

                        
                        $this->load->library('email');

                        $this->email->from('kyc@creditclan.com', 'Platform Administrator');
                        $this->email->to($this->post('email'));
                        
                        $this->email->subject('New Task');
                        $this->email->message('You have been assigned a new task, login to your mobile app to view it ');

                        if($this->email->send()){
                            $this->set_response(['status' => TRUE,'message' => 'Request Assigned Successfully'], REST_Controller::HTTP_OK); 
                        }
                        else{

                           

                            $this->response('Unable to send notification mail to agent, but request was assigned successfully', REST_Controller::HTTP_OK);
                        }
                        
                        
                    }
                    else{

                        $this->set_response([
                            'status' => FALSE,
                            'message' => 'Request already assigned to an agent'
                        ], REST_Controller::HTTP_NOT_FOUND); 
        
                    }
                }
                else{

                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'Unauthorized request'
                    ], REST_Controller::HTTP_UNAUTHORIZED); 
    
                }
            }
            else{

                $this->set_response([
                    'status' => FALSE,
                    'message' => 'All fields are required'
                ], REST_Controller::HTTP_BAD_REQUEST); 

            }
        }

        public function getRequests_post()
        {

        }

        public function testjwt_get()
        {
            $id = 2345678;
            $token = array();
            $token['id'] = $id;
            $token['name'] = 'David';
            echo JWT::encode($token, base64_encode('@1!Onlin3BossBNia'));
            $encoded = JWT::encode($token, base64_encode('@1!Onlin3BossBNia')); echo '<br />';
            $token2 = JWT::decode($encoded, base64_encode('@1!Onlin3BossBNia'));
            var_dump($token2);
        }

    }