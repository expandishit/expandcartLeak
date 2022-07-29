<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Api\Models\Customer;

class CustomerController extends Controller
{

    private $customer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->customer = $this->container['customer'];
    }

    /**
     * Get customers method
     *
     * @param integer start/limit
     *
     * @return json
     * */
    public function getCustomers(Request $request, Response $response)
    {
        $params = $_GET; //$request->getQueryParams();

        $page  = $params['page'] ?? 0;
        $limit = $params['limit'] ?? 20;

        $data = [
            'sort'  => $params['sort'] ?? null,
            'order' => $params['order'] ?? null,
            'start' => $page * $limit,
            'limit' => $limit ,
        ];

        //$customer = (new Customer)->setContainer($this->container);

        return $response->withJson($this->customer->getAll($data));
    }

    /**
     * Forget password method, send email/sms with security code to use in reset password endpoint
     *
     * @param string email/phone
     *
     * @return json
     * */
    public function forgetpassword(Request $request, Response $response)
    {

        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        // $customer = (new Customer)->setContainer($this->container);

        if (isset($parameters['email'])){
            $val = $parameters['email'];
            $col =  'email';
        }else if(isset($parameters['phone'])){
            $val = $parameters['phone'];
            $col =  'telephone';
        }

        $customer_data = $this->customer->getCustomerByCol($val, $col);

        if(!$customer_data){
            $status = 'failed';
            $message = 'User not found';
        }else{
            if($customer_data['status'] == 0){
                $status = 'failed';
                $message = 'User not active';
            }else if($customer_data['approved'] == 0){
                $status = 'failed';
                $message = 'User not approved';
            }else{
                $security_code = substr(uniqid('', true), -6) ;

                if($this->customer->updateSecurity($val, $col, $security_code)){
                    $config = $this->container['config'];

                    $storename = $config->get('config_name') ?? '';
                    if(is_array($storename))
                        $storename = $storename['en'];

                    $subject = "Mobile App Forget Password Request";
                    $body = 'Your Mobile App security code is: <strong>'.$security_code.'</strong>';

                    if($col == 'email') {
                        $mail = $this->container['mail'];
                        $mail->protocol = $config->get('config_mail_protocol');
                        $mail->parameter = $config->get('config_mail_parameter');
                        $mail->hostname = $config->get('config_smtp_host');
                        $mail->username = $config->get('config_smtp_username');
                        $mail->password = $config->get('config_smtp_password');
                        $mail->port = $config->get('config_smtp_port');
                        $mail->timeout = $config->get('config_smtp_timeout');
                        $mail->setTo($val);
                        $mail->setFrom((!empty($config->get('config_smtp_username')) ? $config->get('config_smtp_username') : $config->get('config_email')));
                        $mail->setSender($storename);
                        $mail->setSubject($subject);
                        $mail->setReplyTo(
                            $config->get('config_mail_reply_to'),
                            $config->get('config_name') ? $config->get('config_name') : 'ExpandCart',
                            $config->get('config_email')
                        );
                        $mail->setHtml($body);
                        $mail->send();

                        $message = 'Email sent';
                    }else if($col == 'telephone'){
                        $smshareCommons = $this->container['SmshareCommons'];
                        $sms_to =  trim($val);

                        $smshareCommons->sendSMS($sms_to, $body, $config);
                        $message = 'SMS sent';
                    }
                }else{
                    $status = 'failed';
                    $message = 'Update security code server error';
                }
            }
        }

        return $response->withJson(['status' => $status, 'message' => $message]);
    }

    /**
     * Reset password method, change password based on a security code
     *
     * @param string $security_code
     * @param string $new_password
     * @param string $confirm_password
     *
     * @return json
     * */
    public function resetpassword(Request $request, Response $response)
    {
        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        if(!$parameters['security_code'] || !$parameters['new_password'] || !$parameters['confirm_password']){
            $status = 'failed';
            $message = 'Data missing, security_code or new_password or confirm_password';
            return $response->withJson(['status' => $status, 'message' => $message]);
        }

        if($parameters['new_password'] != $parameters['confirm_password']){
            $status = 'failed';
            $message = 'Password mismatch';
            return $response->withJson(['status' => $status, 'message' => $message]);
        }

        //$customer = (new Customer)->setContainer($this->container);

        $customer_data = $this->customer->getCustomerByCol($parameters['security_code'], 'security_code');

        if(!$customer_data){
            $status = 'failed';
            $message = 'Security code invalid';
        }else{

            $data = [
                'password'       => $parameters['new_password'],
                'customer_id'        => $customer_data['customer_id'],
                'security_code'  => $parameters['security_code']
            ];

            if($this->customer->changePassword($data)){
                $message = 'Password changed successfully.';
            }else{
                $status = 'failed';
                $message = 'Change password server error';
            }
        }

        return $response->withJson(['status' => $status, 'message' => $message]);
    }

    /**
     * generate security code to use in change password for logged in users
     *
     * @param string email/phone
     *
     * @return json
     * */
    public function getSecurityCode(Request $request, Response $response)
    {
        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        if (isset($parameters['email'])){
            $val = $parameters['email'];
            $col =  'email';
        }else if(isset($parameters['phone'])){
            $val = $parameters['phone'];
            $col =  'telephone';
        }

        $customer = (new Customer)->setContainer($this->container);

        $customer_data = $customer->getCustomerByCol($val, $col);
        if(!$customer_data){
            $status = 'failed';
            $message = 'User not found';
        }else{
            if($customer_data['status'] == 0){
                $status = 'failed';
                $message = 'User not active';
            }else if($customer_data['approved'] == 0){
                $status = 'failed';
                $message = 'User not approved';
            }else{
                $security_code = substr(uniqid('', true), -6) ;
                if($customer->updateSecurity($val, $col, $security_code)){
                    return $response->withJson(['status' => $status, 'security_code' => $security_code]);
                }else{
                    $status = 'failed';
                    $message = 'Update security code server error';
                }
            }
        }

        return $response->withJson(['status' => $status, 'message' => $message]);
    }


    public function store(Request $request, Response $response)
    {
        $inputs = $request->getParsedBody();
        $rules  = [
            "firstname"                 => "required|string",
            "lastname"                  => "required|string",
            "email"                     => "required|email",
            "telephone"                 => "required|int",
            "password"                  => "required|string",
            "fax"                       => "int",
            "customer_group_id"         => "int",
        ];
        $validator              = $this->validator($inputs, $rules);
        if (!$validator) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        return $response->withJson([
            'status'    => 'ok',
            'data'      => $this->customer->createCustomer($inputs)
        ], 200);
    }
    public function checkSecurityCode(Request $request, Response $response)
    {
        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        if(!$parameters['security_code']){
            $status = 'failed';
            $message = 'security_code missing';
            return $response->withJson(['status' => $status, 'message' => $message]);
        }

        $customer_data = $this->customer->getCustomerByCol($parameters['security_code'], 'security_code');

        if(!$customer_data){
            $status = 'failed';
            $message = 'Security code invalid';
        }else{

            $status = 'Ok';
            $message = 'Security code valid';
        }

        return $response->withJson(['status' => $status, 'message' => $message]);
    }

    public function delete(Request $request, Response $response, $args)
    {        
        if( !empty($args['id']) && is_numeric($args['id']) ){
            $this->customer->deleteCustomer($args['id']);
            return $response->withJson(['status' => $this->status[1]], 202);
           
        }else {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }
    }

}
