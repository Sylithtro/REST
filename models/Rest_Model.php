<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Rest_Model extends CI_Model{

    
    //Analytics Settings Query

    public function getSocialWeight()
    {
        $checkWeight = $this->db->get('analytics_settings_social');
                          
        return $checkWeight->row();

    }

    public function getUserDetails($user){

        $checkUser = $this->db->where('PEOPLE_ID', $user)->get('people_customers');
                          
        return $checkUser->row();

    }

    public function isMatchHome($home_id){

        $checkHome = $this->db->where('STATE_ID', $home_id)->get('states');
                    
        return $checkHome->row();

    }

    public function getUserWork($work_id)
    {

        $checkWork = $this->db->where('PEOPLE_ID', $work_id)->get('people_work');
                    
        return $checkWork->row();

    }

    public function getOccupation($occupation_id)
    {

        $checkOccupation = $this->db->where('PEOPLE_ID', $occupation_id)->get('people_work');
                    
        return $checkOccupation->row();

    }

    public function getSocial($user)
    {
        $checkSocial = $this->db->where('PEOPLE_ID', $user)->get('people_social');
                    
        return $checkSocial->row();
    }

    //Create Social Analytics Data
    public function createSocial($data)
    {
        $checkWork = $this->db->where('PEOPLE_ID', $data['user_id'])->get('people_social');

        //Check if data already exists and update user social data
        if($checkWork->num_rows() > 0){

            

            $this->db->set('SOCIAL_MEDIA_ID', $data['profile']['id']);
            $this->db->set('SOCIAL_MEDIA_NAME', $data['profile']['name']);
            $this->db->set('SOCIAL_PROFILE', $data['profile']['link']);
            $this->db->set('SOCIAL_HEADLINE', $data['profile']['headline']);
            $this->db->set('PICTURE_URL', $data['profile']['picture']);
            $this->db->set('SOCIAL_PLATFORM', $data['platform']);
            $this->db->set('COMPANY_OF_WPRK_ID', $data['company']['name']);
            $this->db->set('CURRENTLY_WORK_HERE', $data['company']['isCurrent']);
            $this->db->set('WORK_ADDRESS', $data['company']['location']);
            $this->db->set('ADDRESS', $data['profile']['location']);
            $this->db->set('WORK_START_DATE', $data['company']['startMonth']);
            $this->db->set('WORK_END_DATE', $data['company']['startYear']);
            $this->db->set('POSITION', $data['company']['position']);
            $this->db->set('INDUSTRY', $data['profile']['industry']);
            $this->db->set('FIRST_NAME', $data['profile']['firstName']);
            $this->db->set('LAST_NAME', $data['profile']['lastName']);
            $this->db->set('DATE_MODIFIED', date("Y-m-d H:i:s"));
            $this->db->set('CONNECTIONS', $data['profile']['connections']);
            $this->db->set('EMAIL', $data['profile']['email']);
            $this->db->where('PEOPLE_ID', $data['user_id']);
            
            if($this->db->update('people_social')){ 
                
                return TRUE;
            }

        }

        //If data does not exist Insert new social data
        else{

            $newData = array(

                'SOCIAL_MEDIA_ID'=> $data['profile']['id'],
                'SOCIAL_MEDIA_NAME'=> $data['profile']['name'],
                'SOCIAL_PROFILE'=> $data['profile']['link'],
                'PEOPLE_ID'=> $data['user_id'],
                'SOCIAL_HEADLINE'=> $data['profile']['headline'],
                'PICTURE_URL'=> $data['profile']['picture'],
                'SOCIAL_PLATFORM'=> $data['platform'],
                'COMPANY_OF_WPRK_ID'=> $data['company']['name'],
                'CURRENTLY_WORK_HERE'=> $data['company']['isCurrent'],
                'WORK_ADDRESS'=> $data['company']['location'],
                'ADDRESS'=> $data['profile']['location'],
                'WORK_START_DATE'=> $data['company']['startMonth'],
                'WORK_END_DATE'=> $data['company']['startYear'],
                'POSITION'=> $data['company']['position'],
                'INDUSTRY'=> $data['profile']['industry'],
                'FIRST_NAME'=> $data['profile']['firstName'],
                'LAST_NAME'=> $data['profile']['lastName'],
                'DATE_ADDED'=> date("Y-m-d H:i:s"),
                'CONNECTIONS' => $data['profile']['connections'],
                'EMAIL'=> $data['profile']['email']
            
            );

            if($this->db->insert('people_social', $newData)){
                return true;
            }
        }

    }


}