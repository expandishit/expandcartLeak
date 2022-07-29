<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Respect\Validation\Validator as vld;

use Api\Models\User;

class LoginController extends Controller
{

    /**
     * @var
     */
    private $config;

    /**
     * DomainController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->config = $container['config'];
    }

    public function login(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        $user = (new User)->setContainer($this->container);

        $data = [
            'username' =>   $parameters['username'],
            'password' =>   $parameters['password']
        ];

        $user_data = $user->logIn($data);
        $user_data['is_subscribed'] = PRODUCTID == 3 ? 0 : 1; // If productid == 3, that means user is not subscribed, else user is subscribed
        $user_data['store_owner_name'] = BILLING_DETAILS_NAME;
        $user_data['login_init'] = unserialize($this->config->get('login'))['token'];
        if($user_data)
        return $response->withJson(['status' => 'ok', 'data' => $user_data]);

        return $response->withJson(['status' => 'error', 'error_description' => 'User not found']);
    }

    /**
     * Forget password method, send email with security code to use in reset password endpoint
     *
     * @param string $email
     *
     * @return json
     * */
    public function forgetpassword(Request $request, Response $response)
    {
        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        $user = (new User)->setContainer($this->container);

        $user_data = $user->getUserByCol($parameters['email'], 'email');
        if(!$user_data){
            $status = 'failed';
            $message = 'User not found';
        }

        if($user_data['status'] == 0){
            $status = 'failed';
            $message = 'User not active';
        }else{
            $security_code = substr(uniqid('', true), -6) ;
            if($user->updateSecurity($parameters['email'], $security_code)){

                $config = $this->container['config'];

                $storename = $config->get('config_name') ?? '';
                if(is_array($storename))
                    $storename = $storename['en'];

                $subject = "Mobile App Forget Password Request";
                $body = 'Your Mobile App security code is: <strong>'.$security_code.'</strong>';

                $mail = $this->container['mail'];
                $mail->protocol = $config->get('config_mail_protocol');
                $mail->parameter = $config->get('config_mail_parameter');
                $mail->hostname = $config->get('config_smtp_host');
                $mail->username = $config->get('config_smtp_username');
                $mail->password = $config->get('config_smtp_password');
                $mail->port = $config->get('config_smtp_port');
                $mail->timeout = $config->get('config_smtp_timeout');
                $mail->setTo($parameters['email']);
                $mail->setFrom((!empty($config->get('config_smtp_username')) ? $config->get('config_smtp_username') :  $config->get('config_email')));
                $mail->setSender($storename);
                $mail->setSubject($subject);
                $mail->setHtml($body);
                $mail->send();

                $message = 'Email sent';
            }else{
                $status = 'failed';
                $message = 'Update security code server error';
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

        $user = (new User)->setContainer($this->container);

        $user_data = $user->getUserByCol($parameters['security_code'], 'security_code');
        if(!$user_data){
            $status = 'failed';
            $message = 'Security code invalid';
        }else{

            $data = [
                'password'       => $parameters['new_password'],
                'user_id'        => $user_data['user_id'],
                'security_code'  => $parameters['security_code']
            ];

            if($user->changePassword($data)){
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
     * @param string $email
     *
     * @return json
     * */
    public function getSecurityCode(Request $request, Response $response)
    {
        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        $user = (new User)->setContainer($this->container);

        $user_data = $user->getUserByCol($parameters['email'], 'email');
        if(!$user_data){
            $status = 'failed';
            $message = 'User not found';
        }

        if($user_data['status'] == 0){
            $status = 'failed';
            $message = 'User not active';
        }else{
            $security_code = substr(uniqid('', true), -6) ;
            if($user->updateSecurity($parameters['email'], $security_code)){
                return $response->withJson(['status' => $status, 'security_code' => $security_code]);
            }else{
                $status = 'failed';
                $message = 'Update security code server error';
            }
        }

        return $response->withJson(['status' => $status, 'message' => $message]);
    }

    /**
     * generate code to use in change password for logged in users
     *
     * @param string $email
     *
     * @return json
     * */
    public function getCode(Request $request, Response $response)
    {
        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        $user = (new User)->setContainer($this->container);

        $user_data = $user->getUserByCol($parameters['email'], 'email');
        if(!$user_data){
            $status = 'failed';
            $message = 'User not found';
        }

        if($user_data['status'] == 0){
            $status = 'failed';
            $message = 'User not active';
        }else{
            $code = sha1(uniqid(mt_rand(), true));
            if($user->updateCode($parameters['email'], $code)){
                return $response->withJson(['status' => $status, 'code' => $code]);
            }else{
                $status = 'failed';
                $message = 'Update code server error';
            }
        }

        return $response->withJson(['status' => $status, 'message' => $message]);
    }

  /**
     * check user email exists 
     *
     * @param string $email
     *
     * @return json
     * */
    public function emailexists(Request $request, Response $response)
    {
        $status = 'ok';
        $message = '';

        $parameters = $request->getParsedBody();

        $user = (new User)->setContainer($this->container);

        $user_data = $user->getUserByCol($parameters['email'], 'email');
        if(!$user_data){
            $status = 'failed';
            $message = 'User not found';
        }

        if($user_data['status'] == 0){
            $status = 'failed';
            $message = 'User not active';
        }else{
           
                return $response->withJson(['status' => $status, 'user_data' => $user_data]);
            
        }

        return $response->withJson(['status' => $status, 'message' => $message]);
    }

}