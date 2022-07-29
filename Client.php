 <?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/Front_controller.php';
class Client extends Front_controller {
    public function __construct() {
        parent::__construct();
        $this->User_model->checkUserIsLoginAndPermission();
    }

    public function get_my_representative() {
        $page_data["my_reviews_and_reating"] = $this->Common_Model->getSelectedData('blm_reviews_and_rating', array(
            'user_id' => $this->loginUserDetails["id"],
            'representative_id' => $this->loginUserDetails["representative_id"],
        ));
        if($this->input->post("submit") == "review_and_rating"){
            $querydata = array(
                'rating' => $this->input->post('rating'),
                'review' => $this->input->post('review'),
                'user_id' => $this->loginUserDetails["id"],
                'representative_id' => $this->loginUserDetails["representative_id"],
            );
            if(count($page_data["my_reviews_and_reating"]) == 0 ){
                $this->Common_Model->insert_info('blm_reviews_and_rating',$querydata);
            }else{
                $where_array = array(
                    "user_id"=>$querydata["user_id"],
                    "representative_id"=>$querydata["representative_id"]
                );
                $this->Common_Model->update_info('blm_reviews_and_rating',$querydata,$where_array);
            }
            $this->session->set_flashdata('success','Review and rating submitted success');
            $this->User_model->updateRepresentativeAverage($this->loginUserDetails["representative_id"]);
            redirect(base_url('my-representative'));
        }
        $result = $this->Common_Model->get_data_order_by('blm_cms_pages', array("page_name"=>"representative"));
        $page_data['page_data'] = array();
        foreach ($result as $key => $value) {
            $page_data['aca_share_brokers'][$value["type"]] = $value;
        }
        $page_data['aca_share_brokers_blogs'] = $this->Common_Model->get_data_order_by('blogs', array("blogs_type"=>"representative_section_2"));
        $page_data["representative_all_reviews_and_reating"] = $this->User_model->get2RepresentativeReviewsAndReating($this->loginUserDetails["representative_id"]);
        $page_data["representative_all_reating_data'"] = array();
        foreach ($page_data["representative_all_reviews_and_reating"]["representative_all_reating"] as $key => $value) {
            $page_data['representative_all_reating_data'][$value["rating"]] = $value;
        }
        $header_data['page_description'] = "My Representative - Bloom";
        $header_data['page_title'] = 'My Representative';
        $page_data["my_representative"] = $this->Common_Model->getSelectedData('blm_representative', array('id' => $this->loginUserDetails["representative_id"]));
        $this->client_views_load('my_representative_page', $page_data, $header_data, null);
    }

    public function get_my_representative_rating_and_review() {
        $page_data["representative_all_reviews_and_reating"] = $this->User_model->getRepresentativeReviewsAndReatingByLimit($this->loginUserDetails["representative_id"],6);
        $header_data['page_description'] = "My Representative - Bloom";
        $header_data['page_title'] = 'My Representative';
        $page_data["my_representative"] = $this->Common_Model->getSelectedData('blm_representative', array('id' => $this->loginUserDetails["representative_id"]));
        $this->client_views_load('my_representative_rating_and_review', $page_data, $header_data, null);
    }

    public function my_profile(){
        $page_data = array();
        $userid =  $this->loginUserDetails["userid"];
        if(!empty($this->input->post("submit_type")) && $this->input->post("submit_type")=="upload_client_profile"){
            if($_FILES['profile_img']['name'] !=''){
                $config['upload_path'] = FCPATH.'assets/img/users/';
                $config['file_name'] = time() . 'image3' . '-' . md5($_FILES['profile_img']['name']) . '.jpg';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('profile_img'))
                {
                    if($this->loginUserDetails["profile_image"]!='defaultclient.png')
                    {
                        unlink(FCPATH.'assets/img/users/' . $this->loginUserDetails["profile_image"]);
                        $this->session->set_flashdata('success','image updated success');
                    }
                    $data1 = $this->upload->data();
                    $data['profile_img']  = $data1['file_name'];
                    $this->db->where('userid',$userid)->update('blm_users',array('profile_image'=>$data['profile_img']));
                    redirect(base_url('my-profile'));
                }
                else
                {
                     $error = array('error' => $this->upload->display_errors());
                    $this->session->set_flashdata('fail','Error occurred in update image');
                }
            } 
        }
        $api_responce_data = $this->Common_Model->trademobile_service_api_data("jSONGetUserProfileX","POST","struserid=".$userid);
        if($api_responce_data == false){
            echo '
            Redirecting please wait...
            <script>
            document.ready(window.setTimeout(location.href = "'.base_url("my-profile").'",5000));
            </script>';
        }
        $page_data['profiledata'] = json_decode(json_decode(json_encode(simplexml_load_string($api_responce_data)),TRUE)[0],TRUE)['Data'][0];
        $header_data['page_description'] = "My Profile - Bloom";
        $header_data['page_title'] = 'My profile - Bloom';
        $this->client_views_load('sample', $page_data, $header_data, null);
    }

    public function my_reports(){
        /*ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);*/
        $page_data = array(); 
        $header_data['page_description'] = "My Reports - Bloom";
        $header_data['page_title'] = 'My Reports - Bloom';
        $this->client_views_load('my_reports', $page_data, $header_data, null);
    }

    public function my_reports_download(){
        $page_data = array();
        $userid =  $this->loginUserDetails["userid"];
        if(!empty($_GET["submit_type"]) && $_GET["submit_type"] == "finance_ledger"){
            //for FinancialLedger;
            $page_data['page_description'] = "Finance Ledger Reports - Bloom";
            $page_data['page_title'] = 'Finance Ledger Reports - Bloom';
            $page_data['teport_type'] = 'Finance Ledger';
            if($_GET['ledger_range']=="custom_date"){
                $_GET["from"] = date('Ymd', strtotime($_GET["from_ledger"]));
                $_GET["to"] = date('Ymd', strtotime($_GET["to_ledger"]));
            }elseif ($_GET['ledger_range']=="last_1_month") {
                $_GET["from"] = date("Ymd",strtotime('-1 month',strtotime(date("Y-m-d"))));
                $_GET["to"] = date("Ymd",strtotime('-1 day',strtotime(date('Y-m-d'))));
                // echo"<pre>";var_dump($last_month_date);die("qwe");
            }elseif ($_GET['ledger_range']=="last_3_months") {
                $_GET["from"] = date("Ymd",strtotime('-3 month',strtotime(date("Y-m-d"))));
                $_GET["to"] = date("Ymd",strtotime('-1 day',strtotime(date('Y-m-d'))));
            }elseif ($_GET['ledger_range']=="last_6_months") {
                $_GET["from"] = date("Ymd",strtotime('-6 month',strtotime(date("Y-m-d"))));
                $_GET["to"] = date("Ymd",strtotime('-1 day',strtotime(date('Y-m-d'))));
            }elseif ($_GET['ledger_range']=="current_financial_year") {
                $date=date_create(date("Y-m-d"));
                if (date_format($date,"m") >= 4) {
                    $_GET["from"] = (date_format($date,"Y"))."0401";
                    $_GET["to"] = (date_format($date,"Y")+1)."0331";
                } else {
                    $_GET["from"] = (date_format($date,"Y")-1)."0401";
                    $_GET["to"] = date_format($date,"Y")."0331";
                }
            }elseif ($_GET['ledger_range']=="last_financial_year") {
                $date=date_create(date("Y-m-d"));
                if (date_format($date,"m") >= 4) {
                    $_GET["from"] = (date_format($date,"Y")-1)."0401";
                    $_GET["to"] = (date_format($date,"Y"))."0331";
                } else {
                    $_GET["from"] = (date_format($date,"Y")-2)."0401";
                    $_GET["to"] = (date_format($date,"Y")-1)."0331";
                }
            }
            $postStr = "struserid=".$userid."&strDPID=&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"];
            // die($postStr);
            $page_data["api_ledger_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetLedgerDetails","POST",$postStr);
            $page_data["api_ledger_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_ledger_repost"])),true)[0],true);
            // echo "<pre>";var_dump($page_data["api_ledger_repost"]);die("for FinancialLedger;");
        
            $page_data["from_ledger"] = date("d-m-Y",strtotime($_GET["from"]));
            $page_data["to_ledger"] = date("d-m-Y",strtotime($_GET["to"]));
        }else if (!empty($_GET["submit_type"]) && $_GET["submit_type"] == "outstanding") {
            //for OutStanding;
            $page_data['page_description'] = "Outstanding Reports - Bloom";
            $page_data['page_title'] = 'Outstanding Reports - Bloom';
            $page_data['teport_type'] = 'Outstanding';
            $postStr = "struserid=".$userid."&strFutOpt=&strExchSeg=";
            $page_data["api_outstanding_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetOutstandingDetail","POST",$postStr);
            $page_data["api_outstanding_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_outstanding_repost"])),true)[0],true);
        }else if (!empty($_GET["submit_type"]) && $_GET["submit_type"] == "dp_holding") {
            //for DP Holding;
            $page_data['page_description'] = "DP Holding Reports - Bloom";
            $page_data['page_title'] = 'DP Holding Reports - Bloom';
            $page_data['teport_type'] = 'DP Holding';
            $postStr = "struserid=".$userid."&strDematActNo=".$_GET["strDematActNo"];
            $page_data["api_dp_holding_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetDPHolding","POST",$postStr);
            $page_data["api_dp_holding_repost"] = json_decode(json_encode(simplexml_load_string($page_data["api_dp_holding_repost"])),true);
            if(count($page_data["api_dp_holding_repost"])>0){
                $page_data["api_dp_holding_repost"][0] = json_decode($page_data["api_dp_holding_repost"][0],true);
            }
        }else if (!empty($_GET["submit_type"]) && $_GET["submit_type"] == "transaction") {
            //for Transaction
            $page_data['page_description'] = "Transaction Reports - Bloom";
            $page_data['page_title'] = 'Transaction Reports - Bloom';
            $page_data['teport_type'] = 'Transaction';
            $_GET["date"] = date('Ymd', strtotime($_GET["transaction_date"]));
            $postStr = "struserid=".$userid."&strDate=".$_GET["date"]."&strSegment=".$_GET['transaction_segment'];
            $page_data["api_trades_for_date_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetTradesForDate","POST",$postStr);
            $page_data["api_trades_for_date_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_trades_for_date_repost"])),true)[0],true);
            if(!empty($page_data["api_trades_for_date_repost"]["Data"][0]["ErrorMG"])){
                $page_data["api_trades_for_date_repost"] = 0;
            }

        }else if (!empty($_GET["submit_type"]) && $_GET["submit_type"] == "pnl") {
            //for P&L
            ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
            $page_data['page_description'] = "PnL Reports - Bloom";
            $page_data['page_title'] = 'PnL Reports - Bloom';
            $page_data['teport_type'] = 'PnL';
            /*$_GET["from"] = date('Ymd', strtotime($_GET["pnl_from_date"]));
            $_GET["to"] = date('Ymd', strtotime($_GET["pnl_to_date"]));
            $postStr = "strUserid=".$userid."&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strScripCode=&strReportType=".$_GET["pnl_report_type"]."&strStockValuation=1";
            $page_data["api_investorPL_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLCash","POST",$postStr);
            $page_data["api_investorPL_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investorPL_cash_repost"])),true)[0],true);*/

            if($_GET['pnl_range']=="custom_date"){
                $_GET["from"] = date('Ymd', strtotime($_GET["pnl_from_date"]));
                $_GET["to"] = date('Ymd', strtotime($_GET["pnl_to_date"]));
            }elseif ($_GET['pnl_range']=="last_1_month") {
                $_GET["from"] = date("Ymd",strtotime('-1 month',strtotime(date("Y-m-d"))));
                $_GET["to"] = date("Ymd",strtotime('-1 day',strtotime(date('Y-m-d'))));
                // echo"<pre>";var_dump($last_month_date);die("qwe");
            }elseif ($_GET['pnl_range']=="last_3_months") {
                $_GET["from"] = date("Ymd",strtotime('-3 month',strtotime(date("Y-m-d"))));
                $_GET["to"] = date("Ymd",strtotime('-1 day',strtotime(date('Y-m-d'))));
            }elseif ($_GET['pnl_range']=="last_6_months") {
                $_GET["from"] = date("Ymd",strtotime('-6 month',strtotime(date("Y-m-d"))));
                $_GET["to"] = date("Ymd",strtotime('-1 day',strtotime(date('Y-m-d'))));
            }elseif ($_GET['pnl_range']=="current_financial_year") {
                $date=date_create(date("Y-m-d"));
                if (date_format($date,"m") >= 4) {
                    $_GET["from"] = (date_format($date,"Y"))."0401";
                    $_GET["to"] = (date_format($date,"Y")+1)."0331";
                } else {
                    $_GET["from"] = (date_format($date,"Y")-1)."0401";
                    $_GET["to"] = date_format($date,"Y")."0331";
                }
            }elseif ($_GET['pnl_range']=="last_financial_year") {
                $date=date_create(date("Y-m-d"));
                if (date_format($date,"m") >= 4) {
                    $_GET["from"] = (date_format($date,"Y")-1)."0401";
                    $_GET["to"] = (date_format($date,"Y"))."0331";
                } else {
                    $_GET["from"] = (date_format($date,"Y")-2)."0401";
                    $_GET["to"] = (date_format($date,"Y")-1)."0331";
                }
            }
            /*$postStr = "strUserid=".$userid."&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strScripCode=&strReportType=".$_GET["pnl_report_type"]."&strStockValuation=1";
            $page_data["api_investorPL_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLCash","POST",$postStr);
            $page_data["api_investorPL_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investorPL_cash_repost"])),true)[0],true);*/
            if($_GET["pnl_report_type"] == 'S'){
                $postStr = "strUserid=".$this->loginUserDetails["userid"]."&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strScripCode=&strReportType=S&strStockValuation=1";
                $page_data["api_investorPL_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLCash","POST",$postStr);
                $page_data["api_investorPL_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investorPL_cash_repost"])),true)[0],true);

                $postStr = "strUserid=".$userid."&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strScripCode=&strReportType=C&strStockValuation=1";
                $page_data["api_investor_charges_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLCash","POST",$postStr);
                $page_data["api_investor_charges_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investor_charges_cash_repost"])),true)[0],true);
            }else if($_GET["pnl_report_type"] == 'NF'){
                
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);
                $postStr = "strUserid=".$this->loginUserDetails["userid"]."&strExchSeg=NF&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strSeriesID=&strReportType=S&strBFOptionsInclude=Y&strBFOptionsPL=0";
                $page_data["api_investorPL_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLFO","POST",$postStr);
                $page_data["api_investorPL_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investorPL_cash_repost"])),true)[0],true);
                $postStr = "strUserid=".$this->loginUserDetails["userid"]."&strExchSeg=NF&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strSeriesID=&strReportType=C&strBFOptionsInclude=Y&strBFOptionsPL=0";
                $page_data["api_investor_charges_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLFO","POST",$postStr);
                $page_data["api_investor_charges_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investor_charges_cash_repost"])),true)[0],true);

            }else if($_GET["pnl_report_type"] == 'NK'){
                $postStr = "strUserid=".$this->loginUserDetails["userid"]."&strExchSeg=NK&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strSeriesID=&strReportType=S&strBFOptionsInclude=Y&strBFOptionsPL=0";
                $page_data["api_investorPL_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLFO","POST",$postStr);
                $page_data["api_investorPL_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investorPL_cash_repost"])),true)[0],true);
                // pre($page_data["api_investorPL_cash_repost"]);
                $postStr = "strUserid=".$this->loginUserDetails["userid"]."&strExchSeg=NK&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strSeriesID=&strReportType=C&strBFOptionsInclude=Y&strBFOptionsPL=0";
                $page_data["api_investor_charges_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLFO","POST",$postStr);
                $page_data["api_investor_charges_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investor_charges_cash_repost"])),true)[0],true);
            }else if($_GET["pnl_report_type"] == 'C'){
                $postStr = "strUserid=".$this->loginUserDetails["userid"]."&strExch=M&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strSeriesID=&strReportType=S&strBFOptionsInclude=&strBFOptionsPL=0";
                $page_data["api_investorPL_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLComm","POST",$postStr);
                $page_data["api_investorPL_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investorPL_cash_repost"])),true)[0],true);
                $postStr = "strUserid=".$this->loginUserDetails["userid"]."&strExch=M&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"]."&strSeriesID=&strReportType=C&strBFOptionsInclude=&strBFOptionsPL=0";
                $page_data["api_investor_charges_cash_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetInvestorPLComm","POST",$postStr);
                $page_data["api_investor_charges_cash_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_investor_charges_cash_repost"])),true)[0],true);
            }
        }
        $this->load->view('pdfviiew/my_reports_download', $page_data);
        $html = $this->output->get_output();
        $this->load->library('pdf');
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4','landscape');
        $this->dompdf->render();
        $this->dompdf->stream(date('d-m-Y')."_".$_GET["submit_type"]."_".time()."_report.pdf");
    }

    public function my_assessment() {
        $user_id = $this->loginUserDetails["id"];
        $page_data['assessment'] = $this->Common_Model->getSelectedData(ASSESSMENT_RESULT, array('user_id' => $user_id));
        if (!empty($page_data['assessment'])) {
            $page_data['full_assessment'] = $this->Common_Model->getSelectedData(QUES, array('user_id' => $user_id));
            $page_data['user_info'] = $this->Common_Model->getSelectedData('blm_users', array('id' => $user_id));
            $page_data['pnc'] = $this->Common_Model->getSelectedData('final_pnc', array('value' => $page_data['assessment']['value']));
            $header_data['page_title'] = 'My assessment - Bloom';
            $header_data['page_description'] = "My assessment - Bloom";
            $this->client_views_load('my_assessment', $page_data, $header_data, null);
        } else {
            $this->session->set_flashdata('fail','Please fill your risk profiling questionnaire in order for us to provide assessment of your profile');
            redirect(base_url('my-questionnaire'));
        }
    }

    public function MyAssessmentPdf() {
        $user_id = $this->loginUserDetails["id"];
        $result['assessment'] = $this->Common_Model->getSelectedData(ASSESSMENT_RESULT, array('user_id' => $user_id));
        if (!empty($result['assessment'])) {
            $result['full_assessment'] = $this->Common_Model->getSelectedData(QUES, array('user_id' => $user_id));
            $result['pnc'] = $this->Common_Model->getSelectedData('final_pnc', array('value' => $result['assessment']['value']));
            $this->load->library('pdf');
            $html = $this->load->view('pdfviiew/assessment_pdf.php', $result);

            $html = $this->output->get_output();
            $this->load->library('pdf');
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4');
            $this->dompdf->render();
            $this->dompdf->stream(date('d-m-Y')."_my_assessment_".time()."_report.pdf");
            // $this->dompdf->stream("html_contents.pdf", array("Attachment"=> 0));
            // $this->dompdf->stream("html_contents.pdf");
        } else {
            $this->session->set_flashdata('fail', '“Please fill your risk profiling questionnaire in order for us to provide assessment of your 
        profile”');
            redirect(base_url('my-questionarrie'));
        }
    }

    public function QusPdf() {
        $userid = $this->loginUserDetails["id"];
        $result['qusdata'] = $this->Common_Model->getSelectedData('questionnaire', array('user_id' => $userid));
        if (!empty($result['qusdata'])) {
            $this->load->library('pdf');
            $html = $this->load->view('pdfviiew/quess_pdf.php', $result);

            $html = $this->output->get_output();
            $this->load->library('pdf');
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4');
            $this->dompdf->render();
            $this->dompdf->stream(date('d-m-Y')."_my_questionnaire_".time()."_report.pdf");
        } else {
            $this->session->set_flashdata('fail', '“Please fill your risk profiling questionnaire in order for us to provide assessment of your 
        profile”');
            redirect(base_url('my-questionarrie'));
        }
    }

    public function trading_journal() {
        $user_id = $this->loginUserDetails["id"];
        $page_data['trading_journal'] = $this->Common_Model->getSelectedData("blm_trading_journal", array('user_id' => $user_id));
        if(!empty($this->input->post("journal"))){
            $inset_data["journal"] = trim($this->input->post("journal"));
            $inset_data["user_id"] = $user_id;
            if(count($page_data['trading_journal'])){
                $this->Common_Model->update_info("blm_trading_journal",$inset_data,array("user_id"=>$user_id));
            }else{
                $this->Common_Model->insert_info("blm_trading_journal",$inset_data);
            }
            $this->session->set_flashdata('success','Trading journal submitted success');
            redirect(base_url('trading-journal'));
        }
        $header_data['page_title'] = 'Trading Journal - Bloom';
        $header_data['page_description'] = "Trading Journal - Bloom";
        $this->client_views_load('trading_journal', $page_data, $header_data, null);
    }

    public function feedback() {
        $user_id = $this->loginUserDetails["id"];
        if($this->input->post("submit") != ""){
            $this->form_validation->set_rules('comments', "Description", 'required');
            if ($this->form_validation->run()){
                $insert_data["comments"] = $this->input->post("comments");
                $insert_data["feedback_type"] = $this->input->post("submit");
                $insert_data["feedback_category"] = $this->input->post("feedback_category");
                $insert_data["user_id"] = $user_id;
                $this->Common_Model->insert_info("blm_feedback",$insert_data);
                $this->Common_Model->send_feedback_emails($insert_data);
                $this->session->set_flashdata('success','Feedback submitted success');
                redirect(base_url('feedback'));
            }{
                $this->session->set_flashdata('fail','Please fill all details');
            }
        }
        $page_data['feedback_data']["suggestion"] = $this->Common_Model->get_multi_selected_data("blm_feedback_category", array('category_for' => "Suggestion"));
        $page_data['feedback_data']["complaint"] = $this->Common_Model->get_multi_selected_data("blm_feedback_category", array('category_for' => "Complaint"));
        $page_data['feedback_data']["list"] = $this->Common_Model->get_data_order_by("blm_feedback", array('user_id' => $user_id),"id desc");
        $header_data['page_title'] = 'Feedback - Bloom';
        $header_data['page_description'] = "Feedback - Bloom";
        $this->client_views_load('feedback', $page_data, $header_data, null);
    }

    public function discipline_trading() {
        if($this->input->get("did") != ""){
            $this->Common_Model->update_info('blm_discipline_trading_Strategies', array("status" => '1',"updated_at" => date("Y-m-d h:i:s")),array("id" => $this->input->get("did")));
            $this->session->set_flashdata('success','Strategy deleted success');
            redirect(base_url('discipline-trading'));
        }
        if($this->input->post("submit") != ""){
            $this->form_validation->set_rules('strategy_name', 'Strategy name', 'required');
            if( $this->input->post("position_type") == "" && $this->input->post("instrument") == "" && $this->input->post("target_day_wise") == "" && $this->input->post("target_trade_wise") == "" && $this->input->post("stop_loss_day_wise") == "" && $this->input->post("stop_loss_trade_wise") == "" && $this->input->post("entry_time") == "" && $this->input->post("exit_time") == ""){
                $this->form_validation->set_rules('onefiled', 'One Filed', 'required',
                    array(
                        'required' => 'Please set at least one field'
                    )
                );
            }
            $this->form_validation->set_rules('target_day_wise', "Target day wise", 'numeric');
            $this->form_validation->set_rules('target_trade_wise', "Target trade wise", 'numeric');
            $this->form_validation->set_rules('stop_loss_day_wise', "Stop loss day wise", 'numeric');
            $this->form_validation->set_rules('stop_loss_trade_wise', "Stop loss trade wise", 'numeric');

            if ($this->form_validation->run()){
                $insert_data = array(
                    'strategy_name' => $this->input->post('strategy_name'),
                    'position_type' => empty($this->input->post('position_type')) ? "" : $this->input->post('position_type'),
                    'instrument' => empty($this->input->post('instrument')) ? "" : implode("____",$this->input->post('instrument')),
                    'target_day_wise' => $this->input->post('target_day_wise'),
                    'target_trade_wise' => $this->input->post('target_trade_wise'),
                    'stop_loss_day_wise' => $this->input->post('stop_loss_day_wise'),
                    'stop_loss_trade_wise' => $this->input->post('stop_loss_trade_wise'),
                    'entry_time' => $this->input->post('entry_time'),
                    'exit_time' => $this->input->post('exit_time'),
                    'trading_days' => empty($this->input->post('trading_days'))? "" : implode("____",$this->input->post('trading_days')),
                );

                if($insert_data["position_type"] == "Positional"){
                    $insert_data["exit_time"] = "";
                }

                if($this->input->post("submit") == "submit"){
                    $insert_data["status"] = '0' ;
                    $insert_data["user_id"] = $this->loginUserDetails["id"];
                    $insert_data["client_id"] = $this->loginUserDetails["userid"];
                    $insert_data["graph_dataPoints_json"] = '[]';
                    $this->Common_Model->insert_info("blm_discipline_trading_Strategies",$insert_data);
                }else{
                    $this->Common_Model->update_info('blm_discipline_trading_Strategies', $insert_data , array("id"=>$this->input->post("submit")));
                }
                $this->session->set_flashdata('success','Strategy submitted success');
                redirect(base_url('discipline-trading?#qw'));
            }else{
                $this->session->set_flashdata('fail','Please fill your risk profiling questionnaire in order for us to provide assessment of your profile');
            }
        }
        if($this->input->get("eid") != "" || $this->input->get("vid") != ""){
            $id = $this->input->get("eid") != "" ? $this->input->get("eid") : $this->input->get("vid");
            $page_data["discipline_trading_Strategy_details"] = $this->Common_Model->getSelectedData('blm_discipline_trading_Strategies', array('status' => '0','user_id' => $this->loginUserDetails["id"],'id'=>$id));
            if($this->input->post("submit") == ""){
                foreach ($page_data["discipline_trading_Strategy_details"] as $key => $value) {
                    if($key=="instrument" ||$key=="target" || $key=="stop_loss" || $key=="trading_days"){
                        $_POST[$key] = explode("____",$value);
                    }else{
                        $_POST[$key] =  $value ;
                    }
                }
            }
        }

        $page_data["discipline_trading_Strategies"] = $this->Common_Model->getSelectedData('blm_discipline_trading_Strategies', array('status' => '0','user_id' => $this->loginUserDetails["id"]),"id, strategy_name,graph_dataPoints_json","all");
        $header_data['page_title'] = 'Discipline Trading - Bloom';
        $header_data['page_description'] = "Discipline Trading - Bloom";
        $graph_data = array();
        $page_data["count_for_graph_show"] = 0;
        foreach ($page_data["discipline_trading_Strategies"] as $key => $value) {
            $graph_data["graph_dataPoints_Name"][$key]["id"] = $value["id"];
            $graph_data["graph_dataPoints_Name"][$key]["name"] = $value["strategy_name"];
            $graph_data["graph_dataPoints_Name"][$key]["graph_dataPoints_json"] = $value["graph_dataPoints_json"];
            if($value["graph_dataPoints_json"]!="[]"){
                $page_data["count_for_graph_show"]++;
            }
        }
        $graph_data["type"] = "spline";
        $this->client_views_load('discipline_trading', $page_data, $header_data, null);
        // $this->load->view('site/graph_screapt', $graph_data);
    }

    public function logs_on_discipline_trading() {
        $page_data["discipline_trading_Strategies_logs"] = $this->Common_Model->getSelectedData('blm_discipline_trading_Strategies_logs', array('user_id' => $this->loginUserDetails["id"],'trading_Strategy_id' => $this->input->get("vid")),"","single","id desc");
        // pre($page_data["discipline_trading_Strategies_logs"]);
        if(count($page_data["discipline_trading_Strategies_logs"])!=0){
            // pre(count($page_data["discipline_trading_Strategies_logs"])!=0);
            $page_data["discipline_trading_Strategies_logs"]["logs_str"] = json_decode($page_data["discipline_trading_Strategies_logs"]["logs_str"],true);
        }
        // pre($page_data["discipline_trading_Strategies_logs"]);
        $page_data["instrument_types"] = array('Index','Stocks','Currency','Commodity');
        $page_data["discipline_trading_strategies_report"] = $this->Common_Model->logs_on_discipline_trading_report();
        $header_data['page_title'] = 'Discipline Trading Logs - Bloom';
        $header_data['page_description'] = "Discipline Trading Logs - Bloom";
        $this->client_views_load('logs_on_discipline_trading', $page_data, $header_data, null);
    }

    public function logs_on_discipline_trading_pdf() {
        $page_data["discipline_trading_Strategies_logs"] = $this->Common_Model->getSelectedData('blm_discipline_trading_Strategies_logs', array('user_id' => $this->loginUserDetails["id"],'trading_Strategy_id' => $this->input->get("vid")),"","single","id desc");
        if(count($page_data["discipline_trading_Strategies_logs"])!=0){
            $page_data["discipline_trading_Strategies_logs"]["logs_str"] = json_decode($page_data["discipline_trading_Strategies_logs"]["logs_str"],true);
        }
        $page_data["instrument_types"] = array('Index','Stocks','Currency','Commodity');
        $page_data["discipline_trading_strategies_report"] = $this->Common_Model->logs_on_discipline_trading_report();
        $header_data['page_title'] = 'Discipline Trading Logs - Bloom';
        $header_data['page_description'] = "Discipline Trading Logs - Bloom";
        $this->load->view('pdfviiew/logs_on_discipline_trading_pdf', $page_data);
        $html = $this->output->get_output();
        // echo $html;die();
        $this->load->library('pdf');
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4','portrait');
        $this->dompdf->render();
        $this->dompdf->stream(date('d-m-Y')."_".time()."_report.pdf",array('Attachment' => 1));
    }

    public function goal_tracker() {
        if($this->input->post("submit")!=""){
            $this->form_validation->set_rules('name', "Name", 'required');
            $this->form_validation->set_rules('duration', "Duration", 'required');
            $this->form_validation->set_rules('amount', "Amount", 'required|numeric');
            $this->form_validation->set_rules('description', "Description", 'required');

            if ($this->form_validation->run()){
                $data = $this->input->post();
                unset($data["submit"]);
                if ($this->input->post("submit")!="submit_goal_tracker"){
                    $where_array = array("id"=>$this->input->post("submit"),"user_id"=>$this->loginUserDetails["id"]);
                    $this->Common_Model->update_info('blm_user_financial_goal',$data,$where_array);
                }else{
                    $data["user_id"] = $this->loginUserDetails["id"];
                    $this->Common_Model->insert_info('blm_user_financial_goal',$data);
                }
                    $this->session->set_flashdata('success','Goal tracker submitted success');
                    redirect(base_url('goal-tracker'));
            }else{
                $this->session->set_flashdata('fail','Please fill all field');
            }
        }
        if($this->input->get("dlt")){
            $where_array = array("id"=>$this->input->get("dlt"),"user_id"=>$this->loginUserDetails["id"]);
            $data = array("status"=>0);
            $this->Common_Model->update_info('blm_user_financial_goal',$data,$where_array);
            $this->session->set_flashdata('success','Goal tracker deleted successfully');
            redirect(base_url('goal-tracker'));
        }
        if($this->input->get("complete")){
            $where_array = array("id"=>$this->input->get("complete"),"user_id"=>$this->loginUserDetails["id"]);
            $data = array("status"=>2);
            $this->Common_Model->update_info('blm_user_financial_goal',$data,$where_array);
            $this->session->set_flashdata('success','Goal tracker status complete successfully');
            redirect(base_url('goal-tracker'));
        }
        if($this->input->get("eid") || $this->input->get("vid")){
            $id = empty($this->input->get("eid")) ? $this->input->get("vid") : $this->input->get("eid");
            $page_data['financial_goal_data'] = $this->Common_Model->getSelectedData('blm_user_financial_goal', array('id' => $id));
            $_POST = $page_data['financial_goal_data'];
            $_POST['submit'] = $page_data['financial_goal_data']['id'];
        }
        $page_data = array();
        $page_data["goal_tracker"] = $this->Common_Model->getSelectedData('blm_user_financial_goal', array('status!=' => '0','user_id' => $this->loginUserDetails["id"]),"*","all");
        $header_data['page_title'] = 'Goal Tracker - Bloom';
        $header_data['page_description'] = "Goal Tracker - Bloom";
        $this->client_views_load('goal_tracker', $page_data, $header_data, null);
    }

    public function notification_center() {
        if($this->input->get("vid")){
            $where_array = array(
                "id"=>$this->input->get("vid"),
                "receiver"=>$this->loginUserDetails["id"],
            );
            $this->Common_Model->update_info('blm_notification',array("read_it"=>1),$where_array);
            echo "view";
            return true;
        }else if($this->input->get("bid")){
            $where_array = array(
                "id"=>$this->input->get("bid"),
                "receiver"=>$this->loginUserDetails["id"],
            );
            $this->Common_Model->update_info('blm_notification',array("bookmark"=>($this->input->get("bookmark") == 1 ? "0" : "1")),$where_array);
            echo "bookmarked";
            return true;
        }else if($this->input->get("dlt")){
            $where_array = array(
                "id"=>$this->input->get("dlt"),
                "receiver"=>$this->loginUserDetails["id"],
            );
            $this->db->delete('blm_notification',$where_array);
            redirect("notification-center");
        }
        $page_data = array();
        $page_data["notification_data"] = $this->Common_Model->get_data_order_by('blm_notification', array('status' => '1','receiver' => $this->loginUserDetails["id"]),"id desc");
        // pre($this->db->last_query());
        foreach ($page_data["notification_data"] as $key => $value) {
            $page_data['notification_main'][$value["notification_type"]][] = $value;
        }
        $header_data['page_title'] = 'Notification center - Bloom';
        $header_data['page_description'] = "Notification center - Bloom";
        $this->client_views_load('notification_center', $page_data, $header_data, null);
    }

    public function funds_transfer() {
        $userid =  $this->loginUserDetails["userid"];
        $id =  $this->loginUserDetails["id"];
        $page_data = array();
        if (date('m') > 6) {
            $fstart_year = date('Y');
            $fend_year = (date('Y') +1);
        } else {
            $fstart_year = (date('Y')-1);
            $fend_year = date('Y');
        }
        $fstart_date = "1 April ".$fstart_year; // 2015-2016
        $fend_date =  "31 March ".$fend_year; // 2015-2016
        $_GET["from"] = date('Ymd', strtotime($fstart_date));
        $_GET["to"] = date('Ymd', strtotime($fend_date));
        $postStr = "struserid=".$userid."&strDPID=&strFromDt=".$_GET["from"]."&strToDt=".$_GET["to"];
        $page_data["api_ledger_repost"] = $this->Common_Model->trademobile_service_api_data("jSONGetLedgerDetails","POST",$postStr);
        $page_data["api_ledger_repost"] = json_decode(json_decode(json_encode(simplexml_load_string($page_data["api_ledger_repost"])),true)[0],true);
            // pre($page_data["api_ledger_repost"]);
        $page_data["api_ledger_repost"] = $page_data["api_ledger_repost"] == 0 ? array("Balance" => "0" ): $page_data["api_ledger_repost"]["Data"][count($page_data["api_ledger_repost"]["Data"]) -1 ];

        if($this->input->post('submit') == "request_payout"){
            $ledger_array = explode("  ",$page_data["api_ledger_repost"]["Balance"]);
            if($ledger_array[1] == "Cr"){
                if($this->input->post('payout_amount') <= $ledger_array[0]){
                    $this->Common_Model->send_request_payout_emails($this->input->post());
                    $this->session->set_flashdata('success','Payout request submitted success');
                    redirect(base_url('funds-transfer?action=request_payout'));
                }else{
                    $this->session->set_flashdata('fail','The requested amount should be less than or equal to your ledger balance');
                    redirect(base_url('funds-transfer?action=request_payout'));
                }
            }
        }
        $page_data["blm_payout_request_list"] = $this->Common_Model->getSelectedData('blm_payout_request', array("user_id"=>$id),"request_amount, status, created_at","all","id desc");
        $page_data["bank_accounts"] = $this->Common_Model->getSelectedData('blm_bank_accounts', array('status' => '1'),"name, bank_name, ifsc, ac_number, stock_type, accounts_type","all","id desc");
        $page_data["upi_details"] = $this->Common_Model->getSelectedData('blm_cms_pages', array("id"=>"11"));
        $page_data["funds_transfer_cheque_list"] = $this->Common_Model->getSelectedData('blm_cms_pages', array("page_name"=>"funds_transfer_cheque"),"title, description","all");

        $header_data['page_title'] = 'Notification center - Bloom';
        $header_data['page_description'] = "Notification center - Bloom";
        $this->client_views_load('funds_transfer', $page_data, $header_data, null);
    }
    
    public function logout(){
        $this->session->set_flashdata('error', 'User logout');
        $this->User_model->user_logout();
    }
}