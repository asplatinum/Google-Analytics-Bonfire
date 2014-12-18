<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * seo controller
 */
class seo extends Admin_Controller
{

	//--------------------------------------------------------------------


	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->auth->restrict('Analytics.Seo.View');
		$this->lang->load('analytics');

		require_once('../application/third_party/GoogleAnalytics/Client.php');
		require_once('../application/third_party/GoogleAnalytics/Service.php');

		Template::set_block('sub_nav', 'seo/_sub_nav');
		//Assets::add_module_js('analytics', 'analytics.js');
		
		$this->GA_config = $this->GA_config();

		$this->analytics = new Google_Service_Analytics($this->GA_login());
	}

	//--------------------------------------------------------------------


	/**
	 * Displays a list of form data.
	 *
	 * @return void
	 */
	public function index($startDate_offset=1, $endDate=false)
	{

		if($this->GA_config) {
		
			$startDate = date('Y-m-d', strtotime('-'.$startDate_offset.' month')); 
			$endDate = ($endDate ? strtotime($endDate) : date('Y-m-d'));

			$data['GA_users'] = $this->GA_users($this->GA_config()->GA_profileId, $startDate, $endDate);;
			$data['GA_browsers'] = $this->GA_browsers($this->GA_config()->GA_profileId, $startDate, $endDate);
			$data['GA_referrers'] = $this->GA_referrers($this->GA_config()->GA_profileId, $startDate, $endDate);
			$data['GA_visitors_day'] = $this->GA_visitors_day($this->GA_config()->GA_profileId, $startDate, $endDate);

		} else {
			$data['GA_data'] = null;
		}

				Template::set('GA_data', $data);
				Template::set('toolbar_title', 'Analytics');
				Template::render();
			}

	//--------------------------------------------------------------------

	
	private function GA_config() {
		$query = $this->db->get('bf_ga_config');
			if ($query->num_rows() > 0) {
				$row = $query->row(); 
				$data = (object) array('GA_ClientID' => $row->ga_clientID, 'GA_service_account_name' => $row->ga_svc_acc_name, 
					'GA_key_file_location' => APPPATH . 'third_party/GoogleAnalytics/' . $row->ga_p12_key, 
					'GA_profileId' => 'ga:' . $row->ga_profileID);
				return $data;
			} else {
				return false;
			}
		
	}

	private function GA_login() {
		$client = new Google_Client();
		$client->setApplicationName("This_Was_A_Test");
		$service = new Google_Service_Analytics($client);

		if ($this->session->userdata('service_token')) {
			$client->setAccessToken($this->session->userdata('service_token'));
		}

		$key = file_get_contents($this->GA_config->GA_key_file_location);

		$cred = new Google_Auth_AssertionCredentials(
		$this->GA_config()->GA_service_account_name,
			array(
				'https://www.googleapis.com/auth/analytics'),$key,'notasecret'
				);

		$client->setAssertionCredentials($cred);
		if($client->getAuth()->isAccessTokenExpired()) {
			$client->getAuth()->refreshTokenWithAssertion($cred);
		}

		$this->session->set_userdata('service_token', $client->getAccessToken());

		return $client;
	}



	private function GA_users($profileID, $startDate, $endDate) {
		$metrics = "ga:sessions";
		$optParams = array("dimensions" => "ga:userType");
		$data = $this->analytics->data_ga->get($this->GA_config()->GA_profileId, $startDate, $endDate, $metrics, $optParams);

		$res = [
            'items' => isset($data['rows']) ? $data['rows'] : [],
            'columnHeaders' => $data['columnHeaders'],
            'totalResults'  => $data['totalResults']
        ];
        //var_dump($res);
		return $data;
	}

	private function GA_browsers($profileID, $startDate, $endDate) {
		$metrics = "ga:sessions";
		$optParams = array("dimensions" => "ga:browser");
		$data = $this->analytics->data_ga->get($this->GA_config()->GA_profileId, $startDate, $endDate, $metrics, $optParams);

		return $data;
	}

	private function GA_referrers($profileID, $startDate, $endDate) {
		$metrics = "ga:sessions";
		$optParams = array("dimensions" => "ga:source, ga:referralPath", "sort" => "-ga:sessions");
		$data = $this->analytics->data_ga->get($this->GA_config()->GA_profileId, $startDate, $endDate, $metrics, $optParams);

		return $data;
	}

	private function GA_visitors_day($profileID, $startDate, $endDate) {
		$metrics = "ga:pageviews, ga:sessions";
		$optParams = array("dimensions" => "ga:day", "sort" => "ga:day");
		$data = $this->analytics->data_ga->get($this->GA_config()->GA_profileId, $startDate, $endDate, $metrics, $optParams);

		return $data;
	}

	
	

}