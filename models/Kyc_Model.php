<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kyc_Model extends CI_Model{

    public function getAllAgents()
    {

    }

    public function getAgent($id)
    {
        
        $checkAgent = $this->db
                                
                                ->where('PLATFORM_ID', $id['company'])
                                ->where('AGENT_ID', $id['id'])
                                ->get('people_agents');
                          
            $checkAgent = $checkAgent->row();
            $checkPending = $this->db->where('AGENT_ASSIGNED',$checkAgent->AGENT_ID)
                                     ->get('kyc_verification_request');
            $number = $checkPending->num_rows();

            $agentDetails['id'] = $checkAgent->AGENT_ID;
            $agentDetails['platform'] = $checkAgent->PLATFORM_ID;
            $agentDetails['name'] = $checkAgent->AGENT_NAME;
            $agentDetails['username'] = $checkAgent->AGENT_USERNAME;
            $agentDetails['email'] = $checkAgent->AGENT_EMAIL;
            $agentDetails['address'] = $checkAgent->AGENT_ADDRESS;
            $agentDetails['phone'] = $checkAgent->AGENT_PHONE;
            $agentDetails['active'] = $checkAgent->AGENT_ACTIVE;
            $agentDetails['pending_requests'] = $number;
            
        return $agentDetails;

    }

    
    
    public function getAgents($company)
    {
        $checkAgents = $this->db
                                ->where('PLATFORM_ID', $company)
                                ->get('people_agents');
                          
        $all = $checkAgents->result();
        
        foreach($all as $id){

            $checkPending = $this->db->where('AGENT_ASSIGNED',$id->AGENT_ID)
                                     ->get('kyc_verification_request');
            $number = $checkPending->num_rows();

            $agentDetails['id'] = $id->AGENT_ID;
            $agentDetails['platform'] = $id->PLATFORM_ID;
            $agentDetails['name'] = $id->AGENT_NAME;
            $agentDetails['username'] = $id->AGENT_USERNAME;
            $agentDetails['email'] = $id->AGENT_EMAIL;
            $agentDetails['address'] = $id->AGENT_ADDRESS;
            $agentDetails['phone'] = $id->AGENT_PHONE;
            $agentDetails['active'] = $id->AGENT_ACTIVE;
            $agentDetails['pending_requests'] = $number;
            
            $allDetails[] = $agentDetails;
     
        }
        
        return $allDetails ;
    }
    
    public function createAgent($data)
    {

        if($this->db->insert('people_agents', $data)){
            return true;
        }
    }

    public function updatePass($data)
    {

        $this->db->set('AGENT_PASS', $data['password']);
        $this->db->where('AGENT_ID', $data['id']);
        
        if($this->db->update('people_agents')){ 
            
            return TRUE;
        }

    }

    public function banAgent($data)
    {

        $this->db->set('AGENT_STATUS', $data['status']);
        $this->db->where('AGENT_ID', $data['id']);
        
        if($this->db->update('people_agents')){ 
            
            return TRUE;
        }

    }



    public function activateAgent($data)
    {

        $this->db->set('AGENT_STATUS', $data['status']);
        $this->db->where('AGENT_ID', $data['id']);
        
        if($this->db->update('people_agents')){ 
            
            return TRUE;
        }

    }

    public function deleteAgent($data)
    {

        $this->db->where('AGENT_ID', $data['id']);
        $this->db->where('PLATFORM_ID', $data['company']);
        if($this->db->delete('people_agents')){

            return true;
        }
    }

    public function addRequest($request)
    {
        
       $checkRequest = $this->db
                ->where('PEOPLE_ID', $request['details']['user'])
                ->where('PLATFORM_ID', $request['details']['platform'])
                ->get('kyc_request');

        if($checkRequest->num_rows() === 0){
    
            if($this->db->insert('kyc_request', $request['request'])){
                return true;
            }
    
    
        }
        
        

    }

    public function login($data)
    {

        $checkData = $this->db
                            ->where('AGENT_USERNAME', $data['user'])
                            ->or_where('AGENT_EMAIL', $data['user'])
                            ->get('people_agents');

        if($checkData->num_rows() === 0){

            return 'false';
            
        }
        else{

            if(password_verify($data['pass'], $checkData->row()->AGENT_PASS)){

                $trueData['pass'] = true;
                $trueData['data'] = $checkData->row();
                
                return $trueData;

            }
            else{

                return false;
                
            }

        }

    }

    public function assignRequest($request)
    {
        $checkRequest = $this->db
                             ->where('KYC_REQUEST_ID', $request['request'])
                             ->get('kyc_verification_request');
        if($checkRequest->num_rows() == 0){

            if($this->db->insert('kyc_verification_request',$request['data'])){
                return true;
            }
        }
    }
    
    public function getMyDetails($details)
    {

        $checkRequest = $this->db
                ->where('AGENT_ID', $details)
                ->get('people_agents');

        if($checkRequest->num_rows() === 0){
    
         return $checkRequest->row();
         return true;   
    
        }
    }

    public function getRequests()
    {
        
    }
}