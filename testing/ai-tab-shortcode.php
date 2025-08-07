<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'ai-shortcode-ajax.php';

class AI_Tab_Shortcode {

	public function __construct() {
		add_shortcode( 'ai-tab', array( $this, 'render_tab' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		new AI_Tab_Shortcode_Ajax();
	}

	public function render_tab() {
        wp_enqueue_script( 'ai-tab-shortcode' );
		wp_enqueue_style( 'ai-tab-shortcode' );

		$ai_serivces=ai_services();

		$register_service=$ai_serivces->get_registered_service_slugs();
		$service_html='';
		$service_count=0;

		foreach ($register_service as $service) {
			
			if($ai_serivces->is_service_available($service)){
				$service_metadata=$ai_serivces->get_service_metadata($service);

				$name=$service_metadata->get_name();
				$slug=$service_metadata->get_slug();

				$service_html.='<option value="'.$slug.'">'.$name.'</option>';

				$service_count++;
			}
		}

		$html= '<div class="ai-content-generator">
  <h2>AI Content Generator</h2>
  <div class="ai-form">
    <label for="ai-prompt">Your Prompt</label>
    <textarea id="ai-prompt" placeholder="Ask me anything..."></textarea>

    <label for="ai-service">Select AI Service</label>
    <select id="ai-service">'.$service_html.'</select>
    <button id="ai-generate-button">Generate</button>
  </div>

  <div class="ai-output">
    <h3>Generated Content</h3>
    <div id="ai-response">Your content will appear here...</div>
  </div>
</div>';

		if($service_count<1){
			$html="<h2>No services available</h2>";
		}

		return $html;
	}

	public function enqueue_scripts() {
		wp_register_script( 'ai-tab-shortcode', plugin_dir_url( __FILE__ ) . 'assets/js/index.js', array( 'jquery' ), '1.0.0', true );
		
		wp_localize_script('ai-tab-shortcode', 'AI_Tab_Shortcode', array('ajaxurl' => admin_url('admin-ajax.php'), 'action' => 'ai_tab_shortcode'));


		wp_register_style( 'ai-tab-shortcode', plugin_dir_url( __FILE__ ) . 'assets/css/index.css', array(), '1.0.0', 'all' );
	}
}

new AI_Tab_Shortcode();

