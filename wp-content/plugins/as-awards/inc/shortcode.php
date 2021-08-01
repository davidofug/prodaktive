<?php
Namespace AWS\INC;

if(!defined('ABSPATH') ) wp_die();

class shortCode {
    
    public function __construct() {
        add_shortcode( 'awards-search-form', [$this,'renderForm'] );
    }
    
    public function renderForm() {
      	ob_start();
    ?>
        <div class="awards-form">
      	    <input type="text" placeholder="Certificate code" class="form-field" id="certificate-code" /> -OR-
    	    <input type="text" placeholder="Student code" class="form-field" id="student-code" />
    	    <button id="search" >Go</button>  
        </div>
	    
	    <div id="output">
			<p>
				Certificate details will be loaded here.
			</p>
	    </div>

    <?php
    	return ob_get_clean();      
    }

}