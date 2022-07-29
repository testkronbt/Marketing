<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/Admin_controller.php';

class Client extends Admin_controller
{
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $representatives['clients_data'] = $this->Admin_model->get_client_with_representative_data();
        $page_data['header_data'] = array('page_title'=>'Client','side_nave'=>'client','side_sub_nave'=>'','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'client/index', $representatives, null);
    }

    public function change_representative($id = 0){
        if($this->input->post('update')){
            $this->form_validation->set_rules('representative_id', 'Representative name', 'required');
            if ($this->form_validation->run()) {
                $data = $_POST;
                unset($data['update']);
                $result = $this->Client_model->update_table_where_id("blm_users",$data, $id);
                if ($result) {
                    $this->session->set_flashdata('success','Representative updated success');
                    redirect(base_url("admin/client/change_representative/".$id));
                } else {
                    $this->session->set_flashdata('fail','Error occurred in adding representative');
                }
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
            }
        }
        $representative['client_data'] = $this->Client_model->get_client_by_id($id);
        $representative['representative_data'] = $this->Common_Model->getSelectedData("blm_representative",array(),'id,firstname,lastname',"all");
        $page_data['header_data'] = array('page_title'=>'Client','side_nave'=>'client','side_sub_nave'=>'','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'client/edit', $representative, null);
    }

    public function change_status_client()
    {
        if ($_POST['status'] == 'active') {
            $status_val = 1;
        }
        if ($_POST['status'] == 'inactive') {
            $status_val = 0;
        }

        $result = $this->Client_model->update_table_where_id("blm_users",array('user_status' => $status_val), $_POST['id']);

        if ($result) {
            echo 'success';
        } else {
            echo 'error';
        }
        exit;
    }

    public function change_client_block_status()
    {
        if ($_POST['status'] == 'active') {
            $status_val = 1;
        }
        if ($_POST['status'] == 'inactive') {
            $status_val = 0;
        }

        $result = $this->Client_model->update_table_where_id("blm_users", array('usr_block_status' => $status_val), $_POST['id']);

        if ($result) {
            echo 'success';
        } else {
            echo 'error';
        }
        exit;
    }

    public function client_profile($id = 0){
        if($this->input->post('submit') == "update_feedback"){
            $this->form_validation->set_rules('status', 'Status', 'required');
            if ($this->form_validation->run()) {
                $data = array("status"=>$this->input->post("status"));
                $fid = $this->input->post("id");
                $result = $this->Client_model->update_table_where_id("blm_feedback",$data, $fid);
                $this->session->set_flashdata('success','Feedback updated success');
                redirect(base_url("admin/client/client_profile/".$id."?tabaction=feedback"));
            
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
            }
        }else if($this->input->post('submit') == "change_representative"){
            $this->form_validation->set_rules('representative', 'Representative', 'required');
            if ($this->form_validation->run()) {
                $data = array("representative_id"=>$this->input->post("representative"));
                $fid = $this->input->post("id");
                $result = $this->Client_model->update_table_where_id("blm_users",$data, $fid);
                $this->session->set_flashdata('success','Representative change success');
                redirect(base_url("admin/client/client_profile/".$id."?tabaction=representative"));
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
            }
        }else  if($this->input->post("submit") == "payout_request"){
            $this->form_validation->set_rules('status', 'Status', 'required');
            if ($this->form_validation->run()) {
                $data = array(
                    "status"=>$this->input->post("status"),
                    "remark"=>$this->input->post("remark")
                );
                $this->Common_Model->update_info('blm_payout_request',$data, array("id"=>$this->input->post("id")));
                $this->session->set_flashdata('success','Status updated success');
                redirect(base_url('admin/client/client_profile/'.$id.'?tabaction=payout_request'));
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
            }
        }
        $page_data['feedback_list'] = $this->Common_Model->getSelectedData("blm_feedback",array("user_id"=>$id),"","all","id desc");
        $page_data['payout_request_list'] = $this->Common_Model->getSelectedData('blm_payout_request',array("user_id"=>$id),"id, user_id, request_amount, status, created_at","all","id desc");
        $page_data['client_data'] = $this->Client_model->get_client_by_id($id);
        $page_data['client_representative_data'] = $this->Common_Model->getSelectedData("blm_representative",array("id"=>$page_data['client_data']['representative_id']));
        $page_data['header_data'] = array('page_title'=>'Client','side_nave'=>'client','side_sub_nave'=>'','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'client/client_profile', $page_data, null);
    }

    public function feedback_form()
    {
        // pre("tee");
        $this->subadminAccessVerify(get_session('userId'), 'pages_section');
        // $result = $this->Common_Model->getSelectedData('blm_global_constant', array("id"=>,"show_in_admin"=>1),"id,name,value,input_type");
        $feedback_detail = $this->Common_Model->getSelectedData("blm_feedback",array("id"=>$this->input->post("id")));
        $user_detail = $this->Common_Model->getSelectedData("blm_users",array("id"=>$feedback_detail["user_id"]));
        // pre($feedback_detail,false);
        // pre($user_detail);
        echo '
            <div class="card card-warning">
              <div class="card-header">
                  <h3 class="card-title">Edit Feedback Details</h3>
              </div>
              <div class="card-body">
                <input type="hidden" name="id" value="'.$feedback_detail['id'].'">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Client (Userid) </strong>
                        <p >'.$user_detail["firstname"].' '.$user_detail["lastname"].'('.$user_detail["userid"].')</p>
                    </div>
                    <div class="col-md-6">
                        <label>
                            <strong>Date</strong>
                        </label>
                        <p>'.date("d-m-Y", strtotime($feedback_detail["created_at"])).'</p>
                    </div>
                    <div class="col-md-12">
                        <label><strong>Category</strong></label>
                        <div class="w-100">'.$feedback_detail["feedback_category"].'</div>
                    </div>
                    <div class="col-md-12">
                        <label><strong>Description</strong></label>
                        <div class="w-100">'.$feedback_detail["comments"].'</div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control status_str" name="status">
                                <option '.($feedback_detail["status"] == "Pending" ? "selected" : "").'>Pending</option>
                                <option '.($feedback_detail["status"] == "Complete" ? "selected" : "").'>Complete</option>
                                <option '.($feedback_detail["status"] == "In Process" ? "selected" : "").'>In Process</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <button type="submit" class="btn bg-gradient-primary" name="submit" value="update_feedback">Update</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        ';
    }

    public function change_representative_form()
    {
        $this->subadminAccessVerify(get_session('userId'), 'pages_section');
        $client_data = $this->Client_model->get_client_by_id($this->input->post('id'));
        $client_representative_data = $this->Common_Model->getSelectedData("blm_representative",array("id"=>$client_data["representative_id"]));
        // pre($client_representative_data,false);
        $client_representative_list = $this->Common_Model->getSelectedData("blm_representative",array(),"","all");
        // pre($client_representative_list);
        $str = '
            <div class="card card-warning">
              <div class="card-header">
                  <h3 class="card-title">Change Representative</h3>
              </div>
              <div class="card-body">
                <input type="hidden" name="id" value="'.$this->input->post('id').'">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control status_str" name="representative">';
        foreach ($client_representative_list as $key => $value) {
            $str .='            <option '.($client_representative_data["id"] == $value["id"] ? "selected" : "").' value="'.$value["id"].'">'.$value["firstname"].' '.$value["lastname"].'</option>';
        }
        $str .='            </select>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <button type="submit" class="btn bg-gradient-primary" name="submit" value="change_representative">Update</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        ';
        echo $str;
    }

    public function payout_request_edit_form()
    {
        // pre("tee");
        $this->subadminAccessVerify(get_session('userId'), 'pages_section');
        // $result = $this->Common_Model->getSelectedData('blm_global_constant', array("id"=>$this->input->post("id"),"show_in_admin"=>1),"id,name,value,input_type");

        $join_table = array(
            "blm_users" => array("join_con"=>"blm_users.id = blm_payout_request.user_id","type"=>"")
        );
        $result = $this->Common_Model->getSelectedData('blm_payout_request',array("blm_payout_request.id"=>$this->input->post("id")),"blm_payout_request.id, blm_payout_request.remark, blm_payout_request.request_amount, blm_payout_request.status, blm_users.userid, blm_users.firstname, blm_users.lastname","single","",$join_table);

        echo '
            <div class="card card-warning">
              <div class="card-header">
                  <h3 class="card-title">Edit Details</h3>
              </div>
                <div class="card-body">
                    <input type="hidden" name="id" value="'.$result['id'].'">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Client name</label>
                                <div>'.$result['firstname'].' '.$result['lastname'].' (<b>'.$result['userid'].'</b>)</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Request amount</label>
                                <input type="text" class="form-control" placeholder="Enter ..." value="'.$result['request_amount'].'" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Request amount</label>
                                  <select class="form-control" name="status">
                                    <option '.($result['status'] == "Pending" ? "selected": "").'>Pending</option>
                                    <option '.($result['status'] == "Completed" ? "selected": "").'>Completed</option>
                                    <option '.($result['status'] == "Cancel" ? "selected": "").'>Cancel</option>
                                  </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Remark</label>
                                  <textarea class="form-control" name="remark">'.$result['remark'].'</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn bg-gradient-primary" name="submit" value="payout_request">Update</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        '; 
    }
}