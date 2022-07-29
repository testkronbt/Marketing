<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/Admin_controller.php';

class Admin_faq extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->isAdmin();
        $this->subadminAccessVerify(get_session('userId'), 'faq_section');
    }
    public function faq()
    {
        $faqdata = $this->Common_Model->getSelectedData('faq','','',$rows="all","id desc");
        $faqdata['tabledata'] = $faqdata;

        $page_data['header_data'] = array('page_title'=>'FAQ','side_nave'=>'page','side_sub_nave'=>'support','side_second_sub_nave'=>'faq');
        $this->admin_views($page_data, 'faq', $faqdata, null);
    }

    public function add_edit_faq($data = '')
    {
        $faqdata['fatcatdata'] = $this->Common_Model->getSelectedData('faq_categories',array("status"=>1),'',$rows="all");
        if ($data) {
            $faqdata['editdata'] = $this->Common_Model->getSelectedData('faq',array('id'=>$data));
        }
        $page_data['header_data'] = array('page_title'=>'FAQ','side_nave'=>'page','side_sub_nave'=>'support','side_second_sub_nave'=>'faq');
        $this->admin_views($page_data, 'add_edit_faq', $faqdata, null);
    }
    public function add_faq()
    {
        // pre($_POST);
        $this->form_validation->set_rules('question', 'Question Field', 'required');
        $this->form_validation->set_rules('answer', 'Answer Field', 'required');
        $this->form_validation->set_rules('faq_categories', 'Categories', 'required');
        $this->form_validation->set_rules('faq_subcategories', 'Sub Categories', 'required');
        if ($this->form_validation->run() != false) {
            if(isset($_POST['status'])){
                $data = 1;
            }else{
                $data = 0;
            }

            $faq_qus_ans = array(
                'question' => $this->input->post('question'),
                'answer' => $this->input->post('answer'),
                'faq_categories'=>$this->input->post('faq_categories'),
                'faq_subcategories'=>$this->input->post('faq_subcategories'),
                'status' => $data
            );
            $faqstatus = $this->Common_Model->insert_info('faq', $faq_qus_ans);
            if ($faqstatus) {
                $this->session->set_flashdata('success', 'insert success');
                redirect(base_url('Admin/Faq'));
            } else {
                $this->session->set_flashdata('fail', 'somthing Wrong...!');
                redirect(base_url('Admin/AddFaq'));
            }
        } else {

            $this->add_edit_faq();
        }

    }
    public function edit_faq($id)
    {

        $this->form_validation->set_rules('question', 'Question Field', 'required');
        $this->form_validation->set_rules('answer', 'Answer Field', 'required');
        $this->form_validation->set_rules("faq_categories", 'Categories Field', 'required');
        $this->form_validation->set_rules("faq_subcategories", 'Sub Categories Field', 'required');
        if ($this->form_validation->run() != false) {
            if(isset($_POST['status'])){
                $data = 1;
            }else{
                $data = 0;
            }
            $faq_qus_ans = array(
                'question' => $this->input->post('question'),
                'answer' => $this->input->post('answer'),
                'faq_categories'=> $this->input->post('faq_categories'),
                'faq_subcategories'=> $this->input->post('faq_subcategories'),
                'status' => $data
            );
             $faqstatus = $this->Common_Model->update_info('faq',$faq_qus_ans,array('id'=>$id) );
            if ($faqstatus) {
                $this->session->set_flashdata('success', 'update success');
                redirect(base_url('Admin/Faq'));
            } else {
                $this->session->set_flashdata('fail', 'something wrong...!');
                redirect(base_url('Admin/AddFaq'.'/'.$id));
            }
        } else {

            $this->add_edit_faq($id);
        }

    }
    function del_faq($id)
    {
        $faqstatus = $this->Common_Model->del_faq('faq',$id);   
        if ($faqstatus) {
            $this->session->set_flashdata('success', 'Delete success');
            redirect(base_url('Admin/Faq'));
        } else {
            $this->session->set_flashdata('fail', 'something wrong...!');
            redirect(base_url('Admin/Faq'));
        }   
    }
    function ChangeFaqStatus(){
        if ($_POST['status'] == 'active') {
            $status_val = 1;
        }
        if ($_POST['status'] == 'inactive') {
            $status_val = 0;
        }

        $result = $this->Common_Model->update_info('faq',array('status' => $status_val),array('id'=>$_POST['id']) );
        if ($result) {
            echo 'success';
        } else {
            echo 'error';
        }
        exit;
    }
}