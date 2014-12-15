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

	}

	//--------------------------------------------------------------------


	/**
	 * Displays a list of form data.
	 *
	 * @return void
	 */
	public function index($startDate_offset=30, $endDate=false)
	{


		$GA_config = $this->ga_config();
		
		$client = new Google_Client();
		$client->setApplicationName("This_Was_A_Test");
		$service = new Google_Service_Analytics($client);

		if ($this->session->userdata('service_token')) {
			$client->setAccessToken($this->session->userdata('service_token'));
		}

		$key = file_get_contents($GA_config['GA_key_file_location']);

			$cred = new Google_Auth_AssertionCredentials(
			    $GA_config['GA_service_account_name'],
			    array(
			        'https://www.googleapis.com/auth/analytics',
			    ),
			    $key,
			    'notasecret'
			);

		$client->setAssertionCredentials($cred);
		if($client->getAuth()->isAccessTokenExpired()) {
		  $client->getAuth()->refreshTokenWithAssertion($cred);
		}

		$this->session->set_userdata('service_token', $client->getAccessToken());

		$analytics = new Google_Service_Analytics($client);

		$startDate = date('Y-m-d', strtotime('-'.$startDate_offset.' days')); 
		$endDate = ($endDate ? strtotime($endDate) : date('Y-m-d'));


		$metrics = "ga:sessions";
		$optParams = array("dimensions" => "ga:userType");
		$GA_users = $analytics->data_ga->get($GA_config['GA_profileId'], $startDate, $endDate, $metrics, $optParams);


		$optParams = array("dimensions" => "ga:browser");
		$GA_browsers = $analytics->data_ga->get($GA_config['GA_profileId'], $startDate, $endDate, $metrics, $optParams);


		$metrics = "ga:pageviews, ga:sessions";
		$optParams = array("dimensions" => "ga:country");
		$GA_referrers = $analytics->data_ga->get($GA_config['GA_profileId'], $startDate, $endDate, $metrics, $optParams);

		$optParams = array("dimensions" => "ga:day", "sort" => "ga:day");
		$GA_visitors_day = $analytics->data_ga->get($GA_config['GA_profileId'], $startDate, $endDate, $metrics, $optParams);

		$data['GA_users'] = $GA_users;
		$data['GA_browsers'] = $GA_browsers;
		$data['GA_referrers'] = $GA_referrers;
		$data['GA_visitors_day'] = $GA_visitors_day;

				Template::set('GA_data', $data);
				Template::set('toolbar_title', 'Analytics');
				Template::render();
			}

	//--------------------------------------------------------------------

	
	private function ga_config() {
		$query = $this->db->get('bf_ga_config');
		$row = $query->row(); 

		$data['GA_client_id'] = $row->ga_clientID; // Not needed, I think?!???.
		$data['GA_service_account_name'] = $row->ga_svc_acc_name;
		$data['GA_key_file_location'] = APPPATH . 'third_party/GoogleAnalytics/' . $row->ga_p12_key;
		$data['GA_profileId'] = 'ga:' . $row->ga_profileID;

		return $data;
	}
	

}