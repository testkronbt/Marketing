<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/Admin_controller.php';

class Representative extends Admin_controller
{
    public function __construct(){
        parent::__construct();
        $this->isAdmin();
    }

    public function index(){
        $representatives['representatives_data'] = $this->Common_Model->getSelectedData("blm_representative",array(),'*',"all");
        $page_data['header_data'] = array('page_title'=>'Representative','side_nave'=>'representatives','side_sub_nave'=>'representatives','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'representative/index', $representatives, null);
    }

    public function add()
    {
        $page_data['header_data'] = array('page_title'=>'Representative','side_nave'=>'representative','side_sub_nave'=>'representative','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'representative/add', null, null);
    }

    public function insert_representative()
    {
        $this->form_validation->set_rules('firstname', 'First name', 'required');
        $this->form_validation->set_rules('lastname', 'Last name', 'required');
        $this->form_validation->set_rules('designation', 'Designation', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[blm_representative.email]');
        $this->form_validation->set_rules('phoneno', 'Phone', 'required|numeric|max_length[12]');
        $this->form_validation->set_rules('about_me', 'About Me', 'required');

        if ($this->form_validation->run()) {
            $data = $_POST;
            $data['status'] = 0;
            $result = $this->Common_Model->insert_info('blm_representative', $data);

            if ($result) {
                $this->session->set_flashdata('success','Representative added success');
                $page_data['page_title'] = 'Add representative';
                redirect(base_url("admin/representative/add"));
            } else {
                $this->session->set_flashdata('fail','Error occurred in adding representative');
                $page_data['page_title'] = 'Add representative';
                redirect(base_url("admin/representative/add"));
            }
        } else {
            $this->session->set_flashdata('fail','Please check all field are required');
            $page_data['page_title'] = 'Add representative';
            redirect(base_url("admin/representative/add"));
        }
    }

    public function edit($id = 0)
    {
        if($this->input->post('update')){
            $this->form_validation->set_rules('firstname', 'First name', 'required');
            $this->form_validation->set_rules('lastname', 'Last name', 'required');
            $this->form_validation->set_rules('designation', 'Designation', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('phoneno', 'Phone', 'required|numeric|max_length[12]');

            if ($this->form_validation->run()) {
                $data = $_POST;
                unset($data['update']);
                $result = $this->Client_model->update_table_where_id("blm_representative",$data, $id);

                if ($result) {
                    $this->session->set_flashdata('success','Representative updated success');
                    redirect(base_url("admin/representative/edit/".$id));
                } else {
                    $this->session->set_flashdata('fail','Error occurred in adding representative');
                }
            } else {
                $this->session->set_flashdata('fail','Please check all field are required');
            }
        }
        $representative['representative_reviews_and_reating'] = $this->Client_model->get_representative_reviews_and_reating_by_id($id);
        $representative['representative_data'] = $this->Client_model->get_representative_by_id($id);
        $page_data['header_data'] = array('page_title'=>'Edit Representative','side_nave'=>'representatives','side_sub_nave'=>'representatives','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'representative/edit', $representative, null);
    }

    public function change_status_representative()
    {
        if ($_POST['status'] == 'active') {
            $status_val = 1;
        }
        if ($_POST['status'] == 'inactive') {
            $status_val = 0;
        }

        $result = $this->Client_model->update_table_where_id("blm_representative", array('status' => $status_val), $_POST['id']);

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
}