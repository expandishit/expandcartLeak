<?php
class ModelWkposSupplier extends Model {
  public function addSupplyRequest($data, $comment = '') {
    $user = $this->db->query("SELECT CONCAT(firstname, ' ', lastname) as name FROM " . DB_PREFIX . POS_USERS_TABLE . " WHERE user_id = '" . (int)$this->session->data['user_login_id'] . "'")->row;

    $sql = "INSERT INTO " . DB_PREFIX . "wkpos_supplier_request SET user_id = '" . $this->session->data['user_login_id'] . "', user_name = '" . $user['name'] . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()";

    $this->db->query($sql);

    $request_id = $this->db->getLastId();

    $user_info = $this->db->query("SELECT CONCAT(wu.firstname, ' ', wu.lastname) as user, wo.name as outlet, wo.address FROM " . DB_PREFIX . POS_USERS_TABLE . " wu LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wu.outlet_id = wo.outlet_id) WHERE wu.user_id = '" . $this->session->data['user_login_id'] . "'")->row;

    $suppliers = array();

    foreach ($data as $request) {
      $supplier = $this->db->query("SELECT CONCAT(firstname, ' ', lastname) as name FROM " . DB_PREFIX . "wkpos_supplier WHERE supplier_id = '" . $request['sid'] . "'")->row;

      $sql = "INSERT INTO " . DB_PREFIX . "wkpos_request_info SET request_id = '" . $request_id . "', supplier_id = '" . (int)$request['sid'] . "', supplier = '" . $supplier['name'] . "', product_id = '" . (int)$request['pid'] . "', quantity = '" . (int)$request['quant'] . "', comment = '" . $this->db->escape($request['comm']) . "'";

      $this->db->query($sql);

      $suppliers[$request['sid']][] = array(
        'product_id' => $request['pid'],
        'quantity'   => $request['quant'],
        'comment'    => $request['comm']
        );
    }

    if ($suppliers) {
      $this->sendSupplyMail($suppliers, $user_info);
    }
  }

  public function sendSupplyMail($suppliers, $user_info) {
    foreach ($suppliers as $supplier_id => $supplier) {
      $supplier_info = $this->db->query("SELECT CONCAT(firstname, ' ', lastname) as name, email FROM " . DB_PREFIX . "wkpos_supplier WHERE supplier_id = '" . (int)$supplier_id . "'")->row;
      $supplier_email = $supplier_info['email'];

      $subject = 'Order Request';
      $supplier_name = $supplier_info['name'];
      $products = array();

      foreach ($supplier as $info) {
        $product = $this->db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$info['product_id'] . "' AND language_id = '" . $this->config->get('config_language_id') . "'")->row;
        $products[] = array(
          'name'     => $product['name'],
          'quantity' => $info['quantity'],
          'comment'  => $info['comment']
          );
      }

      foreach ($products as $product) {
            $productsHtml .= '<tr>
              <td>'.$product['name'].'</td>
              <td>'.$product['quantity'].'</td>
              <td>'.$product['comment'].'</td>
            </tr>';
      }

      $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
              <html>
              <head>
              <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
              <title>'.$subject.'</title>
              </head>
              <body> 
              <div class="content">
                Dear '. $supplier_name.',
                <br /><br />
                Please send the below listed product(s) as per the given quantity as soon as possible.
                <br /><br />
                <table border="1" cellpadding="10" cellspacing="0" style="text-align: center;">
                  <thead style="font-weight: bold;">
                    <tr>
                      <td>Product Name</td>
                      <td>Quantity</td>
                      <td>Comment</td>
                    </tr>
                  </thead>
                  <tbody>
                    '.$productsHtml.'
                  </tbody>
                </table>
                <br /><br />
                Regards
                <br />
                '. $user_info['user'].'<br />
                '. $user_info['outlet'].'<br />
                '. $user_info['address'].'
              </div>
              </body>
              </html>';

      //$html = $this->load->view('default/template/wkpos/supplier_mail.tpl', $data);

      if(version_compare(VERSION, '2.0.1.1', '<=')) {
        /*Old mail code*/
        $mail = new Mail($this->config->get('config_mail'));
      } else {
        $mail = new Mail();

        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');
      }

      $mail->setTo($supplier_email);
      $mail->setFrom($this->config->get('config_email'));
      $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
      $mail->setSubject($subject);
      $mail->setNewHtml($html);
      $mail->setText(strip_tags($html));
      $mail->send();
    }
  }

  public function getRequestHistory() {
    $sql = "SELECT * FROM " . DB_PREFIX . "wkpos_supplier_request WHERE user_id = '" . $this->session->data['user_login_id'] . "' ORDER BY date_added DESC";

    $requests = $this->db->query($sql)->rows;

    return $requests;
  }

  public function getRequestInfo($request_id) {
    $sql = "SELECT pd.name, ri.quantity, ri.supplier as sname FROM " . DB_PREFIX . "wkpos_request_info ri LEFT JOIN " . DB_PREFIX . "product_description pd ON (ri.product_id = pd.product_id) WHERE pd.language_id = '" . $this->config->get('config_language_id') . "' AND request_id = '" . $request_id . "' ";

    $info = $this->db->query($sql)->rows;

    return $info;
  }
}
