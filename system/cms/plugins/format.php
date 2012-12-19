<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Format Plugin
 *
 * Various text formatting functions.
 *
 * @author  PyroCMS Dev Team
 * @package PyroCMS\Core\Plugins
 */
class Plugin_format extends Plugin
{

	public $version = '1.0.0';
	public $name = array(
		'en' => 'Format',
	);
	public $description = array(
		'en' => 'Format strings, including Markdown and Textile.',
		'el' => 'Μορφοποίηση κειμενοσειρών, συμπεριλαμβανομένων των Markdown και Textile.',
		'fr' => 'Formatter des chaînes de caractères, incluant Markdown et Textile.'
	);

	/**
	 * Returns a PluginDoc array that PyroCMS uses 
	 * to build the reference in the admin panel
	 *
	 * All options are listed here but refer 
	 * to the Blog plugin for a larger example
	 *
	 * @todo fill the  array with details about this plugin, then uncomment the return value.
	 *
	 * @return array
	 */
	public function _self_doc()
	{
		$info = array(
			'your_method' => array(// the name of the method you are documenting
				'description' => array(// a single sentence to explain the purpose of this method
					'en' => 'Displays some data from some module.'
				),
				'single' => true,// will it work as a single tag?
				'double' => false,// how about as a double tag?
				'variables' => '',// list all variables available inside the double tag. Separate them|like|this
				'attributes' => array(
					'order-dir' => array(// this is the order-dir="asc" attribute
						'type' => 'flag',// Can be: slug, number, flag, text, array, any.
						'flags' => 'asc|desc|random',// flags are predefined values like this.
						'default' => 'asc',// attribute defaults to this if no value is given
						'required' => false,// is this attribute required?
					),
					'limit' => array(
						'type' => 'number',
						'flags' => '',
						'default' => '20',
						'required' => false,
					),
				),
			),// end first method
		);
	
		//return $info;
		return array();
	}

	/**
	 * Markdown
	 *
	 * Takes content and formats it with the Markdown Library.
	 *
	 * Usage:
	 * {{ format:markdown }}
	 *   Formatted **text**
	 * {{ /format:markdown }}
	 *
	 * Outputs: <p>Formatted <strong>text</strong></p>
	 *
	 * @return string The HTML generated by the Markdown Library.
	 */
	public function markdown()
	{
		$this->load->helper('markdown');

		$content = $this->attribute('content', $this->content());

		return parse_markdown(trim($content));
	}

	/**
	 * Textile
	 *
	 * Takes content and formats it with the Textile Library.
	 *
	 * Usage:
	 * {{ format:textile }}
	 *   Formatted _text_
	 * {{ /format:textile }}
	 *
	 * Outputs: <p>Formatted <em>text</em></p>
	 *
	 * @return string The HTML generated by the Textile Library.
	 */
	public function textile()
	{
		$this->load->library('textile');

		$content = $this->attribute('content', $this->content());

		return $this->textile->TextileThis(trim($content));
	}

	/**
	 * URL Title
	 *
	 * Converts a string using the `url_title()` URL Helper function
	 *
	 * Usage:
	 * {{ format:url_title string="Introducing New Administrators" separator="dash" lowercase="true" }}
	 *
	 * Outputs: "introducing-new-administrators"
	 *
	 * @return string Formatted with the `url_title` helper function
	 */
	public function url_title()
	{
		$this->load->helper('url');

		$attrs = $this->attributes();

		// fix 'true' or 'false' to real bools.
		if (count($attrs) > 2)
		{
			$bool = array_slice($attrs, 2);
			array_splice($attrs, 2, 1, (bool) $bool);
		}

		return call_user_func_array('url_title', $attrs);
	}

}