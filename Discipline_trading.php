<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/Admin_controller.php';

class Discipline_trading extends Admin_controller
{
    public function __construct(){
        parent::__construct();
        $this->isAdmin();
    }

    public function index(){
        // $this->db->truncate('blm_discipline_trading_file_one');
        // $this->db->truncate('blm_discipline_trading_file_two');  blm_discipline_trading_Strategies_logs
        // $this->db->truncate('blm_discipline_trading_Strategies_logs');  
        // $this->db->truncate('blm_discipline_trading_Strategies');  
        if($this->input->post("submit")=="upload_files"){
            // pre($_POST);
            $this->load->library('simplex');
            if($_FILES["fileOne"] != ""){
                $config['upload_path'] = './site-assets/uploads/admin/';
                $config['allowed_types'] = '*';
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('fileOne'))
                {
                    $data1 = $this->upload->data();
                    $data = $data1;
                        $file = $data["full_path"];
                        $xlsx = $this->simplexlsx->parse($file);
                        unlink($file);
                        $cells = $xlsx->rows();
                    $darray = [];
                    foreach ($cells as $key => $csv_line) {
                        if ($key == 0) {
                            // pre($csv_line);
                            if(trim($csv_line[1]) != "Client" || trim($csv_line[10]) != "Net Qty" || trim($csv_line[12]) != "MTM G/L" || trim($csv_line[19]) != "Instrument Name"){
                                $this->session->set_flashdata('fail',"File one header name is not the same please check file and try again.");
                                redirect("admin/discipline-trading");
                            }
                            continue;
                        }
                        $rowarray = array();
                        $rowarray["client_id"]  = empty($csv_line[1]) ? "" : $csv_line[1];
                        $rowarray["net_qty"]    = empty($csv_line[10]) ? "" : $csv_line[10];
                        $rowarray["mtm_g_l"]    = empty($csv_line[12]) ? "" : $csv_line[12];
                        $rowarray["instrument"] = empty($csv_line[19]) ? "" : $csv_line[19];
                        $rowarray["for_date"] = $_POST["for_date"];

                        $darray[] = $rowarray ;
                        if($rowarray["net_qty"] == "General" || $rowarray["mtm_g_l"] == "General"){
                            $this->session->set_flashdata('fail',"Please change column(Net Qty and MTM G/L) format General to Number");
                            redirect("admin/discipline-trading");
                        }
                    }
                    // $this->db->truncate('blm_discipline_trading_file_one');
                    $this->db->where('for_date', $_POST["for_date"]);
                    $this->db->delete('blm_discipline_trading_file_one');
                    $chunk_darray = array_chunk($darray,200);
                    foreach ($chunk_darray as $dataTwoHundred) {
                        $this->db->insert_batch('blm_discipline_trading_file_one', $dataTwoHundred); 
                    }
                }else{
                    $this->session->set_flashdata('fail',$this->upload->display_errors());
                }
            }

            if($_FILES["filetwo"] != ""){
                $config['upload_path'] = './site-assets/uploads/admin/';
                $config['allowed_types'] = '*';
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('filetwo'))
                {
                    $data1 = $this->upload->data();
                    $data = $data1;
                    $file = $data["full_path"];
                    $xlsx = $this->simplexlsx->parse($file);
                    unlink($file);
                    $cells = $xlsx->rows();
                    $darray = [];
                    foreach ($cells as $key => $csv_line) {
                        if ($key == 0) {
                            if(trim($csv_line[1]) != "Client" || trim($csv_line[11]) != "Time"){
                                $this->session->set_flashdata('fail',"File two header name is not the same please check file and try again.");
                                redirect("admin/discipline-trading");
                            }
                            continue;
                        }
                        $rowarray = array();
                        $rowarray["client_id"]  = empty($csv_line[1]) ? "" : $csv_line[1];
                        $rowarray["timing"] = empty($csv_line[11]) ? "" : substr($csv_line[11], 11);
                        $rowarray["for_date"] = $_POST["for_date"];
                        $darray[] = $rowarray ;
                    }
                    $this->db->where('for_date', $_POST["for_date"]);
                    $this->db->delete('blm_discipline_trading_file_two');
                    // $this->db->truncate('blm_discipline_trading_file_two');
                    $chunk_darray = array_chunk($darray,200);
                    foreach ($chunk_darray as $dataTwoHundred) {
                        $this->db->insert_batch('blm_discipline_trading_file_two', $dataTwoHundred); 
                    }
                }else{
                    $this->session->set_flashdata('fail',$this->upload->display_errors());
                }
            }

            if(empty($this->session->flashdata('fail'))) {
                $this->session->set_flashdata('success','File uploaded successfully');
            }
        }
        $page_data['header_data'] = array('page_title'=>'Discipline Trading','side_nave'=>'discipline-trading','side_sub_nave'=>'discipline-trading','side_second_sub_nave'=>'');
        $this->admin_views($page_data, 'discipline_trading', array(), null);
    }
}