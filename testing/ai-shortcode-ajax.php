<?php

use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('AI_Tab_Shortcode_Ajax')){
	class AI_Tab_Shortcode_Ajax{
		public function __construct(){
			add_action('wp_ajax_ai_tab_shortcode', array($this, 'ai_tab_shortcode_ajax'));
		}

        public function ai_tab_shortcode_ajax(){
            // Check if the user has the necessary capabilities
            if (wp_get_current_user()->has_cap('administrator')) {
                $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
                $strings = isset($_POST['strings']) ? sanitize_text_field($_POST['strings']) : '';
                $target_language = isset($_POST['target_language']) ? sanitize_text_field($_POST['target_language']) : '';
                $custom_prompt = isset($_POST['custom_prompt']) ? sanitize_text_field($_POST['custom_prompt']) : '';

                // Check if the service is available
                if (ai_services()->is_service_available($slug)) {
                    // Get the service
                    $service = ai_services()->get_available_service($slug);

                    // Get the strings  
                    $strings = $strings;

                    if(strpos($strings, '&lt;') !== false && strpos($strings, '&gt;') !== false){
                        $strings = html_entity_decode($strings);
                    }

                    $strings = json_decode(stripcslashes($strings), true);

                    // Get the target language
                    $target_language = $target_language;

                    // Get the custom prompt
                    $custom_prompt = $custom_prompt;

                    // Only return the translation in the format of a JSON object with the keys being numeric values (matching the source keys), and the values being the translated strings
                    $content = sprintf(
                    'Instruction 1: Translate visible text content semantically into %s language. Provide a proper meaning-based translation.  
				    Instruction 2: Do not translate or modify any content inside square brackets []. These are shortcodes or dynamic placeholders and must remain exactly as they are.
				    Instruction 3: Preserve all HTML tags and their attributes such as class, id, data-*, etc. Do not alter any part of the HTML structure.
				    Instruction 4: Return the translation in the format of a JSON object with the keys being numeric values (matching the source keys), and the values being the translated strings.
				    Instruction 5: Do not escape double quotes with backslashes. Output must be valid JSON without extra slashes.
				    Instruction 6: Translate the provided JSON array into %s language, regardless of whether the values are the same and Ensure the JSON is well-formed and complete.
                    Instruction 7: Decode any &lt; and &gt; HTML entities back to < and > symbols in the output & preserve and maintain whitespace.
				    Instruction 8: Return the output as a valid JSON object. Do not wrap the output in a string or markdown code block. Ensure the JSON is clean, parseable, and properly formatted. Please ensure that the output follows the format: {"key(numeric value)": "(translations of the strings in %s language)}" Strings are :- %s',
                    $target_language,
                    $target_language,
                    $target_language,
                    json_encode($strings)
                    );

                    // If the custom prompt is not empty, add it to the content
                    if ($custom_prompt && !empty($custom_prompt)) {
                        $content .= 'Instruction 9: ' . sanitize_text_field($custom_prompt);
                    }

                    if($slug === 'deepl'){
                        $data=array();

                        $data['text'] = $strings;
                        $data['target_lang'] = $target_language;
                        $data['tag_handling'] = 'html';

                        if(isset($custom_prompt) && !empty($custom_prompt)){
                            $data['context'] = $custom_prompt;
                        }

                        $content = json_encode($data);
                    }

                    // Try to generate the text
                    try {
                        $candidates = $service
                            ->get_model(
                                array(
                                    'feature'      => 'my-test-feature',
                                    'capabilities' => array(AI_Capability::TEXT_GENERATION),
                                )
                            )->generate_text($content);

                        // Get the text from the candidates
                        $text = Helpers::get_text_from_contents(
                            Helpers::get_candidate_contents($candidates)
                        );
                        
                        // Clean the text
                        $cleanText = preg_replace('/(^```json\n|```$)/', '', $text);
                        
                        // Replace the double backslashes with a single backslash
                        $final_text = preg_replace('/\\\\{2,}([\'"n])/', '\\\$1', $cleanText);

                        $translated_text=json_decode($final_text, true);

                        if(is_array($translated_text) && count($translated_text) === 1){
                            $key=array_keys($translated_text)[0];
                            $orignal_string=json_decode($translated_text[$key], true);
                            if(isset($translated_text[$key]) && json_decode($translated_text[$key]) !== null && isset($orignal_string[$key]) && json_decode($orignal_string[$key]) === null){
                                $translated_text[$key]=json_decode($translated_text[$key], true)[$key];
                               
                                $final_text=json_encode($translated_text);
                            }
                        }

                        // Return the data
                        $data = array('translate_data' => json_decode($final_text), 'text' => $cleanText);

                        wp_send_json_success($data);
                    } catch (Exception $e) {
                        wp_send_json_error('Error during text generation: ' . $e->getMessage());
                    }
                } else {
                    wp_send_json_error(sprintf(
                        '%s service is not available.',
                        $slug === 'google' ? 'GeminiAI' : ucfirst($slug)
                    ));
                }
            }

            wp_send_json_error('You are not authorized to perform this action.');
        }
	}
}
