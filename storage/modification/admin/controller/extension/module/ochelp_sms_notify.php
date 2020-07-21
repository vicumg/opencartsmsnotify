<?php
class ControllerExtensionModuleOchelpSmsNotify extends Controller {
	private $error = array();

	public function index() {
	    
		$this->load->language('extension/module/ochelp_sms_notify');

		$this->document->setTitle($this->language->get('heading_main_title'));

		$this->load->model('extension/module/ochelp_sms_notify');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('sms_notify', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');


				if (isset($this->request->get['route'])) {
					$get = explode("/", $this->request->get['route']);
					if ($get[0] == 'extension') {
						$ext = $get[0];
						$folder = $get[1];
						$file = $get[2];
					} else {
						$folder = $get[0];
						$file = $get[1];
					}
					if ($file == 'user_permission') {
						$table = 'user_group';
					} else {
						if($folder == 'module'){
							$table = 'module';
						}
						elseif($folder == 'newsblog') {
							$table = $folder.'_'.$file;
						} else {
							$table = $file;
						}
					}
					$this->load->model('setting/setting');
					if ($file == 'user_permission' || $folder == 'module') {
						$id = $this->model_setting_setting->getLastId($table, $table);
					} else {
						$id = $this->model_setting_setting->getLastId($table, $file);
					}

					if ($folder != 'module') {
						if ($file == 'setting') {
							$route = 'setting/store';
							$editroute = 'setting/setting';
						} else {
							$route = $folder.'/'.$file;
							$editroute = $folder.'/'.$file.'/edit';
						}
					} else {
						if (isset($ext)) {
							$route = 'extension/'.$folder.'/'.$file;
							$editroute = 'extension/'.$folder.'/'.$file;
						} else {
							$route = $folder.'/'.$file;
							$editroute = $folder.'/'.$file;
						}
					}

					if (!isset($url)) $url = '';

					if(($folder != 'module') && ($folder != 'newsblog')) {
						if (($file != 'setting') && (isset($this->request->get[$table.'_id']) || isset($id))) {
							$url .= '&'.$table.'_id='.(isset($this->request->get[$table.'_id']) ? $this->request->get[$table.'_id'] : $id);
						}
					}
					elseif($folder == 'newsblog') {
						$url .= '&'.$file.'_id='.(isset($this->request->get[$file.'_id']) ? $this->request->get[$file.'_id'] : $id);
					} else {
						$url .= '&module_id='.(isset($this->request->get['module_id']) ? $this->request->get['module_id'] : $id);
					}
					if (isset($this->request->post['apply']) && $this->request->post['apply'] == '1') {
						$this->response->redirect($this->url->link($editroute, 'user_token=' . $this->session->data['user_token'] . $url, true));
					} else {
						if($folder != 'module') {
							$this->response->redirect($this->url->link($route, 'user_token=' . $this->session->data['user_token'] . $url, true));
						}
					}
				}
			
			$this->response->redirect($this->url->link('extension/module/ochelp_sms_notify', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['action'] = $this->url->link('extension/module/ochelp_sms_notify', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/ochelp_sms_notify', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['sms_gatenames'] = array();

		$files = glob(DIR_SYSTEM . 'library/smsgate/*.php');

		foreach ($files as $file) {
			$data['sms_gatenames'][] = basename($file, '.php');
		}

		if (isset($this->request->post['sms_notify_gatename'])) {
			$data['sms_notify_gatename'] = $this->request->post['sms_notify_gatename'];
		} else {
			$data['sms_notify_gatename'] = $this->config->get('sms_notify_gatename');
		}

		if (isset($this->request->post['sms_notify_to'])) {
			$data['sms_notify_to'] = $this->request->post['sms_notify_to'];
		} else {
			$data['sms_notify_to'] = $this->config->get('sms_notify_to');
		}

		if (isset($this->request->post['sms_notify_from'])) {
			$data['sms_notify_from'] = $this->request->post['sms_notify_from'];
		} else {
			$data['sms_notify_from'] = $this->config->get('sms_notify_from');
		}

		if (isset($this->request->post['sms_notify_message'])) {
			$data['sms_notify_message'] = $this->request->post['sms_notify_message'];
		} else {
			$data['sms_notify_message'] = $this->config->get('sms_notify_message');
		}

		if (isset($this->request->post['sms_notify_gate_username'])) {
			$data['sms_notify_gate_username'] = $this->request->post['sms_notify_gate_username'];
		} else {
			$data['sms_notify_gate_username'] = $this->config->get('sms_notify_gate_username');
		}

		if (isset($this->request->post['sms_notify_gate_password'])) {
			$data['sms_notify_gate_password'] = $this->request->post['sms_notify_gate_password'];
		} else {
			$data['sms_notify_gate_password'] = $this->config->get('sms_notify_gate_password');
		}

		if (isset($this->request->post['sms_notify_alert'])) {
			$data['sms_notify_alert'] = $this->request->post['sms_notify_alert'];
		} else {
			$data['sms_notify_alert'] = $this->config->get('sms_notify_alert');
		}

		if (isset($this->request->post['sms_notify_copy'])) {
			$data['sms_notify_copy'] = $this->request->post['sms_notify_copy'];
		} else {
			$data['sms_notify_copy'] = $this->config->get('sms_notify_copy');
		}

		if (isset($this->request->post['sms_notify_admin_alert'])) {
			$data['admin_alert'] = $this->request->post['sms_notify_admin_alert'];
		} elseif ($this->config->get('sms_notify_admin_alert')) {
			$data['admin_alert'] = $this->config->get('sms_notify_admin_alert');
		} else {
			$data['admin_alert'] = '';
		}

		if (isset($this->request->post['sms_notify_client_alert'])) {
			$data['client_alert'] = $this->request->post['sms_notify_client_alert'];
		} elseif ($this->config->get('sms_notify_client_alert')) {
			$data['client_alert'] = $this->config->get('sms_notify_client_alert');
		} else {
			$data['client_alert'] = '';
		}

		if (isset($this->request->post['sms_notify_order_alert'])) {
			$data['order_alert'] = $this->request->post['sms_notify_order_alert'];
		} elseif ($this->config->get('sms_notify_order_alert')) {
			$data['order_alert'] = $this->config->get('sms_notify_order_alert');
		} else {
			$data['order_alert'] = '';
		}

		if (isset($this->request->post['sms_notify_reviews'])) {
			$data['reviews'] = $this->request->post['sms_notify_reviews'];
		} elseif ($this->config->get('sms_notify_reviews')) {
			$data['reviews'] = $this->config->get('sms_notify_reviews');
		} else {
			$data['reviews'] = '';
		}

	    if (isset($this->request->post['sms_notify_payment_alert'])) {
	      $data['payment_alert'] = $this->request->post['sms_notify_payment_alert'];
	    } elseif ($this->config->get('sms_notify_order_alert')) {
	      $data['payment_alert'] = $this->config->get('sms_notify_payment_alert');
	    } else {
	      $data['payment_alert'] = '';
	    }

		if (isset($this->request->post['sms_notify_translit'])) {
			$data['translit'] = $this->request->post['sms_notify_translit'];
		} elseif ($this->config->get('sms_notify_translit')) {
			$data['translit'] = $this->config->get('sms_notify_translit');
		} else {
			$data['translit'] = false;
		}

		if (isset($this->request->post['sms_notify_force'])) {
			$data['force'] = $this->request->post['sms_notify_force'];
		} elseif ($this->config->get('sms_notify_force')) {
			$data['force'] = $this->config->get('sms_notify_force');
		} else {
			$data['force'] = '';
		}

		if (isset($this->request->post['sms_notify_admin_template'])) {
			$data['admin_template'] = $this->request->post['sms_notify_admin_template'];
		} elseif ($this->config->get('sms_notify_admin_template')) {
			$data['admin_template'] = $this->config->get('sms_notify_admin_template');
		} else {
			$data['admin_template'] = '';
		}

		if (isset($this->request->post['sms_notify_client_template'])) {
			$data['client_template'] = $this->request->post['sms_notify_client_template'];
		} elseif ($this->config->get('sms_notify_client_template')) {
			$data['client_template'] = $this->config->get('sms_notify_client_template');
		} else {
			$data['client_template'] = '';
		}

		if (isset($this->request->post['sms_notify_reviews_template'])) {
			$data['reviews_template'] = $this->request->post['sms_notify_reviews_template'];
		} elseif ($this->config->get('sms_notify_reviews_template')) {
			$data['reviews_template'] = $this->config->get('sms_notify_reviews_template');
		} else {
			$data['reviews_template'] = '';
		}
//vicumg customers notify
        if (isset($this->request->post['sms_notify_customers_templates'])) {
            $data['customers_templates'] = $this->request->post['sms_notify_customers_templates'];
        } elseif ($this->config->get('sms_notify_customers_templates')) {
            $data['customers_templates'] = $this->config->get('sms_notify_customers_templates');
        } else {
            $data['customers_templates'] = [];
        }


		$data['payments'] = array();

		$data['payments'] = $this->model_extension_module_ochelp_sms_notify->getPaymentList();

		if (isset($this->request->post['sms_notify_payment'])) {
			$data['sms_payment'] = $this->request->post['sms_notify_payment'];
		} elseif ($this->config->get('sms_notify_payment')) {
			$data['sms_payment'] = $this->config->get('sms_notify_payment');
		} else {
			$data['sms_payment'] = array();
		}

		if (isset($this->request->post['sms_notify_payment_template'])) {
			$data['payment_template'] = $this->request->post['sms_notify_payment_template'];
		} elseif ($this->config->get('sms_notify_payment_template')) {
			$data['payment_template'] = $this->config->get('sms_notify_payment_template');
		} else {
			$data['payment_template'] = array();
		}

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		if (isset($this->request->post['sms_notify_customer_group'])) {
			$data['sms_customer_group'] = $this->request->post['sms_notify_customer_group'];
		} elseif ($this->config->get('sms_notify_customer_group')) {
			$data['sms_customer_group'] = $this->config->get('sms_notify_customer_group');
		} else {
			$data['sms_customer_group'] = array();
		}		

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		//CKEditor
		if ($this->config->get('config_editor_default')) {
			$this->document->addScript('view/javascript/ckeditor/ckeditor.js');
			$this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
		}

		$data['ckeditor'] = $this->config->get('config_editor_default');

		$data['lang'] = $this->language->get('lang');

		if (isset($this->request->post['sms_notify_sms_template'])) {
			$data['sms_template'] = $this->request->post['sms_notify_sms_template'];
		} elseif ($this->config->get('sms_notify_sms_template')) {
			$data['sms_template'] = html_entity_decode($this->config->get('sms_notify_sms_template'), ENT_QUOTES, 'UTF-8');
		} else {
			$data['sms_template'] = '';
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['sms_notify_status_template'])) {
			$data['order_status_template'] = $this->request->post['sms_notify_status_template'];
		} elseif ($this->config->get('sms_notify_status_template')) {
			$data['order_status_template'] = $this->config->get('sms_notify_status_template');
		} else {
			$data['order_status_template'] = array();
		}

		if (isset($this->request->post['sms_notify_order_status'])) {
			$data['sms_order_status'] = $this->request->post['sms_notify_order_status'];
		} elseif ($this->config->get('sms_notify_order_status')) {
			$data['sms_order_status'] = $this->config->get('sms_notify_order_status');
		} else {
			$data['sms_order_status'] = array();
		}

		if (isset($this->request->post['sms_notify_log'])) {
			$data['sms_notify_log'] = $this->request->post['sms_notify_log'];
		} elseif ($this->config->get('sms_notify_log')) {
			$data['sms_notify_log'] = $this->config->get('sms_notify_log');
		} else {
			$data['sms_notify_log'] = '';
		}

		$data['sms_log'] = '';

		$data['sms_log_filname'] = 'sms_log.log';

		if ($this->config->get('sms_notify_log')) {
			$file = DIR_LOGS . $data['sms_log_filname'];

			if (file_exists($file)) {
				$size = filesize($file);

				if ($size >= 5242880) {
					$suffix = array(
						'B',
						'KB',
						'MB',
						'GB',
						'TB',
						'PB',
						'EB',
						'ZB',
						'YB',
					);

					$i = 0;

					while (($size / 1024) > 1) {
						$size = $size / 1024;
						$i++;
					}

					$data['error_warning'] = sprintf($this->language->get('error_warning'), basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
				} else {
					$data['sms_log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
				}
			}
		} else {
			$this->clearLog();
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ochelp_sms_notify', $data));
	}

	public function order() {
		$this->load->language('extension/module/ochelp_sms_notify');

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		return $this->load->view('extension/module/ochelp_sms_notify_list', $data);
	}

	public function orderInfo() {
		$this->load->language('extension/module/ochelp_sms_notify');

		if (isset($this->request->get['order_id'])) {
			$data['order_id'] = $this->request->get['order_id'];
		} else {
			$data['order_id'] = 0;
		}

		if (isset($this->request->post['sms_notify_sms_template'])) {
			$data['sms_template'] = $this->request->post['sms_notify_sms_template'];
		} elseif ($this->config->get('sms_notify_sms_template')) {
			$data['sms_template'] = html_entity_decode($this->config->get('sms_notify_sms_template'), ENT_QUOTES, 'UTF-8');
		} else {
			$data['sms_template'] = '';
		}

		$data['force'] = $this->config->get('sms_notify_force');


		$data['user_token'] = $this->session->data['user_token'];

		return $this->load->view('extension/module/ochelp_sms_notify_info', $data);
	}

	public function sendSms() {

		$json = array();

		$this->load->language('extension/module/ochelp_sms_notify');

		$this->load->model('sale/order');
		$this->load->model('extension/module/ochelp_sms_notify');

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
		} else {
			$order_info = array();
		}

		if ((utf8_strlen($this->request->post['sms_message']) < 3)) {
			$json['error'] = $this->language->get('error_sms');
		}

		if ($this->config->get('sms_notify_gatename') && $this->config->get('sms_notify_gate_username')) {
			if ($order_info) {
				$phone = preg_replace("/[^0-9]/", '', $order_info['telephone']);
			} elseif ($this->request->post['phone']) {
				$phone = preg_replace("/[^0-9]/", '', $this->request->post['phone']);
			} else {
				$phone = false;
				$json['error'] = $this->language->get('error_sms');
			}
		} else {
			$json['error'] = $this->language->get('error_sms_setting');
		}

		if (!isset($json['error'])) {
			$options = array(
				'to' => $phone,
				'from' => $this->config->get('sms_notify_from'),
				'username' => $this->config->get('sms_notify_gate_username'),
				'password' => $this->config->get('sms_notify_gate_password'),
				'message' => $this->request->post['sms_message'],
			);

			$sms = new Sms($this->config->get('sms_notify_gatename'), $options);
           
			$sms->send();

			if($order_info) {
				$this->model_extension_module_ochelp_sms_notify->addOrderHistory($order_info['order_id'], $order_info['order_status_id'], $options['message']);
			}

			$json['success'] = $this->language->get('text_success_sms');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function sendSmsMass() {

			$json = array();

			$this->load->language('extension/module/ochelp_sms_notify');

			$this->load->model('sale/order');
			$this->load->model('extension/module/ochelp_sms_notify');
				

			if(isset($this->request->post)){
				$numbers = $this->model_extension_module_ochelp_sms_notify->getOrdersPhone($this->request->post);
			}else{
				$numbers = array();
			}
			if ((utf8_strlen($this->request->post['sms_message']) < 3)) {
				$json['error'] = $this->language->get('error_sms');
			}

			$phones = implode(',', array_unique($numbers));

	        if($this->config->get('sms_notify_gatename') && $this->config->get('sms_notify_gate_username')){
		        if (!$phones) {
		            $$phones = false;
		            $json['error'] = $this->language->get('error_sms');
		        }
	    	}else{
		        $json['error'] = $this->language->get('error_sms_setting');
	    	}
			
			if (!isset($json['error'])) {
				$options = array(
					'to'       => $phones,
					'from'     => $this->config->get('sms_notify_from'),
					'username' => $this->config->get('sms_notify_gate_username'),
					'password' => $this->config->get('sms_notify_gate_password'),
					'message'  => $this->request->post['sms_message']
				);

                $sms = new Sms($this->config->get('sms_notify_gatename'), $options);
                $sms->send();
					
				$json['success'] = $this->language->get('text_success_sms');
			}

		$this->response->setOutput(json_encode($json));
	}

	public function clearLog() {

		$json = array();

		$this->load->language('extension/module/ochelp_sms_notify');

		if (!$this->user->hasPermission('modify', 'extension/module/ochelp_sms_notify')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$file = DIR_LOGS . 'sms_log.log';

			$handle = fopen($file, 'w+');

			fclose($handle);

			$json['success'] = $this->language->get('text_success_log');
		}

		$this->response->setOutput(json_encode($json));
	}
    
    public function install() {
		if ($this->validate()) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('module_ochelp_sms_notify', array('module_ochelp_sms_notify_status' => '1'));			
			//Add Events			
			$this->load->model('setting/event');
			$this->model_setting_event->addEvent('ochelp_sms_notify_review_alert', 'catalog/model/catalog/review/addReview/before', 'extension/module/ochelp_sms_notify/review');
			$this->model_setting_event->addEvent('ochelp_sms_notify_order_alert', 'catalog/model/checkout/order/addOrderHistory/before', 'extension/module/ochelp_sms_notify/order');
		}
	}

	public function uninstall() {
		if ($this->validate()) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->deleteSetting('sms_notify');		
			//Remove Events			
			$this->load->model('setting/event');
			$this->model_setting_event->deleteEventByCode('ochelp_sms_notify_review_alert');
			$this->model_setting_event->deleteEventByCode('ochelp_sms_notify_order_alert');
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ochelp_sms_notify')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}