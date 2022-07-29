<?php

class ControllerCommonNewsletter extends Controller
{
	public function subscribe()
	{
		$email = $this->request->post['email'];
		$subscribed = -1; // invalid email

		//1- validation (Email)
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$subscribed = 0; //valid email but no subscription required

			//2- if this email is already a registered user, Then change newsletter column to 1
			$this->load->model('account/customer');
			$customer = $this->model_account_customer->getCustomerByEmail($email);
			if(!empty($customer)){
				if(!$customer['newsletter']){
					//update column newsletter in customer table for this customer
					$this->model_account_customer->updateCustomerNewsletterByCustomerId($customer['customer_id'], 1);
					$subscribed = 1;
				}
			}
			else {
				//3- check if email in "newsletter_subscriber" table..
				//add it if not,
				$this->load->model('newsletter/subscriber');
				$newsletter_subscriber = $this->model_newsletter_subscriber->getSubscriberByEmail($email);
				if( empty($newsletter_subscriber) ){
					$this->model_newsletter_subscriber->addNewSubscriber($email);
					$subscribed = 1;
				}
			}
		}

		//3- Call mail chimp code
		$footer_data = $this->expandish->getFooter();
		$mailChimpCode = $footer_data['NewsLetter']['fields']['MC_Code']['value'];
		if(!empty($mailChimpCode) && $mailChimpCode != '#'){
  			$json = ['redirect' => 1, 'url' => $mailChimpCode];
		}
		else{
			$json = ['redirect' => 0, 'message' => $this->getMessage($subscribed)];
		}
		$this->response->setOutput(json_encode($json));
		return;
	}

	public function getMessage($subscribed)
	{
        $this->language->load_json('common/newsletter');

		if($subscribed == -1)
			return $this->language->get('error_invalid_email_address');
		elseif($subscribed == 0)
			return $this->language->get('text_newsletter_already_exist');
		else
			return $this->language->get('text_newsletter_thanks');
	}
}
