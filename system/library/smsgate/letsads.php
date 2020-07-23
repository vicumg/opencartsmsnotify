<?php
class letsads extends SmsGate {
  protected $code = 'letsads';
  protected $data = array(
    'phone' => '',
    'pass' => '',
    'sender' => '',
    'recipient' => '',
    'username' => '',
    );
    public function __construct($options = array())
    {
        parent::__construct($options);
        $this->data['recipient']=explode(';',$options['to']);
    }

    public function getBalance() {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<request>';
    $xml .= '   <auth>';
    $xml .= '       <login>' . $this->data['username'] . '</login>';
    $xml .= '       <password>' .$this->data['password'] . '</password>';
    $xml .= '   </auth>';
    $xml .= '   <balance />';
    $xml .= '</request>';

    $response = ocmFileGet('http://letsads.com/api', $xml);

    $result = '';
    $xml_response = simplexml_load_string($response);
    if (!empty($xml_response)) {
      if ($xml_response[0]->name == 'Balance') {
        $result = $xml_response[0]->description . ' ' . $xml_response[0]->currency;
      }
    }
    return $result;
  }
  
  public function send($data=[]) {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<request>';
    $xml .= '<auth>';
      $xml .= '<login>' . $this->data['username'] . '</login>';
      $xml .= '<password>' .$this->data['password'] . '</password>';
    $xml .= '   </auth>';
    $xml .= '   <message>';
    $xml .= '       <from>' . $this->data['from'] . '</from>';
    $xml .= '       <text>' . $this->data['message'] . '</text>';
    foreach ($this->data['recipient'] as $recipient) {
      $xml .= '       <recipient>' . $recipient . '</recipient>';
    }
    $xml .= '   </message>';
    $xml .= '</request>';
      
    $response = ocmFileGet('http://letsads.com/api', $xml);

    $result = array();
    $xml_response = simplexml_load_string($response);
    if (!empty($xml_response)) {
      if ($xml_response[0]->name == 'Error') {
        $result['success'] = false;
        $result['description'] = $xml_response[0]->description->__toString();
      } else {
        $result['success'] = true;
      }
    }
    return $result;
  }
}