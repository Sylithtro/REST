<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
use LinkedIn\Client;
use LinkedIn\Scope;

session_start();
class Api extends CI_Controller {

    public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url'));
        $this->load->library(array('session'));
        $this->load->model('Rest_Model');
        //require_once APPPATH . "/vendor/autoload.php";
        
        $this->session->fbAppId = '162552027929950';
        $this->session->fbAppSecret = '9cc53a90f739163fdadcce913a24de7f';
        $this->session->id = 7086;

        

        

	}

    public function index()
    {

    
            
        if($this->input->post('search')){

            $post = $this->input->post('search');

            $cSession = curl_init(); 
            
            curl_setopt($cSession,CURLOPT_URL,"http://www.google.com/search?q=".$post."");
            curl_setopt($cSession,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($cSession,CURLOPT_HEADER, false); 
            
            $result=curl_exec($cSession);
            
            curl_close($cSession);
            
            $data['result'] = $result;
            
            $this->load->view("api_landing", $data);
        }   
        
        else{

            redirect('/welcome');
        }
        
    }

    public function login()
    {
        
        $fb = new Facebook\Facebook([
        'app_id' => $this->session->fbAppId, // Replace {app-id} with your app id
        'app_secret' => $this->session->fbAppSecret,
        'default_graph_version' => 'v3.0',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        
            
        $permissions = ['email','user_posts','user_photos','user_likes','user_age_range','user_gender','user_birthday','user_events','user_friends','user_friends','user_location','user_hometown','user_status','user_link','user_tagged_places']; // Optional permissions
        $loginUrl = $helper->getLoginUrl(base_url().'api/callback', $permissions);
        
        $data['login'] = $loginUrl;
        

            $this->load->view("header");
            $this->load->view("facebook", $data);
            $this->load->view("footer");
        
    }

    public function callback()
    {
        
        if ( isset( $_GET['state'] ) ) {
        $_SESSION['FBRLH_state']=$_GET['state'];
        }
 
        $fb = new Facebook\Facebook([
        'app_id' => $this->session->fbAppId, // Replace {app-id} with your app id
        'app_secret' => $this->session->fbAppSecret,
        'default_graph_version' => 'v3.0',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        
        try {
        $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
        }

        if (! isset($accessToken)) {
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
        }
        exit;
        }
        // Logged in
        
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
       
        

           
            $this->load->view("header");
            $this->load->view("logged");
            $this->load->view("footer");
        
        

        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
            exit;
        }

        echo '<h3>Long-lived</h3>';
        var_dump($accessToken->getValue());
        }

        $_SESSION['fb_access_token'] = (string) $accessToken;

        $this->session->usersId = '';

    }

    public function details()
    {
        $fb = new Facebook\Facebook([
        'app_id' => $this->session->fbAppId, // Replace {app-id} with your app id
        'app_secret' => $this->session->fbAppSecret,
        'default_graph_version' => 'v3.0',
        ]);
        $fields = 'id,location,hometown,email,name,birthday,taggable_friends,picture,gender,favorite_teams,age_range,cover,favorite_athletes,link,inspirational_people,significant_other,updated_time,timezone,verified,friends,devices,install_type,installed';
        try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get('/me?fields='.$fields.'' , $_SESSION['fb_access_token']);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
        }

        $user = $response->getGraphNode();

        foreach($user as $key => $details){

            echo '<h4>'.$key.'</h4>';
            echo '<pre>'; print_r($details); echo '</pre>';

            echo '<br/>';
            
        }

        
        

        
    }

    
    public function permissions()
    {

        $this->load->view("header");
        $this->load->view("social");
        $this->load->view("footer");

    }

    public function creterion()
    {   

        if($this->input->get('lender')){

            $lender = $this->input->get('lender');
            if($lender == 1){

                $data['permission'] = array(
                    'events' => array("Soji's Birthday", 'Your wedding', 'Shiloh 2016'),
                    'friends' => array('Femi Bejide', 'Soji Okunuga','MO','Dami','Olamide'),
                    'location' => array('Lokoja', 'Ikorodu', 'Surulere', 'Victoria Island')
                );

            }

            elseif ($lender == 2) {

                $data['permission'] = array(
                    'events' => array("Imagine Dragons Concert", 'Baby Peniels Naming Ceremony', 'CreditClan Platform Launch'),
                    'friends' => array('Jenifer', 'Funmi','Joshua','Ibukun','Victor'),
                    'location' => array('Ikeja', 'Mr Biggs', 'Ajah', 'LASU'),
                    'likes' => array('J. Cole', 'Brymo', 'FIFA 18', 'G.O.T')
                );
            }
            
        }
        
        
        $this->load->view("header");
        $this->load->view("criteria", $data);
        $this->load->view("footer");
        
    }

    public function social_match()
    {

        $this->load->view("header");
        $this->load->view("social_match");
        $this->load->view("footer");
        
    }
    public function linkedIn()
    {

        $client = new LinkedIn\Client(
            '86jlrsi8gjiuje',
            'CIK2VR70WSp1yJrh'
        );

        $this->session->redirect_url = base_url() . 'api/linkedin_result';

        $client->setRedirectUrl($this->session->redirect_url);

        $redirectUrl = $client->getRedirectUrl();

        $scopes = new LinkedIn\Scope;
        $scopes = [
            Scope::READ_BASIC_PROFILE, 
            Scope::READ_EMAIL_ADDRESS,
            Scope::MANAGE_COMPANY,
            Scope::SHARING,
        ];
        $loginUrl = $client->getLoginUrl($scopes);

        $data['loginurl'] = $loginUrl;

        

        $this->load->view("header");
        $this->load->view("linkedin", $data);
        $this->load->view("footer");
    }


    public function linkedIn_result()
    {
        
        
        $client = new LinkedIn\Client(
            '86jlrsi8gjiuje',
            'CIK2VR70WSp1yJrh'
        );
          
        $client->setRedirectUrl($this->session->redirect_url);
            
        
       

        $accessToken = $client->getAccessToken($_GET['code']);

        

        $this->session->token = $accessToken;

        $profile = $client->get(
            'people/~:(id,email-address,first-name,last-name,skills,num-connections,location,industry,headline,current-share,summary,specialties,positions,picture-url,public-profile-url)'
        );
        
        $data['profile'] = $profile;

            $data['weight'] = $this->Rest_Model->getSocialWeight();
            $data['user_profile'] = $this->Rest_Model->getUserDetails();
            // Social Profile Comparism starts
           //Compare Profile Name With Social Profile Name 
           $score = 0;
           $profile_score = 0;
           $email = false;
           $first = false;
           $last = false;
           $isJobCorrect = 0;
           $userCompany = '';
           $userCompanyName = '';
           $userStartMonth = false;
           $userStartYear = false;
           $isTrueLocation = false;
           $userCompanyLocation = '';
           $aCurrentStaff = false;
           $socialData = array();


           $firstNameCheck = strpos($data['user_profile']->LEGAL_NAME, $data['profile']['firstName'] );

           if($firstNameCheck !== false){
            $first = true;
           }

           $lastNameCheck = strpos($data['user_profile']->LEGAL_NAME, $data['profile']['lastName']);

           if($lastNameCheck !== false){
            $last = true;
           }

           if($first == true || $last == true){            
            $score = $score + $data['weight']->person_profile_name;
            $profile_score = 50;
           }


           

           //Compare Profile Email With Social Profile Email 

           //Build Array
           $emailCheck = strpos($data['user_profile']->EMAIL, $data['profile']['emailAddress'] );

           if($emailCheck !== false){

            $score = $score + $data['weight']->person_profile_email;

            $profile_score = $profile_score + 50;

            $email = true;

           }


           if($first == true || $last == true){

            $analytics['linkedIn']['profile']['isNameMatched'] = true;
           }

           else{
               $analytics['linkedIn']['profile']['isNameMatched'] = false;
           }


           if(array_key_exists('pictureUrl', $data['profile'])){

                $analytics['linkedIn']['profile']['isPictureSet'] = true;
           }
           else{

            $analytics['linkedIn']['profile']['isPictureSet'] = false;
           }


           /*$userState = $this->Rest_Model->isMatchHome($data['user_profile']->HOME_STATE);

           
           $HomeState = strpos($userState->STATE_NAME, $data['profile']['emailAddress'] );

           $data['state'] = $userState;*/

            


           // Social Profile Comparism Ends

           //Job Comparism Starts
           $ci = 0;
           $count = 0;
           $occupationQuery = $this->Rest_Model->getUserWork($this->session->id);
           $userCompanyName = $occupationQuery->COMPANY_OF_WORK_ID;
            if(array_key_exists('values', $profile['positions'])){
                foreach ($profile['positions']['values'] as $key => $value) {
                
                   // echo $key . '<br />';
                    if(array_key_exists('company', $value)){

                        


                        foreach( $value['company'] as $company){
                            if($company = $userCompanyName){

                                $count = $ci;
                                $isJobCorrect = true;

                                $data['work'] = $occupationQuery->COMPANY_OF_WORK_ID;
                                if($isJobCorrect){
                                    $userCompany = true;
                                }
                                if(array_key_exists('month', $value['startDate'])){
                                    if($value['startDate']['month'] == $occupationQuery->WORK_START_DATE){

                                        $userStartMonth = true;
                                    }
                                    
                                }
                                if(array_key_exists('year', $value['startDate'])){
                                    if($value['startDate']['year'] == $occupationQuery->WORK_END_DATE){

                                        $userStartYear = true;
                                    }
                                    
                                }

                                if(array_key_exists('size', $value['company'])){
                                    if($value['isCurrent'] == $occupationQuery->CURRENTLY_WORK_HERE){

                                        $aCurrentStaff = true;
                                    }
                                    else{

                                        $aCurrentStaff = false;
                                    }
                                }
                                if(array_key_exists('title', $value['company'])){
                                    if($value['isCurrent'] == $occupationQuery->CURRENTLY_WORK_HERE){

                                        $aCurrentStaff = true;
                                    }
                                    else{

                                        $aCurrentStaff = false;
                                    }
                                }

                                if(array_key_exists('name', $value['location'])){
                                   
                                    $locationCheck = strpos($occupationQuery->ADDRESS, $value['location']['name'] );
                                    
                                    if($locationCheck !== false){

                                        $isTrueLocation = true;
                                        $userCompanyLocation = $occupationQuery->ADDRESS;
                                    }
                                    
                                        
                                
                                }
                                
                                
                                $analytics['linkedIn']['company']['isNameMatched'] = $userCompany;
                                $analytics['linkedIn']['company']['name'] = $userCompanyName;
                                $analytics['linkedIn']['company']['isStartMonthMatch'] = $userStartMonth;
                                $analytics['linkedIn']['company']['startMonth'] = $occupationQuery->WORK_START_DATE;
                                $analytics['linkedIn']['company']['isYearMatch'] = $userStartYear;
                                $analytics['linkedIn']['company']['startYear'] = $occupationQuery->WORK_END_DATE;
                                $analytics['linkedIn']['company']['isStatusMatch'] = $aCurrentStaff;
                                $analytics['linkedIn']['company']['locationMatch'] = $isTrueLocation;
                                $analytics['linkedIn']['company']['location'] = $userCompanyLocation;
                                $analytics['linkedIn']['company']['job_score'] = 0;
                                
                                
                                
                                
                                $data['score'] = $score;
                                $analytics['linkedIn']['profile']['isEmailMatched'] = $email;
                                $analytics['linkedIn']['profile']['profile_score'] = $profile_score;

                                
                                $socialData['platform'] = 'linkedIn';
                                $socialData['user_id'] = $this->session->id;
                                if(isset($profile['positions']['values'][$count]['company']['name'])){$socialData['company']['name'] = $profile['positions']['values'][$count]['company']['name'];}else{$socialData['company']['name'] = NULL;}
                                if(isset($profile['positions']['values'][$count]['startDate']['month'])){$socialData['company']['startMonth'] = $profile['positions']['values'][$count]['startDate']['month'];}else{$socialData['company']['startMonth'] = NULL;}
                                if(isset($profile['positions']['values'][$count]['startDate']['year'])){$socialData['company']['startYear'] = $profile['positions']['values'][$count]['startDate']['year'];}else{$socialData['company']['startYear'] = NULL;}
                                if(isset($profile['positions']['values'][$count]['location']['name'])){$socialData['company']['location'] = $profile['positions']['values'][$count]['location']['name'];}else{$socialData['company']['location'] = NULL;}
                                $socialData['company']['isCurrent'] = $profile['positions']['values'][$count]['isCurrent'];
                                if(isset($profile['positions']['values'][$count]['title'])){$socialData['company']['position'] = $profile['positions']['values'][$count]['title'];}
                                if(isset($profile['id'])){$socialData['profile']['id'] = $profile['id'];}else{$socialData['profile']['id'] = NULL;}
                                if(isset($profile['emailAddress'])){$socialData['profile']['email'] = $profile['emailAddress'];}else{$socialData['profile']['email'] = NULL;}
                                $socialData['profile']['name'] = $profile['firstName'] . ' ' . $profile['lastName'];
                                $socialData['profile']['firstName'] = $profile['firstName'];
                                $socialData['profile']['lastName'] = $profile['lastName'];
                                if(isset($profile['pictureUrl'])){$socialData['profile']['picture'] = $profile['pictureUrl'];}else{$socialData['profile']['picture'] = NULL;}
                                if(isset($profile['headline'])){$socialData['profile']['headline'] = $profile['headline'];}else{$socialData['profile']['headline'] = NULL;}
                                if(isset($profile['industry'])){$socialData['profile']['industry'] = $profile['industry'];}else{$socialData['profile']['industry'] = NULL;}
                                if(isset($profile['location']['name'])){$socialData['profile']['location'] = $profile['location']['name'];}else{$socialData['profile']['location'] = NULL;}
                                if(isset($profile['numConnections'])){$socialData['profile']['connections'] = $profile['numConnections'];}else{$socialData['profile']['connections'] = NULL;}
                                if(isset($profile['publicProfileUrl'])){$socialData['profile']['link'] = $profile['publicProfileUrl'];}else{$socialData['profile']['link'] = NULL;}
                                
                                


                                

                                $data['analytics'] = $analytics;
                                $data['count'] = $count;
                                $data['social'] = $socialData;

                                $finalAnalysis = json_encode($analytics);

                                //if($this->Rest_Model->createSocial($socialData)){

                                                                
                                    
                                //}



                                $ci++;
                            }
                            
                        }

                        if($this->Rest_Model->createSocial($socialData)){

                            $this->load->view("header");
                            $this->load->view("linkedin_result", $data);
                            $this->load->view("footer");


                        }
                                    

                        
                        
                        
                        
                        
                        /*if(array_key_exists('industry', $value['company'])){
                            echo $value['company']['industry'] . '<br />';
                        }
                        
                        if(array_key_exists('size', $value['company'])){
                            echo $value['company']['size'] . '<br />';
                        }
                        
                       

                        
                        echo $value['title'] . '<br />';
                        
                        
                        echo '<br />';
                        echo '<br />';*/

                    }
                    
                // print_r($value);
                
            }
        }      

        
    }
}