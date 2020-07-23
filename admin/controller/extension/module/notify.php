<?php
class ControllerExtensionModuleNotify extends Controller {


	public function index() {

	    $data=[];
        $options = array(

            'from' => $this->config->get('sms_notify_from'),
            'username' => $this->config->get('sms_notify_gate_username'),
            'password' => $this->config->get('sms_notify_gate_password'),
        );

        $sms = new Sms($this->config->get('sms_notify_gatename'), $options);


	    $data['balance'] =$sms->getBalance();
        $data['customers_templates'] = $this->config->get('sms_notify_customers_templates');
	    $response =$this->load->view('extension/module/notify', $data);


	    return $response;

    }
}