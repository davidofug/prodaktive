<?php
if(!is_user_logged_in())
	wp_safe_redirect( wp_login_url() );