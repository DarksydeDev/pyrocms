<?php
/**
 * @author 		Yorick Peterse - PyroCMS development team
 * @package		PyroCMS
 * @subpackage	Installer
 *
 * @since 		v0.9.6.2
 *
 * Installer controller.
 */
class Installer extends Controller 
{
	function __construct()
	{
		parent::Controller();
		
		// Load the config file that contains a list of supported servers
		$this->load->config('servers');
	}
	
	// Index function
	function index()
	{
		// The index function doesn't do that much itself, it only displays a view file with 3 buttons : Install, Upgrade and Maintenance.
		$data['page_output'] = $this->load->view('main','',TRUE);
		
		// Load the view file
		$this->load->view('global',$data);
	}
	
	// Index function
	function step_1()
	{
		// Load the model
		$this->load->model('installer_m');
		
		if($_POST)
		{									
			// Data validation
			$results = $this->installer_m->validate($_POST);
			
			if($results == TRUE)
			{
				// Store the database settings
				$this->installer_m->store_db_settings('set', $_POST);
				
				// Set the flashdata message
				$this->session->set_flashdata('message','The database settings have been stored succesfully.');
				$this->session->set_flashdata('message_type','success');

				// Redirect to the first step
				redirect('installer/step_2');
			}
			else
			{
				// Set the flashdata message
				$this->session->set_flashdata('message','The provided database settings were incorrect or could not be stored.');
				$this->session->set_flashdata('message_type','error');

				// Redirect to the first step
				redirect('installer/step_1');
			}
		}
		
		$supported_servers = $this->config->item('supported_servers');
		$data->server_options = array();
	
		foreach($supported_servers as $key => $server)
		{
			$data->server_options[$key] = $server['name'];
		}
		
		// Get the port from the session or set it to the default value when it isn't specified
		if($this->session->userdata('port'))
		{
			$data->port = $this->session->userdata('port');
		}
		else
		{
			$data->port = 3306;
		}
		
		// Load the view file
		$this->load->view('global', array(
			'page_output' => $this->load->view('step_1', $data, TRUE)
		));
	}
	
	// Install function - First step
	function step_2()
	{
		// Did the user enter the DB settings ?
		if(!$this->session->userdata('step_1_passed'))
		{	
			// Set the flashdata message
			$this->session->set_flashdata('message','Please fill in the required database settings in the form below.');
			$this->session->set_flashdata('message_type','error');
			
			// Redirect
			redirect('');
		}
			
		// Load the installer model
		$this->load->model('installer_m');
	
		// Check the PHP version
		$data->php_version = $this->installer_m->get_php_version();
	
		// Check the MySQL data
		$data->mysql->server_version = $this->installer_m->get_mysql_version('server');
		$data->mysql->client_version = $this->installer_m->get_mysql_version('client');
	
		// Check the GD data
		$data->gd_version 	= $this->installer_m->get_gd_version();
		
		// Get the server
		$selected_server = $this->session->userdata('http_server');
		$supported_servers = $this->config->item('supported_servers');
		
		$data->http_server->supported = $this->installer_m->verify_http_server($this->session->userdata('http_server'));
		$data->http_server->name = @$supported_servers[$selected_server]['name'];
		
		// Check the final results
		$data->step_passed = $this->installer_m->check_server($data);
		$this->session->set_userdata('step_2_passed', $data->step_passed);
	
		// Load the view files
		$final_data['page_output'] = $this->load->view('step_2', $data, TRUE);
		$this->load->view('global',$final_data);
	}
	
	// The second step 
	function step_3()
	{
		if(!$this->session->userdata('step_1_passed') OR !$this->session->userdata('step_2_passed'))
		{
			// Redirect the user back to step 1
			redirect('installer/step_2');
		}
		
		// Load the file helper
		$this->load->helper('file');
		
		// Get the write permissions for the folders
		$array['codeigniter/cache'] 				= $this->installer_m->is_writeable('../codeigniter/cache');
		$array['codeigniter/logs'] 					= $this->installer_m->is_writeable('../codeigniter/logs');
		$array['application/cache'] 				= $this->installer_m->is_writeable('../application/cache');
		$array['application/uploads'] 				= $this->installer_m->is_writeable('../application/uploads');
		$array['application/assets/img/galleries'] 	= $this->installer_m->is_writeable('../application/assets/img/galleries');
		$array['application/assets/img/products'] 	= $this->installer_m->is_writeable('../application/assets/img/products');
		$array['application/assets/img/staff'] 		= $this->installer_m->is_writeable('../application/assets/img/staff');
		$array['application/assets/img/suppliers'] 	= $this->installer_m->is_writeable('../application/assets/img/suppliers'); 
		$array['application/uploads/assets'] 		= $this->installer_m->is_writeable('../application/uploads/assets'); 
		$array['application/uploads/assets/cache'] 	= $this->installer_m->is_writeable('../application/uploads/assets/cache'); 
		
		// Get the write permissions for the files
		$array['application/config/config.php'] 	= $this->installer_m->is_writeable('../application/config/config.php'); 
		$array['application/config/database.php'] 	= $this->installer_m->is_writeable('../application/config/database.php'); 
		
		// If all permissions are TRUE, go ahead
		$data->step_passed = !in_array(FALSE, $array);
		$this->session->set_userdata('step_3_passed', $data->step_passed);
		
		// View variables
		$data->perm_status 	= $array;
		
		// Load the view files
		$final_data['page_output']	= $this->load->view('step_3', $data, TRUE);
		$this->load->view('global', $final_data); 
	}
	
	// The third step
	function step_4()
	{
		if(!$this->session->userdata('step_1_passed') OR !$this->session->userdata('step_2_passed') OR !$this->session->userdata('step_3_passed'))
		{
			// Redirect the user back to step 2
			redirect('installer/step_2');
		}
		
		// Check to see if the user submitted the installation form
		if($_POST)
		{
			// Only install PyroCMS if the provided data is correct
			if($this->installer_m->validate() == TRUE)
			{
				// Install the system and display the results
				$install_results = $this->installer_m->install($_POST);
				var_dump($install_results);
				exit;
				// Validate the results and create a flashdata message
				if($install_results['status'] == TRUE)
				{
					// Show an error message
					$this->session->set_flashdata('message', $install_results['message']);
					$this->session->set_flashdata('message_type','success');

					// Redirect
					redirect('installer/complete');
				}
				else
				{
					// Show an error message
					$this->session->set_flashdata('message', $install_results['message']);
					$this->session->set_flashdata('message_type','error');

					// Redirect
					redirect('installer/step_4');
				}					
			}
			else
			{
				// Show an error message
				$this->session->set_flashdata('message','The installer could not connect to the MySQL server, be sure to enter the correct information.');
				$this->session->set_flashdata('message_type','error');
				
				// Redirect
				redirect('installer/step_4');
			}
		}
		
		// Load the view files
		$final_data['page_output'] = $this->load->view('step_4','', TRUE);
		$this->load->view('global', $final_data); 
	}
	
	// All done
	function complete()
	{
		$data['admin_url'] = 'http://'.$this->input->server('SERVER_NAME').preg_replace('/installer\/index.php$/', 'index.php/admin', $this->input->server('SCRIPT_NAME'));

		// Load the view files
		$data['page_output'] = $this->load->view('complete',$data, TRUE);
		$this->load->view('global',$data); 
	}
}
?>
