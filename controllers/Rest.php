<?php 
defined('BASEPATH') OR exit('No direct script access allowed');


  use Restserver\Libraries\REST_Controller;

    class Rest extends REST_Controller{


        function __construct()
        {
            // Construct the parent class
            parent::__construct();

            // Configure limits on our controller methods
            // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
            $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
            $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
            $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
            $this->load->model('Rest_Model');
            date_default_timezone_set("Africa/Lagos");
            
        }

        public function customer_get()
        {
            //Get Requested customers social details
            $id = $this->get('id');
            $userQuery = $this->Rest_Model->getSocial($id);

            
            
            
            //Get Social Analytics fields weight
            $socialWeightQuery = $this->Rest_Model->getSocialWeight();

            if($userQuery){

                //Perform Analytics With fetched Data

                //Fetch Customer profile Details
                $userProfileQuery = $this->Rest_Model->getUserDetails($id);

                //Start analytics on fetched profile details
                if($userProfileQuery){

                    //Compare Profile Names
                    $firstNameCheck = stripos($userProfileQuery->LEGAL_NAME, $userQuery->FIRST_NAME );
                    $lastNameCheck = stripos($userProfileQuery->LEGAL_NAME, $userQuery->LAST_NAME);

                    if($firstNameCheck !== false || $lastNameCheck !== false){
                        
                        $name = true;
                        $name_score = $socialWeightQuery->PERSON_PROFILE_NAME;
                        
                    }
                    else{

                        $name = false;
                        $name_score = 0;
                    }

                    //Compare Profile Emails
                    $emailCheck = stripos($userProfileQuery->EMAIL, $userQuery->EMAIL );

                    if($emailCheck !== false){

                        $email = true;
                        $email_score = $socialWeightQuery->PERSON_PROFILE_EMAIL;
                    }
                    else{

                        $email = false;
                        $email_score = 0;
                    }

                    //Check if Profile Picture is Present

                    if($userQuery->PICTURE_URL !== NULL){
                        $picture = true;
                        $picture_score = $socialWeightQuery->PERSON_PROFILE_PICTURE;
                    }
                    else{

                        $picture = false;
                        $picture_score = 0;
                    }

                    //Check if Last Post Date is greater than 30 days can be modified from db
                    if($userQuery->PROFILE_LAST_SHARE > (time() - (3600 * 24) * $socialWeightQuery->MINIMUM_SHARE)){

                        $share = true;
                        $share_score = $socialWeightQuery->PERSON_PROFILE_LAST_SHARE;
                    }
                    else{

                        $share = false;
                        $share_score = 0;
                    }
                    
                    //Check if Total Connection Less than 100 can be modified from db
                    if($userQuery->CONNECTIONS < $socialWeightQuery->MINIMUM_CONNECT){

                        $userConnect = false;
                        $userConnect_score = 0;

                    }
                    else{
                        $userConnect = true;
                        $userConnect_score = $socialWeightQuery->PERSON_PROFILE_CONNECTIONS;
                    }

                    $personalTotal = ($name_score + $email_score + $picture_score + $userConnect_score + $share_score) ;
                    //Check Headline For tags



                }
                else{

                    // Set the response and exit
                    $this->response([
                        'status' => FALSE,
                        'message' => 'This user Does not have a complete Personal Profile'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code

                }

                //Fetch User Work Details
                $userWorkQuery = $this->Rest_Model->getUserWork($id);

                    //Start analytics on fetched company details
                    if($userWorkQuery){

                    
                        $workNameCheck = stripos($userWorkQuery->COMPANY_OF_WORK_ID, $userQuery->COMPANY_OF_WPRK_ID );

                        if($workNameCheck !== false){

                            $workName = true;
                            $workName_score = $socialWeightQuery->PERSON_JOB_NAME;
                        }
                        else{

                            $workName = false;
                            $workName_score = 0;
                        }

                        if($userQuery->CURRENTLY_WORK_HERE > 0){
                            
                            $employedHere = true;
                            $employedHere_score = $socialWeightQuery->PERSON_CURRENT_HERE;
                        }
                        else{

                            $employedHere = false;
                            $employedHere_score = 0;
                        }

                        //Compare Company Address
                        $workAddressCheck = stripos($userWorkQuery->ADDRESS, $userQuery->ADDRESS );
                        if($workAddressCheck !== false){

                            $workAddress = true;
                            $workAddress_score = $socialWeightQuery->PERSON_JOB_LOCATION;

                        }
                        else{

                            $workAddress = false;
                            $workAddress_score = 0;

                        }

                        if($userWorkQuery->WORK_START_DATE == $userQuery->WORK_START_DATE){

                            $startDate = true;
                            $startDate_score = $socialWeightQuery->PERSON_START_DATE;
                        }
                        else{
                            $startDate = false;
                            $startDate_score = 0;
                        }

                        if($userWorkQuery->WORK_END_DATE == $userQuery->WORK_END_DATE){

                            $endDate = true;
                            $endDate_score = $socialWeightQuery->PERSON_END_DATE;
                        }
                        else{
                            $endDate = false;
                            $endDate_score = 0;
                        }

                        //$userJob = $this->Rest_Model->getOccupation();


                        $companyTotal = ($endDate_score + $startDate_score + $workAddress_score + $workName_score + $employedHere_score);
    
    
                    }
                    
                

                if($userQuery->CONNECTIONS <= 500){

                    $connect = $userQuery->CONNECTIONS;
                }
                else{
    
                    $connect = 'Above 500';
                }
                if($userQuery->DATE_MODIFIED < 1){
    
                    $userDate = date_create($userQuery->DATE_ADDED);
                }
                else{
    
                    $userDate = date_create($userQuery->DATE_MODIFIED);
    
                }
                
                //Build Analytics And Social details Array
            
                $users['user_id'] = $userQuery->PEOPLE_ID;
                $users['platform'] = $userQuery->SOCIAL_PLATFORM;
                $users['details']['profile']['full_name'] = $userQuery->SOCIAL_MEDIA_NAME;
                $users['details']['profile']['social_id'] = $userQuery->SOCIAL_MEDIA_ID;
                $users['details']['profile']['email'] = $userQuery->EMAIL;
                $users['details']['profile']['first_name'] = $userQuery->FIRST_NAME;
                $users['details']['profile']['last_name'] = $userQuery->LAST_NAME;
                $users['details']['profile']['link'] = $userQuery->SOCIAL_PROFILE;
                $users['details']['profile']['headline'] = $userQuery->SOCIAL_HEADLINE;
                $users['details']['profile']['picture'] = $userQuery->PICTURE_URL;
                $users['details']['profile']['location'] = $userQuery->ADDRESS;
                $users['details']['profile']['date'] = 'Analytics performed on ' . date_format($userDate, "D, d M Y, h:i:s a");
                $users['details']['company']['name'] = $userQuery->COMPANY_OF_WPRK_ID;
                $users['details']['company']['location'] = $userQuery->WORK_ADDRESS;
                $users['details']['company']['resume_month'] = $userQuery->WORK_START_DATE;
                $users['details']['company']['resume_year'] = $userQuery->WORK_END_DATE;
                $users['details']['company']['position'] = $userQuery->POSITION;
                $users['details']['company']['industry'] = $userQuery->INDUSTRY;
                $users['details']['company']['connections'] = $connect;
                $users['analytics']['profile']['name_is_match'] = $name;
                $users['analytics']['profile']['name_score'] = $name_score;
                $users['analytics']['profile']['email_is_match'] = $email;
                $users['analytics']['profile']['email_score'] = $email_score;
                $users['analytics']['profile']['is_picture'] = $picture;
                $users['analytics']['profile']['picture_score'] = $picture_score;
                $users['analytics']['profile']['is_connect'] = $userConnect;
                $users['analytics']['profile']['connect_score'] = $userConnect_score;
                $users['analytics']['profile']['is_share'] = $share;
                $users['analytics']['profile']['share_score'] = $share_score;
                $users['analytics']['profile']['total_score'] = $personalTotal;
                $users['analytics']['profile']['weight'] = ($socialWeightQuery->PERSON_PROFILE_SUMMARY);
                $users['analytics']['company']['name_is_match'] = $workName;
                $users['analytics']['company']['name_score'] = $workName_score;
                $users['analytics']['company']['is_address'] = $workAddress;
                $users['analytics']['company']['address_score'] = $workAddress_score;
                $users['analytics']['company']['is_staff'] = $employedHere;
                $users['analytics']['company']['staff_score'] = $employedHere_score;
                $users['analytics']['company']['is_start_month'] = $startDate;
                $users['analytics']['company']['start_month_score'] = $startDate_score;
                $users['analytics']['company']['start_year'] = $endDate_score;
                $users['analytics']['company']['start_year_score'] = $endDate;
                $users['analytics']['company']['total_score'] = $companyTotal;
                $users['analytics']['company']['weight'] = ($socialWeightQuery->PERSON_JOB_SUMMARY);
                
                $users['analytics']['final_score'] = ($companyTotal *($socialWeightQuery->PERSON_JOB_SUMMARY/100)) + ($personalTotal * ($socialWeightQuery->PERSON_PROFILE_SUMMARY/100));
                    
            }
            else{

                $users = NULL;
            }
            

            // If the id parameter doesn't exist return all the users

            if ($id === NULL)
            {
                // Check if the users data store contains users (in case the database result returns NULL)
                if ($users)
                {
                    // Set the response and exit
                    $this->response($users, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
                else
                {
                    // Set the response and exit
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No users were found'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
                }
            }

            // Find and return a single record for a particular user.

            $id = (int) $id;

            // Validate the id.
            if ($id <= 0)
            {
                // Invalid id, set the response and exit.
                $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
            }

            
            //Get The User id 

            $user = NULL;

            if (!empty($users))
            {
                    if (isset($users['user_id']) && $users['user_id'] == $id)
                    {
                        $user = $users;
                    }
                
            }

            if (!empty($user) && $userProfileQuery)
            {

                if($userWorkQuery){

                    $this->set_response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

                }

                else{

                    $this->set_response([
                        'status' => FALSE,
                        'message' => 'User Work Profile Does Not Exist'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
                    
                }
                
            }
            else
            {
                $this->set_response([
                    'status' => FALSE,
                    'message' => 'User Social Profile Does Not Exist'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

    }