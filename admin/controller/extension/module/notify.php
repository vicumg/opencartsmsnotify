<?php
class ControllerExtensionModuleNotify extends Controller {


	public function index() {

	    $data=[];
        $data['customers_templates'] = $this->config->get('sms_notify_customers_templates');
	    $response =$this->load->view('extension/module/notify', $data);


	    return $response;

    }
}