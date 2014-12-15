<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Install_analytics extends Migration {

	public function up()
	{
		$prefix = $this->db->dbprefix;

		$sql[] = "CREATE TABLE IF NOT EXISTS `ga_config` (
			  `ga_clientID` varchar(255) NOT NULL,
			  `ga_svc_acc_name` varchar(255) NOT NULL,
			  `ga_p12_key` varchar(255) NOT NULL,
			  `ga_profileID` int(10) NOT NULL
			);";

		$fields = array(
			'ga_clientID' => array(
				'type' => 'varchar',
				'constraint' => 255
			),
			'ga_svc_acc_name' => array(
				'type' => 'varchar',
				'constraint' => 255
			),
			'ga_p12_key' => array(
				'type' => 'varchar',
				'constraint' => 255
			),
			'ga_profileID' => array(
				'type' => 'int',
				'constraint' => 10
			),
		);

		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('ga_config');

	}

	//--------------------------------------------------------------------

	public function down()
	{
		$prefix = $this->db->dbprefix;

		$this->dbforge->drop_table('ga_config');

	}

	//--------------------------------------------------------------------

}