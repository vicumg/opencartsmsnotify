<?php
class ControllerSaleOrder extends Controller {
	private $error = array();


				public function setOrderStatus() {
					$json = array();

					$this->language->load('sale/order');

					$this->load->model('sale/order');

					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = -1;
						$json['error']['order'] = $this->language->get('error_order');
					}

					if (isset($this->request->post['order_status_id'])) {
						$order_status_id = $this->request->post['order_status_id'];
					} else {
						$order_status_id = -1;
						$json['error']['order_status_id'] = $this->language->get('error_order_status');
					}
					if (isset($this->request->post['user_change'])) {
						$user_change = $this->request->post['user_change'];
					} else {
						$user_change = '';
					}
					
					if (!$json) {									
						$res = $this->model_sale_order->setOrderStatus($order_id, $order_status_id);
						$this->model_sale_order->updateManagerUser($order_id, $user_change);
						$this->load->model('extension/module/ordersetting');		
						$status_info = $this->model_extension_module_ordersetting->getStatusSendOnOff($order_status_id);
						if(($status_info['status_send_on_off'] !='N') && ($status_info['order_status_subject'] !='') && ($status_info['os_email_template'] !='')){
							$this->sendMailOrderStatus($order_id, $order_status_id);
						}
						
						if ($res < 0) {
							$json['error']['order_status_id'] = $this->language->get('error_order_status_not_found') + $res;
						} else {
							$json['background_color'] = $res[0]['row_color'];
							$json['color_text'] = $res[0]['row_color_text'];
						}
					} 
					
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));	
				}
				
				private function sendMailOrderStatus($order_id, $order_status_id) {
					$text 	= "";
					$this->load->model('sale/order');
					$this->load->model('extension/module/ordersetting');	
					
					$order_info = $this->model_extension_module_ordersetting->getOrderStatusSendMail($order_id);
					$subject_info = $this->model_sale_order->getOrderStatusSubject($order_status_id, $order_info['language_id']);
					if(empty($subject_info)){
					$subject  = $order_info['order_status_name'];
					} else { 
					$subject  = $this->model_extension_module_ordersetting->getCustomFields($order_info, $subject_info);
					}	
					
					$html_message_info = $this->model_sale_order->getOrderStatusTemplates($order_status_id, $order_info['language_id']);		
					
					if(empty($html_message_info)){
					$html_message_info  = $order_info['order_status_name'];
					} else { 
					$html_message  = $this->model_extension_module_ordersetting->getCustomFields($order_info, $html_message_info);
					}
					$mail = new Mail($this->config->get('config_mail_engine'));
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

					$mail->setTo($order_info['email']);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender($order_info['store_name'], ENT_QUOTES, 'UTF-8');
					$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
					$mail->setHtml(html_entity_decode($html_message, ENT_QUOTES, 'UTF-8'));
					$mail->setText($text);
					$mail->send();
				}
				
				public function saveCommentManager() {
					$this->language->load('sale/order');
					
					$json = array();
					$order_id = null;
					$comment_manager = "";
		
					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = null;
					}
					if (isset($this->request->post['comment_manager'])) {
						$comment_manager = $this->request->post['comment_manager'];
					} else {
						$comment_manager = "";
					}
					
					$this->load->model('sale/order');
									
					$this->model_sale_order->updateCommentManager($order_id, $comment_manager);
					
					if (!$json) {
						$json['success'] = sprintf($order_id);
					} else {
						$json['redirect'] = "bad";
					}
		
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));		
				}
				public function saveTTN() {
					$this->language->load('sale/order');
					
					$json = array();
					$order_id = null;
					$text_ttn = "";
		
					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = null;
					}
					if (isset($this->request->post['text_ttn'])) {
						$text_ttn = $this->request->post['text_ttn'];
					} else {
						$text_ttn = "";
					}
					
					$this->load->model('sale/order');
									
					$this->model_sale_order->updateTTN($order_id, $text_ttn);
					
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));		
				}
				
				public function sendTTN() {
					$this->language->load('sale/order');
					
					$json = array();
					$order_id = null;
					$number_ttn = "";
		
					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = null;
					}
					if (isset($this->request->post['number_ttn'])) {
						$number_ttn = $this->request->post['number_ttn'];
					} else {
						$number_ttn = '';
					}
					if (isset($this->request->post['email_user'])) {
						$email_user = $this->request->post['email_user'];
					} else {
						$email_user = '';
					}
					
					
					$this->load->model('sale/order');
					$this->load->model('extension/module/ordersetting');	
					$order_info = $this->model_extension_module_ordersetting->getOrderStatusSendMail($order_id);
					
					$subjuct = $this->config->get('ttn_subject_ut');
					$html_message = $this->config->get('ttn_description_ut');
					$html_message_count = strlen($html_message[$order_info['language_id']]['text']);
					
					if(($subjuct[$order_info['language_id']]['text'] !='') && ($html_message_count >='20')) { 
						$this->sendMail($number_ttn, $email_user, $order_id);
					} else {
						$json['redirect'] = "bad1";
					}
					if (!$json) {
						$json['success'] = $this->language->get('text_ttn_sendmail');
					} else {
						$json['redirect'] = "bad2";
					}
		
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));	
				}
				private function sendMail($number_ttn, $email_user, $order_id) {
					
					$this->load->model('extension/module/ordersetting');	
					$order_info = $this->model_extension_module_ordersetting->getOrderStatusSendMail($order_id);
					$subject_text = $this->config->get('ttn_subject_ut');
					$html_message_text = $this->config->get('ttn_description_ut');
					$subject = $this->model_extension_module_ordersetting->getCustomFields($order_info, $subject_text[$order_info['language_id']]['text']);
					$html_message  = $this->model_extension_module_ordersetting->getCustomFields($order_info, $html_message_text[$order_info['language_id']]['text']);
					
					$mail = new Mail($this->config->get('config_mail_engine'));
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

					$mail->setTo($order_info['email']);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender($order_info['store_name'], ENT_QUOTES, 'UTF-8');
					$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
					$mail->setHtml(html_entity_decode($html_message, ENT_QUOTES, 'UTF-8'));
					$mail->send();
				}
				
				public function savePriceZak() {
					$this->language->load('sale/order');
					
					$json = array();
					$order_id = null;
					$price_zak = "";
		
					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = null;
					}
					if (isset($this->request->post['price_zak'])) {
						$price_zak = $this->request->post['price_zak'];
					} else {
						$price_zak = "";
					}
					if (isset($this->request->post['delivery_price'])) {
						$delivery_price = $this->request->post['delivery_price'];
					} else {
						$delivery_price = '0.0000';
					}
					if (isset($this->request->post['build_price'])) {
						$build_price = $this->request->post['build_price'];
					} else {
						$build_price = '0.0000';
					}
					
					if (isset($this->request->post['product_id'])) {
						$product_id = $this->request->post['product_id'];
					} else {
						$product_id = '';
					}
					if (isset($this->request->post['build_price_prefix'])) {
						$build_price_prefix = $this->request->post['build_price_prefix'];
					} else {
						$build_price_prefix = '+';
					}
					
					if (isset($this->request->post['sum_price_zak'])) {
						$sum_price_zak = $this->request->post['sum_price_zak'];
					} else {
						$sum_price_zak = '0.0000';
					}
					
					if (isset($this->request->post['rise_product_price'])) {
						$rise_product_price = $this->request->post['rise_product_price'];
					} else {
						$rise_product_price = '0.0000';
					}
					if (isset($this->request->post['rise_product_price_prefix'])) {
						$rise_product_price_prefix = $this->request->post['rise_product_price_prefix'];
					} else {
						$rise_product_price_prefix = '+';
					}
					$this->load->model('sale/order');
					
					if (!$json) {			
						$calculated_summ = $this->model_sale_order->updatePriceZak($order_id, $price_zak, $product_id, $sum_price_zak, $delivery_price, $build_price, $build_price_prefix, $rise_product_price, $rise_product_price_prefix);
						if(!$calculated_summ) {
							$json['error']['calculated_summ'] = $this->language->get('error_price_1');
						} else {
							$json['calculated_summ'] = $calculated_summ;
							$json['success'] = $this->language->get('success_calculated_summ_update');
						}
					} 
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));		
				}
				public function saveManagerUser() {
					$this->language->load('sale/order');
					
					$json = array();
					$order_id = null;
					$manager_user = "";
		
					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = null;
					}
					if (isset($this->request->post['manager_user'])) {
						$manager_user = $this->request->post['manager_user'];
					} else {
						$manager_user = "";
					}
					
					$this->load->model('sale/order');
									
					$this->model_sale_order->updateManagerUser($order_id, $manager_user);
		
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));		
				}
				public function updateBuild() {

					$json = array();

					$this->language->load('sale/order');

					$this->load->model('sale/order');

					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = -1;
						
					}

					if (isset($this->request->post['build_price_yes_no']) && ($this->request->post['build_price_yes_no'] == "Y" || $this->request->post['build_price_yes_no'] == "N")) {
						$build_price_yes_no = $this->request->post['build_price_yes_no'];
					} else {
						$build_price_yes_no = -1;
					}

					if (!$json) {									
						$this->model_sale_order->updateBuild($order_id, $build_price_yes_no);
						$json['success'] = $this->language->get('success_adservice_update');
					} 
					
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));	
				}
				public function updateRiseProduct() {

					$json = array();

					$this->language->load('sale/order');

					$this->load->model('sale/order');

					if (isset($this->request->post['order_id'])) {
						$order_id = $this->request->post['order_id'];
					} else {
						$order_id = -1;
						
					}

					if (isset($this->request->post['rise_product_yes_no']) && ($this->request->post['rise_product_yes_no'] == "Y" || $this->request->post['rise_product_yes_no'] == "N")) {
						$rise_product_yes_no = $this->request->post['rise_product_yes_no'];
					} else {
						$rise_product_yes_no = -1;
					}

					if (!$json) {									
						$this->model_sale_order->updateRiseProduct($order_id, $rise_product_yes_no);
						$json['success'] = $this->language->get('success_adservice_update');
					} 
					
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));	
				}
		
	public function index() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

		$this->getList();
	}

	public function add() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

		$this->getForm();
	}

	public function edit() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

		$this->getForm();
	}
	
	public function delete() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->session->data['success'] = $this->language->get('text_success');

		$url = '';

      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}
        
        //svs_order_plus_plus end
        
      

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}


      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
        
        //svs_order_plus_plus end
        
      
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
	
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true));
	}
			
	protected function getList() {

		// API login
			$this->load->model('user/api');

			$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

			if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
				$session = new Session($this->config->get('session_engine'), $this->registry);
				
				$session->start();
				
				$this->model_user_api->deleteApiSessionBySessonId($session->getId());
				
				$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
				
				$session->data['api_id'] = $api_info['api_id'];

				$data['api_token'] = $session->getId();
			} else {
				$data['api_token'] = '';
			}
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
		$data['entry_notify'] = $this->language->get('entry_notify');
		$data['text_column_products'] = $this->language->get('text_column_products');
		$data['text_column_comment_manager'] = $this->language->get('text_column_comment_manager');
		$data['text_column_price_purchase'] = $this->language->get('text_column_price_purchase');
		$data['text_column_total_profit'] = $this->language->get('text_column_total_profit');
		$data['text_column_rise_product'] = $this->language->get('text_column_rise_product');
		$data['text_column_build_price'] = $this->language->get('text_column_build_price');
		$data['text_column_delivery_price'] = $this->language->get('text_column_delivery_price');
		$data['text_column_manager_process_orders'] = $this->language->get('text_column_manager_process_orders');
		$data['text_column_send_ttn_email'] = $this->language->get('text_column_send_ttn_email');
		$data['text_btn_send_ttn'] = $this->language->get('text_btn_send_ttn');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['text_product_model'] = $this->language->get('text_product_model');
		$data['text_product_sku'] = $this->language->get('text_product_sku');
		$data['text_product_upc'] = $this->language->get('text_product_upc');
		$data['entry_build_yes_no'] = $this->language->get('entry_build_yes_no');
		$data['entry_rise_yes_no'] = $this->language->get('entry_rise_yes_no');
		$data['entry_delivery_price'] = $this->language->get('entry_delivery_price');
		$data['entry_calculated_summ'] = $this->language->get('entry_calculated_summ');
		$data['entry_manager_process_orders'] = $this->language->get('entry_manager_process_orders');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['entry_date_added_start'] = $this->language->get('entry_date_added_start');
		$data['entry_date_added_end'] = $this->language->get('entry_date_added_end');
		
		$config_on_off_model_product = $this->config->get('config_on_off_model_product');
		if (isset($config_on_off_model_product)) {
			$data['on_off_model_product'] = $this->config->get('config_on_off_model_product');
		} else {
			$data['on_off_model_product'] = '0';
		}
		$config_on_off_sku_product = $this->config->get('config_on_off_sku_product');
		if (isset($config_on_off_sku_product)) {
			$data['on_off_sku_product'] = $this->config->get('config_on_off_sku_product');
		} else {
			$data['on_off_sku_product'] = '0';
		}
		$config_on_off_upc_product = $this->config->get('config_on_off_upc_product');
		if (isset($config_on_off_upc_product)) {
			$data['on_off_upc_product'] = $this->config->get('config_on_off_upc_product');
		} else {
			$data['on_off_upc_product'] = '0';
		}
		$config_on_off_order_payment_method = $this->config->get('config_on_off_order_payment_method');
		if (isset($config_on_off_order_payment_method)) {
			$data['on_off_order_payment_method'] = $this->config->get('config_on_off_order_payment_method');
		} else {
			$data['on_off_order_payment_method'] = '0';
		}
		$config_on_off_order_shipping_method = $this->config->get('config_on_off_order_shipping_method');
		if (isset($config_on_off_order_shipping_method)) {
			$data['on_off_order_shipping_method'] = $this->config->get('config_on_off_order_shipping_method');
		} else {
			$data['on_off_order_shipping_method'] = '0';
		}		
		$config_on_off_column_product = $this->config->get('config_on_off_column_product');
		if (isset($config_on_off_column_product)) {
			$data['on_off_column_product'] = $this->config->get('config_on_off_column_product');
		} else {
			$data['on_off_column_product'] = '0';
		}
		$config_on_off_column_delivery_price = $this->config->get('config_on_off_column_delivery_price');
		if (isset($config_on_off_column_delivery_price)) {
			$data['on_off_column_delivery_price'] = $this->config->get('config_on_off_column_delivery_price');
		} else {
			$data['on_off_column_delivery_price'] = '0';
		}
		$config_on_off_column_build = $this->config->get('config_on_off_column_build');
		if (isset($config_on_off_column_build)) {
			$data['on_off_column_build'] = $this->config->get('config_on_off_column_build');
		} else {
			$data['on_off_column_build'] = '0';
		}
		$config_on_off_column_rise_product = $this->config->get('config_on_off_column_rise_product');
		if (isset($config_on_off_column_rise_product)) {
			$data['on_off_column_rise_product'] = $this->config->get('config_on_off_column_rise_product');
		} else {
			$data['on_off_column_rise_product'] = '0';
		}
		$config_on_off_column_comment_manager = $this->config->get('config_on_off_column_comment_manager');
		if (isset($config_on_off_column_comment_manager)) {
			$data['on_off_column_comment_manager'] = $this->config->get('config_on_off_column_comment_manager');
		} else {
			$data['on_off_column_comment_manager'] = '0';
		}
		$config_on_off_column_send_ttn_email = $this->config->get('config_on_off_column_send_ttn_email');
		if (isset($config_on_off_column_send_ttn_email)) {
			$data['on_off_column_send_ttn_email'] = $this->config->get('config_on_off_column_send_ttn_email');
		} else {
			$data['on_off_column_send_ttn_email'] = '0';
		}
		$config_on_off_column_price_purchase = $this->config->get('config_on_off_column_price_purchase');
		if (isset($config_on_off_column_price_purchase)) {
			$data['on_off_column_price_purchase'] = $this->config->get('config_on_off_column_price_purchase');
		} else {
			$data['on_off_column_price_purchase'] = '0';
		}
		$config_on_off_column_total_profit = $this->config->get('config_on_off_column_total_profit');
		if (isset($config_on_off_column_total_profit)) {
			$data['on_off_column_total_profit'] = $this->config->get('config_on_off_column_total_profit');
		} else {
			$data['on_off_column_total_profit'] = '0';
		}
		$config_on_off_column_manager_process_orders = $this->config->get('config_on_off_column_manager_process_orders');
		if (isset($config_on_off_column_manager_process_orders)) {
			$data['on_off_column_manager_process_orders'] = $this->config->get('config_on_off_column_manager_process_orders');
		} else {
			$data['on_off_column_manager_process_orders'] = '0';
		}
		
		//vicumg
        $data['notify']=$this->load->controller('extension/module/notify');
		$this->language->load('user/user');
		$this->load->model('user/user');
		$this->language->load('common/header');
		$data['logged'] = $this->user->getUserName();
		
		$results_get_user = $this->model_user_user->getUsers();
		foreach ($results_get_user as $result) {
      		$data['users'][] = array(
				'user_id'    => $result['user_id'],
				'username'   => $result['username'],
			);
		}
	
		
 
		if (isset($this->request->get['filter_build_price_yes_no'])) {
			$filter_build_price_yes_no = $this->request->get['filter_build_price_yes_no'];
		} else {
			$filter_build_price_yes_no = null;
		}
		if (isset($this->request->get['filter_rise_product_yes_no'])) {
			$filter_rise_product_yes_no = $this->request->get['filter_rise_product_yes_no'];
		} else {
			$filter_rise_product_yes_no = null;
		}
		if (isset($this->request->get['filter_delivery_price'])) {
			$filter_delivery_price = $this->request->get['filter_delivery_price'];
		} else {
			$filter_delivery_price = null;
		}
		if (isset($this->request->get['filter_calculated_summ'])) {
			$filter_calculated_summ = $this->request->get['filter_calculated_summ'];
		} else {
			$filter_calculated_summ = null;
		}
		if (isset($this->request->get['filter_manager_process_orders'])) {
			$filter_manager_process_orders = $this->request->get['filter_manager_process_orders'];
		} else {
			$filter_manager_process_orders = null;
		}
		if (isset($this->request->get['filter_date_added_start'])) {
			$filter_date_added_start = $this->request->get['filter_date_added_start'];
		} else {
			$filter_date_added_start = null;
		}
		if (isset($this->request->get['filter_date_added_end'])) {
			$filter_date_added_end = $this->request->get['filter_date_added_end'];
		} else {
			$filter_date_added_end = null;
		}
		
		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}


      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
        
        //svs_order_plus_plus end
        
      
		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}
		

		if (isset($this->request->get['filter_order_status'])) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = '';
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = '';
		}
		
		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}
        
        //svs_order_plus_plus end
        
      

 
		if (isset($this->request->get['filter_build_price_yes_no'])) {
			$url .= '&filter_build_price_yes_no=' . $this->request->get['filter_build_price_yes_no'];
		}
		if (isset($this->request->get['filter_rise_product_yes_no'])) {
			$url .= '&filter_rise_product_yes_no=' . $this->request->get['filter_rise_product_yes_no'];
		}
		if (isset($this->request->get['filter_delivery_price'])) {
			$url .= '&filter_delivery_price=' . $this->request->get['filter_delivery_price'];
		}
		if (isset($this->request->get['filter_calculated_summ'])) {
			$url .= '&filter_calculated_summ=' . $this->request->get['filter_calculated_summ'];
		}
		if (isset($this->request->get['filter_manager_process_orders'])) {
			$url .= '&filter_manager_process_orders=' . $this->request->get['filter_manager_process_orders'];
		}
		if (isset($this->request->get['filter_date_added_start'])) {
			$url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
		}
		if (isset($this->request->get['filter_date_added_end'])) {
			$url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
		}
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}


      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
        
        //svs_order_plus_plus end
        
      
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
	
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'], true);

					$data['invoice'] = ($this->config->get('orderpro_invoice_type')) ? $this->url->link('sale/orderpro/invoice', 'user_token=' . $this->session->data['user_token'], true) : $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'], true);
					$data['export_order'] = ($this->config->get('orderpro_export_orders')) ? $this->url->link('sale/orderpro/export', 'user_token=' . $this->session->data['user_token'], true) : '';
					$data['merge_order'] = ($this->config->get('orderpro_merge_orders')) ? 1 : 0;
					
					$data['button_export'] = $this->language->get('button_export');
					$data['button_merge'] = $this->language->get('button_merge');
						
		$data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'], true);
		$data['add'] = $this->url->link('sale/orderpro/edit', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = str_replace('&amp;', '&', $this->url->link('sale/order/delete', 'user_token=' . $this->session->data['user_token'] . $url, true));

		$data['orders'] = array();

		$filter_data = array(
			'filter_order_id'        => $filter_order_id,

      
        //svs_order_plus_plus start
        
        'filter_product_name'    => $filter_product_name,
        
        //svs_order_plus_plus end
        
      
			'filter_customer'	     => $filter_customer,
			'filter_order_status'    => $filter_order_status,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_total'           => $filter_total,
			'filter_date_added'      => $filter_date_added,
			'filter_date_modified'   => $filter_date_modified,
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$order_total = $this->model_sale_order->getTotalOrders($filter_data);

	//	$results = $this->model_sale_order->getOrders($filter_data);
		$results = $this->model_sale_order->getOrdersByTabs($filter_data);

      
        //svs_order_plus_plus start
        
        $novaposhta_cn_number = [];
        
        //svs_order_plus_plus end
        
      

		foreach ($results as $result) {

			
			$this->load->model('tool/image');
			
			$this->load->model('tool/upload');

			$data['products'] = array();
			$products = $this->model_sale_order->getOrderProductsList($result['order_id']);
			foreach ($products as $product){ 
				$option_data = array();

					$options = $this->model_sale_order->getOrderOptions($result['order_id'], $product['order_product_id']);
					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $option['value'],
								'type'  => $option['type']
							);
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$option_data[] = array(
									'name'  => $option['name'],
									'value' => $upload_info['name'],
									'type'  => $option['type'],
									'href'  => $this->url->link('tool/upload/download', 'token=' . $this->session->data['user_token'] . '&code=' . $upload_info['code'], 'SSL')
								);
							}
						}
					}
				$data['products'][] = array(
						'order_product_id' => $product['order_product_id'],
						'order_id'         => $product['order_id'],
						'product_id'       => $product['product_id'],
						'name'    	 	   => $product['name'],
						'price_zak'    	   => $product['price_zak'],
						'popup'    	 	   => $this->model_tool_image->resize($product['image'], 500, 500),
						'thumb'    	 	   => $this->model_tool_image->resize($product['image'], 100, 100),
						'model'    		   => $product['model'],
						'sku'    		   => $product['sku'],
						'upc'    		   => $product['upc'],
						'option'   		   => $option_data,
						'quantity'		   => $product['quantity'],
						'price'    		   => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $result['currency_code'], $result['currency_value']),
						'href'     		   => $this->url->link('catalog/product/edit', 'token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id'], 'SSL')
					);
			}
			
			$price_zak_sum_all_product=0;
			foreach($data['products'] as $sum_zak){
				$price_zak_sum_all_product+=($sum_zak['price_zak']*$sum_zak['quantity']);
			}
			$calculated_summ2 = $result['total']-$price_zak_sum_all_product-$result['delivery_price'].$result['build_price_prefix'].$result['build_price'].$result['rise_product_price_prefix'].$result['rise_product_price'];
			
		

      
        //svs_order_plus_plus start
        
        $novaposhta_cn_number[] = [
                'DocumentNumber' => $result['novaposhta_cn_number'],
                'Phone' => $result['telephone']
            ];
        
        //svs_order_plus_plus end
        
      
			$data['orders'][] = array(

      
        //svs_order_plus_plus start
        
        'novaposhta_status_text'    => '',
        
        //svs_order_plus_plus end
        
      
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'novaposhta_cn_number'      => $result['novaposhta_cn_number'],
				'order_status'  => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'shipping_code' => $result['shipping_code'],

			'row_color'      			=> $result['row_color'],
			'row_color_text'      		=> $result['row_color_text'],
			'firstname'      			=> $result['firstname'],
			'lastname'      			=> $result['lastname'],
			'telephone'      			=> $result['telephone'],
			'novaposhta_cn_number'      => $result['novaposhta_cn_number'],
			'shipping_city'     		=> $result['shipping_city'],
			'shipping_address_1'     	=> $result['shipping_address_1'],
			'email'     				=> $result['email'],
			'order_status_id'			=> $result['order_status_id'],
			'products'     				=> $data['products'],
			'comment_manager'     		=> $result['comment_manager'],
			'manager_process_orders'    => $result['manager_process_orders'],
			'build_price'    			=> $result['build_price'],
			'build_price_yes_no'    	=> $result['build_price_yes_no'],
			'build_price_prefix'    	=> $result['build_price_prefix'],
			'rise_product_price'    	=> $result['rise_product_price'],
			'rise_product_yes_no'    	=> $result['rise_product_yes_no'],
			'rise_product_price_prefix' => $result['rise_product_price_prefix'],
			'delivery_price'    		=> $result['delivery_price'],
			'payment_method'    		=> $result['payment_method'],
			'shipping_method'    		=> $result['shipping_method'],
			'text_ttn'    				=> $result['text_ttn'],
			'time_date_added'    		=> date($this->language->get('datetime_format'), strtotime($result['date_added'])),
			'calculated_summ'     		=> $this->currency->format(eval('return '.$calculated_summ2.';'), $result['currency_code'], '', true, $result['currency_code'], $result['currency_value']),
		
				'view'          => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true),
				'edit'          => $this->url->link('sale/orderpro/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
        if (isset($this->session->data['order_active_tab'])) {
            $data['order_active_tab']=$this->session->data['order_active_tab'];
        }else{
            $data['order_active_tab']='';
        }

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}
        
        //svs_order_plus_plus end
        
      

 
		if (isset($this->request->get['filter_build_price_yes_no'])) {
			$url .= '&filter_build_price_yes_no=' . $this->request->get['filter_build_price_yes_no'];
		}
		if (isset($this->request->get['filter_rise_product_yes_no'])) {
			$url .= '&filter_rise_product_yes_no=' . $this->request->get['filter_rise_product_yes_no'];
		}
		if (isset($this->request->get['filter_delivery_price'])) {
			$url .= '&filter_delivery_price=' . $this->request->get['filter_delivery_price'];
		}
		if (isset($this->request->get['filter_calculated_summ'])) {
			$url .= '&filter_calculated_summ=' . $this->request->get['filter_calculated_summ'];
		}
		if (isset($this->request->get['filter_manager_process_orders'])) {
			$url .= '&filter_manager_process_orders=' . $this->request->get['filter_manager_process_orders'];
		}
		if (isset($this->request->get['filter_date_added_start'])) {
			$url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
		}
		if (isset($this->request->get['filter_date_added_end'])) {
			$url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
		}
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}


      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
        
        //svs_order_plus_plus end
        
      
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_order'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url, true);
		$data['sort_customer'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_status'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=order_status' . $url, true);
		$data['sort_total'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.total' . $url, true);
		$data['sort_date_added'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_added' . $url, true);
		$data['sort_date_modified'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_modified' . $url, true);

		$url = '';

      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}
        
        //svs_order_plus_plus end
        
      

 
		if (isset($this->request->get['filter_build_price_yes_no'])) {
			$url .= '&filter_build_price_yes_no=' . $this->request->get['filter_build_price_yes_no'];
		}
		if (isset($this->request->get['filter_rise_product_yes_no'])) {
			$url .= '&filter_rise_product_yes_no=' . $this->request->get['filter_rise_product_yes_no'];
		}
		if (isset($this->request->get['filter_delivery_price'])) {
			$url .= '&filter_delivery_price=' . $this->request->get['filter_delivery_price'];
		}
		if (isset($this->request->get['filter_calculated_summ'])) {
			$url .= '&filter_calculated_summ=' . $this->request->get['filter_calculated_summ'];
		}
		if (isset($this->request->get['filter_manager_process_orders'])) {
			$url .= '&filter_manager_process_orders=' . $this->request->get['filter_manager_process_orders'];
		}
		if (isset($this->request->get['filter_date_added_start'])) {
			$url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
		}
		if (isset($this->request->get['filter_date_added_end'])) {
			$url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
		}
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}


      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
        
        //svs_order_plus_plus end
        
      
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['filter_order_id'] = $filter_order_id;
		$data['filter_customer'] = $filter_customer;
		$data['filter_order_status'] = $filter_order_status;
		$data['filter_order_status_id'] = $filter_order_status_id;
		$data['filter_total'] = $filter_total;
		$data['filter_date_added'] = $filter_date_added;

      
        //svs_order_plus_plus start
        
        $data['filter_product_name'] = $filter_product_name;
        
        //svs_order_plus_plus end
        
      
		$data['filter_date_modified'] = $filter_date_modified;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$this->load->model('localisation/order_status');

		 $orderStatuses = $this->model_localisation_order_status->getOrderStatuses();
        $data['order_statuses']=[];
      
        //svs_order_plus_plus start
        
        $filter_data['limit'] = 100000;
        
        foreach ($orderStatuses as $key => $value) {
            $orderStatusId= $value['order_status_id'];
            $filter_data['filter_order_status_id'] = $orderStatusId;
            $ordersWithStatus =  $this->model_sale_order->getTotalOrders($filter_data);

            $data['order_statuses'][$orderStatusId]['orders_count'] = (int)$ordersWithStatus;
            $data['order_statuses'][$orderStatusId]['order_status_id'] = $orderStatusId;
            $data['order_statuses'][$orderStatusId]['name'] = $value['name'];

        }
        
        //svs_order_plus_plus end
        
      

		// API login
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
		
		// API login
		$this->load->model('user/api');

		$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
			$session = new Session($this->config->get('session_engine'), $this->registry);
			
			$session->start();
					
			$this->model_user_api->deleteApiSessionBySessonId($session->getId());
			
			$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
			
			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        
        $data['svs_letsads_modal'] = $this->load->controller('extension/module/svs_letsads/modal');


                //OCHELP sms notify
        $data['sms_notify_list'] = $this->load->controller('extension/module/ochelp_sms_notify/order');
                

      
        //svs_order_plus_plus start
        
        $response = $this->load->controller('extension/shipping/novaposhta_simple/getTrackingDocument',$novaposhta_cn_number);
        
        foreach ($data['orders'] as &$value) {
            foreach ((array)$response as $value1) {
                
                if($value['novaposhta_cn_number'] == $value1['Number']){
                    
                    $value['novaposhta_status_text'] = $value1['Status'];
                    
                }
                
            }
        }
        
        //svs_order_plus_plus end
        
      
		$this->response->setOutput($this->load->view('sale/order_list', $data));
	}
		

					/* START Shipping Data */
					public function getShippingData() {
						$this->load->language('sale/order');

						$data = array();

						if (!empty($this->request->post['selected']) && $this->validate()) {
							$shipping_methods = array('novaposhta', 'ukrposhta');

							$settings = array();

							foreach ($shipping_methods as $shipping_method) {
								if ($this->config->get('shipping_' . $shipping_method . '_status')) {
									$settings[$shipping_method] = $this->config->get('shipping_' . $shipping_method);

									$data['shipping_methods'][$shipping_method]['heading'] = $this->language->get('heading_cn_' . $shipping_method);

									$data['shipping_methods'][$shipping_method]['cn_list'] = array (
										'text' => $this->language->get('text_cn_list'),
										'href' => $this->url->link('extension/shipping/' . $shipping_method . '/getCNList', 'user_token=' . $this->session->data['user_token'], 'SSL')
									);
								} else {
									unset($shipping_methods[$shipping_method]);
								}
							}

							$this->load->model('sale/order');

							$orders = $this->model_sale_order->getOrdersShippingData($this->request->post['selected']);

							foreach ($orders as $order) {
								foreach ($shipping_methods as $shipping_method) {
									if (!empty($settings[$shipping_method]['compatible_shipping_method']) && (empty($order['shipping_code']) || in_array($order['shipping_code'], $settings[$shipping_method]['compatible_shipping_method']) || in_array(stristr($order['shipping_code'], '.', true), $settings[$shipping_method]['compatible_shipping_method']))) {
										if ($order[$shipping_method . '_cn_number']) {
											unset($data['orders'][$order['order_id']]);

											if ($settings[$shipping_method]['consignment_edit']) {
												if ($settings[$shipping_method]['consignment_edit_text'][$this->config->get('config_language_id')]) {
													$text = $settings[$shipping_method]['consignment_edit_text'][$this->config->get('config_language_id')];
												} else {
													$text = $this->language->get('text_cn_edit');
												}

												if ($shipping_method == 'novaposhta') {
													$cn_id = '&cn_ref=' . $order['novaposhta_cn_ref'];
												} elseif ($shipping_method == 'ukrposhta') {
													$cn_id = '&cn_uuid=' . $order['ukrposhta_cn_uuid'];
												} else {
													$cn_id = '';
												}

												$data['orders'][$order['order_id']][$shipping_method]['edit'] = array(
													'text' => $text,
													'href' => $this->url->link('extension/shipping/' . $shipping_method . '/getCNForm', 'order_id=' . $order['order_id'] . '&user_token=' . $this->session->data['user_token'] . $cn_id, 'SSL')
												);
											}

											if ($settings[$shipping_method]['consignment_delete']) {
												if ($settings[$shipping_method]['consignment_delete_text'][$this->config->get('config_language_id')]) {
													$text = $settings[$shipping_method]['consignment_delete_text'][$this->config->get('config_language_id')];
												} else {
													$text = $this->language->get('text_cn_delete');
												}

												$data['orders'][$order['order_id']][$shipping_method]['delete'] = array(
													'text' => $text,
													'href' => ''
												);
											}

											break;
										} else {
											if ($settings[$shipping_method]['consignment_create']) {
												if ($settings[$shipping_method]['consignment_create_text'][$this->config->get('config_language_id')]) {
													$text = $settings[$shipping_method]['consignment_create_text'][$this->config->get('config_language_id')];
												} else {
													$text = $this->language->get('text_cn_create');
												}

												$data['orders'][$order['order_id']][$shipping_method]['create'] = array(
													'text' => $text,
													'href' => $this->url->link('extension/shipping/' . $shipping_method . '/getCNForm', 'order_id=' . $order['order_id'] . '&user_token=' . $this->session->data['user_token'], 'SSL')
												);
											}

											if ($settings[$shipping_method]['consignment_assignment_to_order']) {
												if ($settings[$shipping_method]['consignment_assignment_to_order_text'][$this->config->get('config_language_id')]) {
													$text = $settings[$shipping_method]['consignment_assignment_to_order_text'][$this->config->get('config_language_id')];
												} else {
													$text = $this->language->get('text_cn_assignment');
												}

												$data['orders'][$order['order_id']][$shipping_method]['assignment'] = array(
													'text' => $text,
													'href' => ''
												);
											}
										}
									}
								}
							}

							$data['heading_cn'] = $this->language->get('heading_cn');
							$data['text_cn_list'] = $this->language->get('text_cn_list');

							$data['entry_cn_number'] = $this->language->get('entry_cn_number');
						}

						if (!empty($this->error['warning'])) {
							$data['error'] = $this->error['warning'];
						}

						$this->response->addHeader('Content-Type: application/json');
						$this->response->setOutput(json_encode($data));
					}
					/* END Shipping Data */
				
	public function getForm() {
		$data['text_form'] = !isset($this->request->get['order_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';

      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}
        
        //svs_order_plus_plus end
        
      

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}


      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
        
        //svs_order_plus_plus end
        
      
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['cancel'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
		}

		if (!empty($order_info)) {
			$data['order_id'] = $this->request->get['order_id'];
			$data['store_id'] = $order_info['store_id'];
			$data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

			$data['customer'] = $order_info['customer'];
			$data['customer_id'] = $order_info['customer_id'];
			$data['customer_group_id'] = $order_info['customer_group_id'];
			$data['firstname'] = $order_info['firstname'];
			$data['lastname'] = $order_info['lastname'];
			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];
			$data['novaposhta_cn_number'] = $order_info['novaposhta_cn_number'];
			$data['account_custom_field'] = $order_info['custom_field'];

			$this->load->model('customer/customer');

			$data['addresses'] = $this->model_customer_customer->getAddresses($order_info['customer_id']);

			$data['payment_firstname'] = $order_info['payment_firstname'];
			$data['payment_lastname'] = $order_info['payment_lastname'];
			$data['payment_company'] = $order_info['payment_company'];
			$data['payment_address_1'] = $order_info['payment_address_1'];
			$data['payment_address_2'] = $order_info['payment_address_2'];
			$data['payment_city'] = $order_info['payment_city'];
			$data['payment_postcode'] = $order_info['payment_postcode'];
			$data['payment_country_id'] = $order_info['payment_country_id'];
			$data['payment_zone_id'] = $order_info['payment_zone_id'];
			$data['payment_custom_field'] = $order_info['payment_custom_field'];
			$data['payment_method'] = $order_info['payment_method'];
			$data['payment_code'] = $order_info['payment_code'];

			$data['shipping_firstname'] = $order_info['shipping_firstname'];
			$data['shipping_lastname'] = $order_info['shipping_lastname'];
			$data['shipping_company'] = $order_info['shipping_company'];
			$data['shipping_address_1'] = $order_info['shipping_address_1'];
			$data['shipping_address_2'] = $order_info['shipping_address_2'];
			$data['shipping_city'] = $order_info['shipping_city'];
			$data['shipping_postcode'] = $order_info['shipping_postcode'];
			$data['shipping_country_id'] = $order_info['shipping_country_id'];
			$data['shipping_zone_id'] = $order_info['shipping_zone_id'];
			$data['shipping_custom_field'] = $order_info['shipping_custom_field'];
			$data['shipping_method'] = $order_info['shipping_method'];
			$data['novaposhta_cn_number'] = $order_info['novaposhta_cn_number'];
			$data['shipping_code'] = $order_info['shipping_code'];

			// Products
			$data['order_products'] = array();

			$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$data['order_products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']),
					'quantity'   => $product['quantity'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'reward'     => $product['reward']
				);
			}

			// Vouchers
			$data['order_vouchers'] = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);

			$data['coupon'] = '';
			$data['voucher'] = '';
			$data['reward'] = '';

			$data['order_totals'] = array();

			$order_totals = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

			foreach ($order_totals as $order_total) {
				// If coupon, voucher or reward points
				$start = strpos($order_total['title'], '(') + 1;
				$end = strrpos($order_total['title'], ')');

				if ($start && $end) {
					$data[$order_total['code']] = substr($order_total['title'], $start, $end - $start);
				}
			}

			$data['order_status_id'] = $order_info['order_status_id'];
			$data['comment'] = $order_info['comment'];
			$data['affiliate_id'] = $order_info['affiliate_id'];
			$data['affiliate'] = $order_info['affiliate_firstname'] . ' ' . $order_info['affiliate_lastname'];
			$data['currency_code'] = $order_info['currency_code'];
		} else {
			$data['order_id'] = 0;
			$data['store_id'] = 0;
			$data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
			
			$data['customer'] = '';
			$data['customer_id'] = '';
			$data['customer_group_id'] = $this->config->get('config_customer_group_id');
			$data['firstname'] = '';
			$data['lastname'] = '';
			$data['email'] = '';
			$data['telephone'] = '';
			$data['novaposhta_cn_number'] = '';
			$data['customer_custom_field'] = array();

			$data['addresses'] = array();

			$data['payment_firstname'] = '';
			$data['payment_lastname'] = '';
			$data['payment_company'] = '';
			$data['payment_address_1'] = '';
			$data['payment_address_2'] = '';
			$data['payment_city'] = '';
			$data['payment_postcode'] = '';
			$data['payment_country_id'] = '';
			$data['payment_zone_id'] = '';
			$data['payment_custom_field'] = array();
			$data['payment_method'] = '';
			$data['payment_code'] = '';

			$data['shipping_firstname'] = '';
			$data['shipping_lastname'] = '';
			$data['shipping_company'] = '';
			$data['shipping_address_1'] = '';
			$data['shipping_address_2'] = '';
			$data['shipping_city'] = '';
			$data['shipping_postcode'] = '';
			$data['shipping_country_id'] = '';
			$data['shipping_zone_id'] = '';
			$data['shipping_custom_field'] = array();
			$data['shipping_method'] = '';
			$data['shipping_code'] = '';

			$data['order_products'] = array();
			$data['order_vouchers'] = array();
			$data['order_totals'] = array();

			$data['order_status_id'] = $this->config->get('config_order_status_id');
			$data['comment'] = '';
			$data['affiliate_id'] = '';
			$data['affiliate'] = '';
			$data['currency_code'] = $this->config->get('config_currency');

			$data['coupon'] = '';
			$data['voucher'] = '';
			$data['reward'] = '';
		}

		// Stores
		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			);
		}

		// Customer Groups
		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		// Custom Fields
		$this->load->model('customer/custom_field');

		$data['custom_fields'] = array();

		$filter_data = array(
			'sort'  => 'cf.sort_order',
			'order' => 'ASC'
		);

		$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

		foreach ($custom_fields as $custom_field) {
			$data['custom_fields'][] = array(
				'custom_field_id'    => $custom_field['custom_field_id'],
				'custom_field_value' => $this->model_customer_custom_field->getCustomFieldValues($custom_field['custom_field_id']),
				'name'               => $custom_field['name'],
				'value'              => $custom_field['value'],
				'type'               => $custom_field['type'],
				'location'           => $custom_field['location'],
				'sort_order'         => $custom_field['sort_order']
			);
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

      
        //svs_order_plus_plus start
        
        $filter_data['limit'] = 100000;
        
        foreach ($data['order_statuses'] as $key => &$value) {
            
            $filter_data['filter_order_status_id'] = $value['order_status_id'];
            
            $value['name'] .= ' ('. $this->model_sale_order->getTotalOrders($filter_data) .')';
        }
        
        //svs_order_plus_plus end
        
      

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$data['voucher_min'] = $this->config->get('config_voucher_min');

		$this->load->model('sale/voucher_theme');

		$data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

		// API login
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
		
		// API login
		$this->load->model('user/api');

		$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
			$session = new Session($this->config->get('session_engine'), $this->registry);
			
			$session->start();
					
			$this->model_user_api->deleteApiSessionBySessonId($session->getId());
			
			$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
			
			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/order_form', $data));
	}

	public function info() {
		$this->load->model('sale/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$order_info = $this->model_sale_order->getOrder($order_id);

		if ($order_info) {
			$this->load->language('sale/order');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['text_ip_add'] = sprintf($this->language->get('text_ip_add'), $this->request->server['REMOTE_ADDR']);
			$data['text_order'] = sprintf($this->language->get('text_order'), $this->request->get['order_id']);

			$url = '';

      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
		}
        
        //svs_order_plus_plus end
        
      

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}


      
        //svs_order_plus_plus start
        
        if (isset($this->request->get['filter_product_name'])) {
			$filter_product_name = $this->request->get['filter_product_name'];
		} else {
			$filter_product_name = '';
		}
        
        //svs_order_plus_plus end
        
      
			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_order_status'])) {
				$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
			}
			
			if (isset($this->request->get['filter_order_status_id'])) {
				$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
			}
			
			if (isset($this->request->get['filter_total'])) {
				$url .= '&filter_total=' . $this->request->get['filter_total'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
			);

			$data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
			$data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);

					$data['invoice'] = ($this->config->get('orderpro_invoice_type')) ? $this->url->link('sale/orderpro/invoice', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true) : $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
					$data['export_order'] = ($this->config->get('orderpro_export_orders')) ? $this->url->link('sale/orderpro/export', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true) : '';
					$data['button_export'] = $this->language->get('button_export');
						
			$data['edit'] = $this->url->link('sale/orderpro/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
			$data['cancel'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true);

			$data['user_token'] = $this->session->data['user_token'];

			$data['order_id'] = $this->request->get['order_id'];

			$data['store_id'] = $order_info['store_id'];
			$data['store_name'] = $order_info['store_name'];
			
			if ($order_info['store_id'] == 0) {
				$data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
			} else {
				$data['store_url'] = $order_info['store_url'];
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			$data['firstname'] = $order_info['firstname'];
			$data['lastname'] = $order_info['lastname'];

			if ($order_info['customer_id']) {
				$data['customer'] = $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['customer_id'], true);
			} else {
				$data['customer'] = '';
			}

			$this->load->model('customer/customer_group');

			$customer_group_info = $this->model_customer_customer_group->getCustomerGroup($order_info['customer_group_id']);

                $this->load->model('extension/module/simplecustom');

                $customInfo = $this->model_extension_module_simplecustom->getCustomFields('order', $order_info['order_id'], $order_info['language_code']);
            

			if ($customer_group_info) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '';
			}

			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];

			$data['shipping_method'] = $order_info['shipping_method'];
			$data['novaposhta_cn_number'] = $order_info['novaposhta_cn_number'];
			$data['payment_method'] = $order_info['payment_method'];

			// Payment Address
			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);


                $find[] = '{company_id}';
                $find[] = '{tax_id}';
                $replace['company_id'] = isset($order_info['payment_company_id']) ? $order_info['payment_company_id'] : '';
                $replace['tax_id'] = isset($order_info['payment_tax_id']) ? $order_info['payment_tax_id'] : '';

                foreach($customInfo as $id => $value) {
                    if (strpos($id, 'payment_') === 0) {
                        $id = str_replace('payment_', '', $id);
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    } elseif (strpos($id, 'shipping_') === false) {
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    }
                }
            
			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			// Shipping Address
			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);


                $find[] = '{company_id}';
                $find[] = '{tax_id}';
                $replace['company_id'] = isset($order_info['shipping_company_id']) ? $order_info['shipping_company_id'] : '';
                $replace['tax_id'] = isset($order_info['shipping_tax_id']) ? $order_info['shipping_tax_id'] : '';

                foreach($customInfo as $id => $value) {
                    if (strpos($id, 'shipping_') === 0) {
                        $id = str_replace('shipping_', '', $id);
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    } elseif (strpos($id, 'payment_') === false) {
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    }
                }
            
			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			// Uploaded files
			$this->load->model('tool/upload');

			$data['products'] = array();

			$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $upload_info['name'],
								'type'  => $option['type'],
								'href'  => $this->url->link('tool/upload/download', 'user_token=' . $this->session->data['user_token'] . '&code=' . $upload_info['code'], true)
							);
						}
					}
				}

				$data['products'][] = array(
					'order_product_id' => $product['order_product_id'],
					'product_id'       => $product['product_id'],
					'name'    	 	   => $product['name'],
					'model'    		   => $product['model'],
					'option'   		   => $option_data,
					'quantity'		   => $product['quantity'],
					'price'    		   => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    		   => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'href'     		   => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id'], true)
				);
			}

			$data['vouchers'] = array();

			$vouchers = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
					'href'        => $this->url->link('sale/voucher/edit', 'user_token=' . $this->session->data['user_token'] . '&voucher_id=' . $voucher['voucher_id'], true)
				);
			}

			$data['totals'] = array();

			$totals = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			$this->load->model('customer/customer');

			$data['reward'] = $order_info['reward'];

			$data['reward_total'] = $this->model_customer_customer->getTotalCustomerRewardsByOrderId($this->request->get['order_id']);

			$data['affiliate_firstname'] = $order_info['affiliate_firstname'];
			$data['affiliate_lastname'] = $order_info['affiliate_lastname'];

			if ($order_info['affiliate_id']) {
				$data['affiliate'] = $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['affiliate_id'], true);
			} else {
				$data['affiliate'] = '';
			}

			$data['commission'] = $this->currency->format($order_info['commission'], $order_info['currency_code'], $order_info['currency_value']);

			$this->load->model('customer/customer');

			$data['commission_total'] = $this->model_customer_customer->getTotalTransactionsByOrderId($this->request->get['order_id']);

			$this->load->model('localisation/order_status');

			$order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

			if ($order_status_info) {
				$data['order_status'] = $order_status_info['name'];
			} else {
				$data['order_status'] = '';
			}

			$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

      
        //svs_order_plus_plus start
        
        $filter_data['limit'] = 100000;
        
        foreach ($data['order_statuses'] as $key => &$value) {
            
            $filter_data['filter_order_status_id'] = $value['order_status_id'];
            
            $value['name'] .= ' ('. $this->model_sale_order->getTotalOrders($filter_data) .')';
        }
        
        //svs_order_plus_plus end
        
      

			$data['order_status_id'] = $order_info['order_status_id'];

			$data['account_custom_field'] = $order_info['custom_field'];

			// Custom Fields
			$this->load->model('customer/custom_field');

			$data['account_custom_fields'] = array();

			$filter_data = array(
				'sort'  => 'cf.sort_order',
				'order' => 'ASC'
			);

			$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'account' && isset($order_info['custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['account_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['account_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['account_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['custom_field'][$custom_field['custom_field_id']]
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['account_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name']
							);
						}
					}
				}
			}

			// Custom fields
			$data['payment_custom_fields'] = array();

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address' && isset($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['payment_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['payment_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['payment_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name'],
									'sort_order' => $custom_field['sort_order']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['payment_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['payment_custom_field'][$custom_field['custom_field_id']],
							'sort_order' => $custom_field['sort_order']
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['payment_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}
				}
			}

			// Shipping
			$data['shipping_custom_fields'] = array();

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['shipping_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['shipping_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name'],
									'sort_order' => $custom_field['sort_order']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['shipping_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['shipping_custom_field'][$custom_field['custom_field_id']],
							'sort_order' => $custom_field['sort_order']
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['shipping_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}
				}
			}


                $this->load->model('extension/module/simplecustom');

                $customAccountInfo = $this->model_extension_module_simplecustom->getInfoWithValues('order', $order_info['order_id'], 'customer');

                foreach ($customAccountInfo as $key => $info) {
                    if (is_array($info['value'])) {
                        $tmp = array();
                        foreach ($info['value'] as $v) {
                            $tmp[] = !empty($info['values']) && !empty($v) && !empty($info['values'][$v]) ? $info['values'][$v] : $v;
                        }
                        $value = implode(',', $tmp);
                    } else {
                        $value = !empty($info['values']) && !empty($info['value']) && !empty($info['values'][$info['value']]) ? $info['values'][$info['value']] : $info['value'];
                    }

                    if ($info['type'] == 'file') {
                        $value = '<a href="index.php?route=extension/module/simple/download&user_token='. $this->session->data['token'] . '&name='.$value.'">'.$info['filename'].'</a>';
                    }
                        
                    $data['account_custom_fields'][] = array(
                        'name'  => $info['label'],
                        'value' => $value,
                        'sort_order' => 0
                    );
                }

                $customShippingInfo = $this->model_extension_module_simplecustom->getInfoWithValues('order', $order_info['order_id'], 'shipping_address');

                foreach ($customShippingInfo as $key => $info) {
                    if (is_array($info['value'])) {
                        $tmp = array();
                        foreach ($info['value'] as $v) {
                            $tmp[] = !empty($info['values']) && !empty($v) && !empty($info['values'][$v]) ? $info['values'][$v] : $v;
                        }
                        $value = implode(',', $tmp);
                    } else {
                        $value = !empty($info['values']) && !empty($info['value']) && !empty($info['values'][$info['value']]) ? $info['values'][$info['value']] : $info['value'];
                    }

                    if ($info['type'] == 'file') {
                        $value = '<a href="index.php?route=extension/module/simple/download&user_token='. $this->session->data['token'] . '&name='.$value.'">'.$info['filename'].'</a>';
                    }

                    $data['account_custom_fields'][] = array(
                        'name'  => $info['label'],
                        'value' => $value,
                        'sort_order' => 0
                    );
                }

                $customPaymentInfo = $this->model_extension_module_simplecustom->getInfoWithValues('order', $order_info['order_id'], 'payment_address');

                foreach ($customPaymentInfo as $key => $info) {
                    if (is_array($info['value'])) {
                        $tmp = array();
                        foreach ($info['value'] as $v) {
                            $tmp[] = !empty($info['values']) && !empty($v) && !empty($info['values'][$v]) ? $info['values'][$v] : $v;
                        }
                        $value = implode(',', $tmp);
                    } else {
                        $value = !empty($info['values']) && !empty($info['value']) && !empty($info['values'][$info['value']]) ? $info['values'][$info['value']] : $info['value'];
                    }

                    if ($info['type'] == 'file') {
                        $value = '<a href="index.php?route=extension/module/simple/download&user_token='. $this->session->data['token'] . '&name='.$value.'">'.$info['filename'].'</a>';
                    }
                    
                    $data['account_custom_fields'][] = array(
                        'name'  => $info['label'],
                        'value' => $value,
                        'sort_order' => 0
                    );
                }
            
			$data['ip'] = $order_info['ip'];
			$data['forwarded_ip'] = $order_info['forwarded_ip'];
			$data['user_agent'] = $order_info['user_agent'];
			$data['accept_language'] = $order_info['accept_language'];

			// Additional Tabs
			$data['tabs'] = array();

			if ($this->user->hasPermission('access', 'extension/payment/' . $order_info['payment_code'])) {
				if (is_file(DIR_CATALOG . 'controller/extension/payment/' . $order_info['payment_code'] . '.php')) {
					$content = $this->load->controller('extension/payment/' . $order_info['payment_code'] . '/order');
				} else {
					$content = '';
				}

				if ($content) {
					$this->load->language('extension/payment/' . $order_info['payment_code']);

					$data['tabs'][] = array(
						'code'    => $order_info['payment_code'],
						'title'   => $this->language->get('heading_title'),
						'content' => $content
					);
				}
			}

			$this->load->model('setting/extension');

			$extensions = $this->model_setting_extension->getInstalled('fraud');

			foreach ($extensions as $extension) {
				if ($this->config->get('fraud_' . $extension . '_status')) {
					$this->load->language('extension/fraud/' . $extension, 'extension');

					$content = $this->load->controller('extension/fraud/' . $extension . '/order');

					if ($content) {
						$data['tabs'][] = array(
							'code'    => $extension,
							'title'   => $this->language->get('extension')->get('heading_title'),
							'content' => $content
						);
					}
				}
			}
			
			// The URL we send API requests to
			$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
			
			// API login
			$this->load->model('user/api');

			$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

			if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
				$session = new Session($this->config->get('session_engine'), $this->registry);
				
				$session->start();
				
				$this->model_user_api->deleteApiSessionBySessonId($session->getId());
				
				$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
				
				$session->data['api_id'] = $api_info['api_id'];

				$data['api_token'] = $session->getId();
			} else {
				$data['api_token'] = '';
			}

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');


				//Blackbox
				$data['blackbox'] = $this->load->controller('common/blackbox');
				//Blackbox
			

                //OCHELP sms notify
        $data['sms_notify_info'] = $this->load->controller('extension/module/ochelp_sms_notify/orderInfo');
                
			$this->response->setOutput($this->load->view('sale/order_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function createInvoiceNo() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif (isset($this->request->get['order_id'])) {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$invoice_no = $this->model_sale_order->createInvoiceNo($order_id);

			if ($invoice_no) {
				$json['invoice_no'] = $invoice_no;
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addReward() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info && $order_info['customer_id'] && ($order_info['reward'] > 0)) {
				$this->load->model('customer/customer');

				$reward_total = $this->model_customer_customer->getTotalCustomerRewardsByOrderId($order_id);

				if (!$reward_total) {
					$this->model_customer_customer->addReward($order_info['customer_id'], $this->language->get('text_order_id') . ' #' . $order_id, $order_info['reward'], $order_id);
				}
			}

			$json['success'] = $this->language->get('text_reward_added');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeReward() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {
				$this->load->model('customer/customer');

				$this->model_customer_customer->deleteReward($order_id);
			}

			$json['success'] = $this->language->get('text_reward_removed');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addCommission() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {
				$this->load->model('customer/customer');

				$affiliate_total = $this->model_customer_customer->getTotalTransactionsByOrderId($order_id);

				if (!$affiliate_total) {
					$this->model_customer_customer->addTransaction($order_info['affiliate_id'], $this->language->get('text_order_id') . ' #' . $order_id, $order_info['commission'], $order_id);
				}
			}

			$json['success'] = $this->language->get('text_commission_added');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeCommission() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {
				$this->load->model('customer/customer');

				$this->model_customer_customer->deleteTransactionByOrderId($order_id);
			}

			$json['success'] = $this->language->get('text_commission_removed');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function history() {
		$this->load->language('sale/order');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$this->load->model('sale/order');

		$results = $this->model_sale_order->getOrderHistories($this->request->get['order_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'status'     => $result['status'],
				'comment'    => nl2br($result['comment']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_sale_order->getTotalOrderHistories($this->request->get['order_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/order/history', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->get['order_id'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('sale/order_history', $data));
	}

	public function invoice() {
		$this->load->language('sale/order');

		$data['title'] = $this->language->get('text_invoice');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$this->load->model('sale/order');

		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                $this->load->model('extension/module/simplecustom');

                $customInfo = $this->model_extension_module_simplecustom->getCustomFields('order', $order_info['order_id'], $order_info['language_code']);
            

				if ($store_info) {
					$store_address = $store_info['config_address'];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
					$store_fax = $store_info['config_fax'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
					$store_fax = $this->config->get('config_fax');
				}

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}

				if ($order_info['payment_address_format']) {
					$format = $order_info['payment_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => $order_info['payment_zone_code'],
					'country'   => $order_info['payment_country']
				);


                $find[] = '{company_id}';
                $find[] = '{tax_id}';
                $replace['company_id'] = isset($order_info['payment_company_id']) ? $order_info['payment_company_id'] : '';
                $replace['tax_id'] = isset($order_info['payment_tax_id']) ? $order_info['payment_tax_id'] : '';

                foreach($customInfo as $id => $value) {
                    if (strpos($id, 'payment_') === 0) {
                        $id = str_replace('payment_', '', $id);
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    } elseif (strpos($id, 'shipping_') === false) {
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    }
                }
            
				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);


                $find[] = '{company_id}';
                $find[] = '{tax_id}';
                $replace['company_id'] = isset($order_info['shipping_company_id']) ? $order_info['shipping_company_id'] : '';
                $replace['tax_id'] = isset($order_info['shipping_tax_id']) ? $order_info['shipping_tax_id'] : '';

                foreach($customInfo as $id => $value) {
                    if (strpos($id, 'shipping_') === 0) {
                        $id = str_replace('shipping_', '', $id);
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    } elseif (strpos($id, 'payment_') === false) {
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    }
                }
            
				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_data = array();

					$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$value = $upload_info['name'];
							} else {
								$value = '';
							}
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $value
						);
					}

					$product_data[] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,
						'quantity' => $product['quantity'],
						'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
						'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$voucher_data = array();

				$vouchers = $this->model_sale_order->getOrderVouchers($order_id);

				foreach ($vouchers as $voucher) {
					$voucher_data[] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$total_data = array();

				$totals = $this->model_sale_order->getOrderTotals($order_id);

				foreach ($totals as $total) {
					$total_data[] = array(
						'title' => $total['title'],
						'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}


      
        //svs_order_plus_plus start
        
        $novaposhta_cn_number[] = [
                'DocumentNumber' => $result['novaposhta_cn_number'],
                'Phone' => $result['telephone']
            ];
        
        //svs_order_plus_plus end
        
      
				$data['orders'][] = array(

      
        //svs_order_plus_plus start
        
        'novaposhta_status_text'    => '',
        
        //svs_order_plus_plus end
        
      
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'store_fax'        => $store_fax,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'novaposhta_cn_number' => $order_info['novaposhta_cn_number'],
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'payment_address'  => $payment_address,
					'payment_method'   => $order_info['payment_method'],
					'product'          => $product_data,
					'voucher'          => $voucher_data,
					'total'            => $total_data,
					'comment'          => nl2br($order_info['comment'])
				);
			}
		}

		$this->response->setOutput($this->load->view('sale/order_invoice', $data));
	}

	public function shipping() {
		$this->load->language('sale/order');

		$data['title'] = $this->language->get('text_shipping');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$this->load->model('sale/order');

		$this->load->model('catalog/product');

		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_sale_order->getOrder($order_id);

			// Make sure there is a shipping method
			if ($order_info && $order_info['shipping_code']) {
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                $this->load->model('extension/module/simplecustom');

                $customInfo = $this->model_extension_module_simplecustom->getCustomFields('order', $order_info['order_id'], $order_info['language_code']);
            

				if ($store_info) {
					$store_address = $store_info['config_address'];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
				}

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}

				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);


                $find[] = '{company_id}';
                $find[] = '{tax_id}';
                $replace['company_id'] = isset($order_info['shipping_company_id']) ? $order_info['shipping_company_id'] : '';
                $replace['tax_id'] = isset($order_info['shipping_tax_id']) ? $order_info['shipping_tax_id'] : '';

                foreach($customInfo as $id => $value) {
                    if (strpos($id, 'shipping_') === 0) {
                        $id = str_replace('shipping_', '', $id);
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    } elseif (strpos($id, 'payment_') === false) {
                        $find[] = '{'.$id.'}';
                        $replace[$id] = $value;
                    }
                }
            
				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_weight = '';

					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					if ($product_info) {
						$option_data = array();

						$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

						foreach ($options as $option) {
							if ($option['type'] != 'file') {
								$value = $option['value'];
							} else {
								$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

								if ($upload_info) {
									$value = $upload_info['name'];
								} else {
									$value = '';
								}
							}

							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $value
							);

							$product_option_value_info = $this->model_catalog_product->getProductOptionValue($product['product_id'], $option['product_option_value_id']);

							if ($product_option_value_info) {
								if ($product_option_value_info['weight_prefix'] == '+') {
									$option_weight += $product_option_value_info['weight'];
								} elseif ($product_option_value_info['weight_prefix'] == '-') {
									$option_weight -= $product_option_value_info['weight'];
								}
							}
						}

						$product_data[] = array(
							'name'     => $product_info['name'],
							'model'    => $product_info['model'],
							'option'   => $option_data,
							'quantity' => $product['quantity'],
							'location' => $product_info['location'],
							'sku'      => $product_info['sku'],
							'upc'      => $product_info['upc'],
							'ean'      => $product_info['ean'],
							'jan'      => $product_info['jan'],
							'isbn'     => $product_info['isbn'],
							'mpn'      => $product_info['mpn'],
							'weight'   => $this->weight->format(($product_info['weight'] + (float)$option_weight) * $product['quantity'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point'))
						);
					}
				}


      
        //svs_order_plus_plus start
        
        $novaposhta_cn_number[] = [
                'DocumentNumber' => $result['novaposhta_cn_number'],
                'Phone' => $result['telephone']
            ];
        
        //svs_order_plus_plus end
        
      
				$data['orders'][] = array(

      
        //svs_order_plus_plus start
        
        'novaposhta_status_text'    => '',
        
        //svs_order_plus_plus end
        
      
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'novaposhta_cn_number'  => $order_info['novaposhta_cn_number'],
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'product'          => $product_data,
					'comment'          => nl2br($order_info['comment'])
				);
			}
		}

		$this->response->setOutput($this->load->view('sale/order_shipping', $data));
	}

	public function setActiveTab(){

	    $this->session->data['order_active_tab']=$this->request->post['order_active_tab'];

        $json['success'] ='success';

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
