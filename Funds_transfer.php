<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/Admin_controller.php';

class Funds_transfer extends Admin_controller
{
    public function __construct(){
        parent::__construct();
        $this->isAdmin();
    }

    public function bank_accounts(){
        $view_page_data['account_details'] = $this->Common_Model->getSelectedData("blm_bank_accounts",array(),'*',"all");
        $page_data['header_data'] = array('page_title'=>'Bank Accounts','side_nave'=>'funds_transfer','side_sub_nave'=>'bank_accounts','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'funds_transfer/bank_accounts', $view_page_data, null);
    }

    public function add_edit_bank_accounts($id = false)
    {
        if($this->input->post("submit")){
            $this->form_validation->set_rules('bank_name', 'Bank name', 'required');
            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('ac_number', 'Accounts Number', 'required');
            $this->form_validation->set_rules('ifsc', 'IFSC', 'required');
            $this->form_validation->set_rules('stock_type', 'Stock Type', 'required');
            $this->form_validation->set_rules('accounts_type', 'Accounts Type', 'required');

            if ($this->form_validation->run()) {
                $data = $_POST;
                if ($this->input->post("submit") == "submit") {
                    $data['status'] = 1;
                    unset($data['submit']);
                    $id = $this->Common_Model->insert_info('blm_bank_accounts', $data,true);
                    $this->session->set_flashdata('success','Bank account added success');
                } else {
                    $where = array("id"=>$data['submit']);
                    unset($data['submit']);
                    $this->Common_Model->update_info('blm_bank_accounts', $data,$where);
                    $this->session->set_flashdata('success','Bank account updated success');
                    $page_data['page_title'] = 'Bank account';
                }
                redirect(base_url("admin/funds_transfer/add_edit_bank_accounts/".$id));
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
                $page_data['page_title'] = 'Bank account';
            }
        }
        if($id){
            $view_page_data['account_detail'] = $this->Common_Model->getSelectedData("blm_bank_accounts",array());
            $_POST = $view_page_data['account_detail'];
            $_POST["submit"] = $view_page_data['account_detail']['id'];
        }
        $page_data['header_data'] = array('page_title'=>'Bank Accounts','side_nave'=>'funds_transfer','side_sub_nave'=>'bank_accounts','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'funds_transfer/add_edit_bank_accounts', null, null);
    }

    public function change_account_details_status()
    {
        if ($_POST['status'] == 'active') {
            $status_val = 1;
        }
        if ($_POST['status'] == 'inactive') {
            $status_val = 0;
        }

        $where = array("id"=>$_POST['id']);
        $result = $this->Common_Model->update_info("blm_bank_accounts", array('status' => $status_val), $where);

        if ($result) {
            echo 'success';
        } else {
            echo 'error';
        }
        exit;
    }

    public function cheque()
    {
        /*if($this->input->post("update")){
            $this->form_validation->set_rules('description', 'Description', 'required');
            if ($this->form_validation->run()) {
                $data = array("description"=>$this->input->post("description"));
                $where = array("id"=>10);
                $this->Common_Model->update_info('blm_cms_pages', $data,$where);
                $this->session->set_flashdata('success','Cheque details updated success');
                $page_data['page_title'] = 'Cheque details';
                redirect(base_url("admin/funds_transfer/cheque"));
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
                $page_data['page_title'] = 'Cheque details';
            }
        }else{
            $_POST = $view_page_data['cheque_detail'];
        }*/
        $page_data['cheque_list'] = $this->Common_Model->getSelectedData("blm_cms_pages",array("page_name"=>"funds_transfer_cheque"),"","all");
        // pre($view_page_data['cheque_list']);
        $page_data['header_data'] = array('page_title'=>'Cheque details','side_nave'=>'funds_transfer','side_sub_nave'=>'cheque','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'funds_transfer/cheque_list', null, null);
    }

    public function add_edit_cheque($id=null)
    {
        if($this->input->post("update")){
            $this->form_validation->set_rules('description', 'Description', 'required');
            $this->form_validation->set_rules('title', 'Title', 'required');
            if($this->form_validation->run()) {
                $data = $_POST;
                if ($this->input->post("update") == "Submit") {
                    $data["page_name"] = "funds_transfer_cheque";
                    unset($data['update']);
                    $id = $this->Common_Model->insert_info('blm_cms_pages', $data,true);
                    $this->session->set_flashdata('success','Bank account added success');
                } else {
                    $where = array("id"=>$data['update'],"page_name"=>"funds_transfer_cheque");
                    unset($data['update']);
                    $this->Common_Model->update_info('blm_cms_pages', $data,$where);
                    $this->session->set_flashdata('success','Bank account updated success');
                    $page_data['page_title'] = 'Bank account';
                }
                redirect(base_url("admin/funds_transfer/add_edit_cheque/".$id));

                $data = array("description"=>$this->input->post("description"));
                $where = array("id"=>$id);
                $this->Common_Model->update_info('blm_cms_pages', $data,$where);
                $this->session->set_flashdata('success','Cheque details updated success');
                $page_data['page_title'] = 'Cheque details';
                redirect(base_url("admin/funds_transfer/cheque"));
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
                $page_data['page_title'] = 'Cheque details';
            }
        }
        if($id){
            $view_page_data['cheque_detail'] = $this->Common_Model->getSelectedData("blm_cms_pages",array("id"=>$id,"page_name"=>"funds_transfer_cheque"));
            $_POST = $view_page_data['cheque_detail'];
            $_POST["update"] = $view_page_data['cheque_detail']['id'];
        }
        $page_data['header_data'] = array('page_title'=>'Cheque details','side_nave'=>'funds_transfer','side_sub_nave'=>'cheque','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'funds_transfer/cheque', null, null);
    }

    public function upi()
    {
        if($this->input->post("update")){
            $this->form_validation->set_rules('btn_link', 'Button link', 'required');
            $this->form_validation->set_rules('btn_text', 'Button text', 'required');
            $this->form_validation->set_rules('title', 'Title', 'required');
            if ($this->form_validation->run()) {
                $data = array();
                $data["btn_link"] = $this->input->post("btn_link");
                $data["btn_text"] = $this->input->post("btn_text");
                $data["title"] = $this->input->post("title");
                if($_FILES['banner_img']['name'] !=''){
                    $config['upload_path'] = './site-assets/uploads/cms_img/';
                    $config['file_name'] = time() . 'image3' . '-' . md5($_FILES['banner_img']['name'].time()) . '.jpg';
                    $config['allowed_types'] = '*';
                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if ($this->upload->do_upload("banner_img"))
                    {
                        $data1 = $this->upload->data();
                        $data["img"]  = $data1['file_name'];
                        $view_page_data['upi_detail'] = $this->Common_Model->getSelectedData("blm_cms_pages",array("id"=>11));
                        unlink('./site-assets/uploads/cms_img/'.$view_page_data['upi_detail']['img']);
                    }
                    else
                    {
                        $this->session->set_flashdata('fail','Error occurred in update image');
                    }
                }
                // pre($data);

                $where = array("id"=>11);
                $this->Common_Model->update_info('blm_cms_pages', $data,$where);
                $this->session->set_flashdata('success','UPI details updated success');
                $page_data['page_title'] = 'UPI details';
                redirect(base_url("admin/funds_transfer/UPI"));
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
                $page_data['page_title'] = 'UPI details';
            }
        }else{
            $view_page_data['upi_detail'] = $this->Common_Model->getSelectedData("blm_cms_pages",array("id"=>11));
            $_POST = $view_page_data['upi_detail'];
        }
        $page_data['header_data'] = array('page_title'=>'UPI details','side_nave'=>'funds_transfer','side_sub_nave'=>'upi','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'funds_transfer/upi', null, null);
    }

    public function payout_request()
    {
        $this->subadminAccessVerify(get_session('userId'), 'pages_section');
        if($this->input->post("submit")){
            $data = array(
                "status"=>$this->input->post("status"),
                "remark"=>$this->input->post("remark")
            );
            $this->Common_Model->update_info('blm_payout_request',$data, array("id"=>$this->input->post("id")));
            $this->session->set_flashdata('success','Status updated success');
            redirect(base_url('admin/funds_transfer/payout_request'));
        }
        $join_table = array(
            "blm_users" => array("join_con"=>"blm_users.id = blm_payout_request.user_id","type"=>"")
        );
        $page_data['payout_request_data'] = $this->Common_Model->getSelectedData('blm_payout_request',array(),"blm_payout_request.id,blm_payout_request.user_id, blm_payout_request.request_amount, blm_payout_request.status, blm_payout_request.created_at, blm_users.userid, blm_users.firstname, blm_users.lastname","all","id desc",$join_table);
        // pre($page_data['payout_request_data']);
        $page_data['page_title'] = 'Payout request';
        $page_data['header_data'] = array('page_title'=>'Payout request','side_nave'=>'funds_transfer','side_sub_nave'=>'payout_request','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'funds_transfer/payout_request', $page_data, null);
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
                <form metho="post">
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
                </form>
              </div>
            </div>
            <button type="submit" class="btn bg-gradient-primary" name="submit" value="update">Update</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        '; 
    }
}