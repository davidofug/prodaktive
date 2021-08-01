<?php
Namespace AWS\INC;

if( !defined('ABSPATH') ) wp_die('Stop cheating!' );

class Base {
    
    public $PLUGIN_URL;
    
    public function __construct() {
		date_default_timezone_set('Africa/Kampala');
        $this->PLUGIN_URL = plugin_dir_url( dirname(__FILE__) );
        \add_action( 'edit_form_after_title', [$this,'setPostTitle'] );
        \add_action( 'wp_enqueue_scripts', [$this,'enqueueAssets'] );
        \add_action( 'wp_ajax_nopriv_get_award', [$this,'findAward']);
		\add_action( 'admin_menu', [$this, 'removeAdminMenuItems'], 999 );
		\add_action( 'admin_bar_menu', [$this, 'removeToolbarNodes'], 999);
		\add_action( 'admin_head', [$this, 'customCSS'] );
		//\add_filter( 'default_title', [$this, 'setJobTitle'], 10, 2 );
		\add_filter( 'redirect_post_location', [$this, 'redirectIfSaved'] );
		\add_action('admin_notices', [$this, 'jobNoticeMessages']);
		\add_action( 'admin_menu', [$this, 'attachCustomMenuItems'], 999 );
		\add_action( 'add_meta_boxes_jobdone', [$this, 'movePostMetaboxes'] );

    }
    
    public function enqueueAssets() {
		wp_enqueue_style( 'aws_css', $this->PLUGIN_URL . 'assets/css/style.css', '', '1.0.0');
        wp_register_script( 'aws_js', $this->PLUGIN_URL . 'assets/js/script.js', array('jquery'), '1.0.0', true );
        wp_localize_script( 'aws_js', 'awsAjax', array( 'url' => admin_url( 'admin-ajax.php' )));        
		wp_enqueue_script( 'aws_js' );

    }
	
	public function movePostMetaboxes( $post ) {
		global $wp_meta_boxes;

		remove_meta_box( 'submitdiv', 'jobdone', 'side' );
		add_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', 'jobdone', 'normal', 'low' );
	}
	
    public function generateAwardCode() {
        return strtoupper( substr( md5( mt_rand() ), 1, 8 ) );
    }
    
    public function setPostTitle( $post ) {
        $postType = get_post_type();
        $allowedPostTypes = ['award','trainee','trainer','pool'];
        
        if (!in_array($postType,$allowedPostTypes) AND $post->post_title != 'auto-draft' ) return;
           
       $awardCode = $this->generateAwardCode();
       
       if(strlen( $awardCode ) == 8 ) :
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    if( $( '#title' ).val() == '' && $( '#title' ).val().length <= 0 ) {
                        $( '#title' ).val( '<?php echo $awardCode; ?>' ).prop( 'readonly', true );
                    }
                });
            </script>
        <?php
       endif;
    }
    
    public function findAward() {
    
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) :
            
            if( isset( $_GET['CERTIFICATE_CODE'] ) OR isset( $_GET['STUDENT_CODE'] ) ) :
                
    		    $certificateCode = !empty( $_GET['CERTIFICATE_CODE'] ) ? \sanitize_text_field( $_GET['CERTIFICATE_CODE'] ) : null ;
    		    $studentCode = !empty( $_GET['STUDENT_CODE'] ) ? \sanitize_text_field( $_GET['STUDENT_CODE'] ) : null ;
    		    
    		    if( $certificateCode !== null ) :
    		        
    		        $award = get_page_by_title( $certificateCode, OBJECT, 'award' );
    		        
    		        if( $award->ID ) :
		
						$traineeID = (int) get_post_meta( $award->ID, 'trainee', true );
						$trainee = get_post( $traineeID );
		
						$firstName = get_post_meta( $trainee->ID, 'first_name', true );
						$lastName = get_post_meta( $trainee->ID, 'last_name', true );
						$attachmentID = get_post_meta( $trainee->ID, 'photo', true );
		
						$photo = wp_get_attachment_image_src( $attachmentID, 'thumbnail' );
		
						$awardType = get_post_meta( $award->ID, 'award_type', true );
		
						$awardIn = get_post_meta($award->ID, 'awarded_in', true );

                		echo \json_encode([
							'result' => 'successful',
        					'award' => ( object ) [
								'code' => $award->post_title,
								'in' => $awardIn,
								'type' => $awardType,
								'trainee' => ( object ) [
									'photo' => $photo[0],
									'code' => $trainee->post_title,
									'name' => (object) [
										'first' => $firstName,
										'last' => $lastName,
									],
								]
							]
        				]);
		
        			else:
            			echo \json_encode([
            			    'result' => 'not-found',
        					'certificate_code' => $certificateCode,
        				]);
        				
    				endif;
    				
    			elseif($studentCode !== null ) :
		
    			    $student = get_page_by_title( $studentCode, OBJECT, 'trainee' );
		
					if($student->ID) :
		
						$firstName = get_post_meta( $student->ID, 'first_name', true );
						$lastName = get_post_meta( $student->ID, 'last_name', true );
						$attachmentID = get_post_meta( $student->ID, 'photo', true );
						$photo = wp_get_attachment_image_src( $attachmentID, 'thumbnail' )[0];
		
						$awards = get_posts([
							'post_type' => 'award',
							'post_status' => 'publish',
							'posts_per_page' => 10,
							'meta_query' => [
								[
									'key' => 'trainee',
									'value' => $student->ID
								]
							]
						]);

						if(sizeof($awards)):
		
							$studentAwards = [];
		
							foreach( $awards as $award) :
		
								$studentAwards[] = (object)[
									'code' => $award->post_title,
									'in' => get_post_meta( $award->ID, 'awarded_in', true ),
									'type' => get_post_meta( $award->ID, 'award_type', true )
								];
	
							endforeach;
		
							echo \json_encode([
								'result' => 'successful',
								'trainee' => ( object ) [
									'photo' => $photo,
									'code' => $studentCode,
									'name' => (object) [
										'first' => $firstName,
										'last' => $lastName,
									],
								],
								'awards' => $studentAwards
							]);
		
						else:
							echo \json_encode([
								'result' => 'not-found',
								'student_id' => $studentCode,
							]);

						endif;
					else:
						echo \json_encode([
							'result' => 'Failure ',
							'msg' => 'Student not found'
						]);		
					endif;
    				
                else:
                    echo \json_encode([
                        'result' => 'unexpected request ',
    				]);
    			endif;
				
			else:
			    
    			echo \json_encode([
                    'result' => 'unexpected payload ',
                    'payload_was' => $_GET
				]);	
			   
			endif;
		else:
    		echo \json_encode([
                'result' => 'unexpected request method ',
			]);	
			
	    endif;

		
		wp_die();
    }
	
	public function removeAdminMenuItems() {
		$user = wp_get_current_user();
		/*global $submenu;
		
		echo '<pre>';
			print_r($submenu);
		echo '</pre>'; */
		if( array_intersect(['trainee','employee', 'intern'], $user->roles)) :
			remove_menu_page('edit.php?post_type=page');
			remove_menu_page('edit.php?post_type=pool');
			remove_menu_page('edit.php?post_type=trainee');
			remove_menu_page('edit.php?post_type=trainer');
			remove_menu_page('edit.php?post_type=award');			
			remove_menu_page('edit.php?post_type=elementor_library');
			remove_menu_page('edit.php');
			remove_menu_page('edit-comments.php');
			remove_menu_page('tools.php');	
		endif;
	}
	
	public function removeToolbarNodes( $wp_admin_bar ) {
		$user = wp_get_current_user();
		if( array_intersect(['trainee','employee', 'intern'], $user->roles)) :
			$wp_admin_bar->remove_node('new-content');
			$wp_admin_bar->remove_node('comments');
			$wp_admin_bar->remove_node('wp-logo');
		endif;
	}
	
	public function setJobTitle( $post_title, $post) {
		return date('Y-m-d H:i:s');
	}
	 
	public function setJobStatus( $post_id ) {

		$post_type = get_post_type( $post_id );

		if ( 'jobdone' == $post_type ) :
		
			$args = [
				'post_status' => 'publish',
			];
		
			wp_update_post( $args );
		
		endif;
	}
	
	public function redirectIfSaved( $location ) {

		if ( 'jobdone' == get_post_type() ) {

			if ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) )
				return admin_url( "edit.php?post_type=jobdone&status=success" );
		} 

		return $location;
	}
	
	public function jobNoticeMessages() {

		global $pagenow;

		if (( $pagenow == 'edit.php' ) && ($_GET['post_type'] == 'jobdone') && ($_GET['status'] == 'success') ) {
			echo '<div class="updated custom-notice"><p>Task added successfully!</p></div>';
		}
	}
	
	public function customCSS(){
		$user = wp_get_current_user();
		if( array_intersect(['trainee','employee', 'intern','stakeholder'], $user->roles)) : ?>
			<style type="text/css">
				#submitdiv .postbox-header,
				#minor-publishing-actions,
				#misc-publishing-actions,
				/*div#post-body-content,*/
				#screen-meta-links,
				#acf-group_60095b47116ad .postbox-header,
				#submitdiv #delete-action {
					display: none;
				}
				
				#submitdiv.postbox,
				#submitdiv #major-publishing-actions {
					border: none;
					box-shadow: none;
					padding:0;
					background: #f1f1f1;
				}
			</style>

			<script>
				jQuery(document).ready(function($) {
					$( '#post-body' ).removeClass('columns-2').addClass('columns-1');
                });
			</script>

	<?php endif; }
	
	public function attachCustomMenuItems() {
	  add_menu_page( 'Attendance', 'Attendance', 'submit_attendance', 'attendance.php', [$this, 'attendanceView'], 'dashicons-calendar', 2 );
	add_menu_page( 'Assignments', 'Assignments', 'manage_assignments', 'assignment.php', [$this, 'assignmentViews'], 'dashicons-calendar', 2 );
	add_menu_page( 'Conduct', 'Conduct', 'submit_attendance', 'conduct.php', [$this, 'codeOfConductView'], 'dashicons-calendar', 2 );
	}
	
	public function attendanceView() {
		
		$user = wp_get_current_user();
		
		$today = getdate();
		
		$args = [
			'post_type' => 'attendance',
			'posts_per_page' => 1,
			'post_status' => 'publish',
			'date_query' => [
				[
					'year' => $today['year'],
					'month' => $today['mon'],
					'day' => $today['mday']
				]
			],
			'author' => $user->ID
		];
		
		$attendance = get_posts($args);

		if(isset($_POST['checkin'])):
		
			$comments = sanitize_text_field($_POST['checkincoments']);
			
			$checkin_id = wp_insert_post([
				'post_type' => 'attendance',
				'post_status' => 'publish',
				'post_title' => wp_strip_all_tags( date('Y-m-d:H:i:s') ),
				'post_author' => $user->ID,
				'post_content' => $comments
			], true );

			if( !is_wp_error( $checkin_id ) ):
				update_post_meta($checkin_id, 'checkin', date( 'Y-m-d H:i:s' ) );
				echo '<div class="notice inline notice-success"><p>You have checked in</p></div>';
			else:
				echo $checkin_id->get_error_message();
				echo '<div class="notice inline notice-error"><p>System failed to check in. Try Again!</p></div>';
			endif;
				
		elseif( isset($_POST['checkout']) ) :
		
			$comments = sanitize_text_field( $_POST['checkoutcomments'] );
		
			update_post_meta( $attendance[0]->ID, 'checkoutcomments', $comments );
			update_post_meta( $attendance[0]->ID, 'checkout', date('Y-m-d H:i:s' ) );
		
			echo '<div class="notice inline notice-success"><p>Checked out successfully!</p></div>';
			
		else:

			if( sizeof($attendance) <= 0 ) : ?>
				<form action="<?= admin_url('admin.php?page=attendance.php'); ?>" method="post">
					<div>
						<h1>Check in</h1>
						<div>
							<label for="check-in-comments">Comments (optional)</label><br/>
							<textarea name="checkincomments" id="check-in-comments" placeholder="Enter comments..."></textarea>
						</div>
						<input type="hidden" name="checkin" value="1" />
						<button id="check-in-btn" name="checkinbtn" class="button button-primary">Check in</button>
					</div>
				</form>
			<?php endif;

			if( sizeof($attendance) == 1 ) : ?>

				<p><b>Checked in </b> <?= $attendance[0]->post_date; ?> </p>
				<?php
					$checkedOut = ( string ) get_post_meta( $attendance[0]->ID, 'checkout', true);
				
					if(!$checkedOut) : ?>
						<form action="<?= admin_url('admin.php?page=attendance.php'); ?>" method="post">
							<div>
								<h1>Check out</h1>
								<div>
									<label for="check-out-comments">Comments (optional)</label><br/>
									<textarea placeholder="Enter comments..." name="checkoutcomments" id="check-out-comments"></textarea>	
								</div>
								<input name="checkout" value="1" type="hidden" />
								<button id="check-out-btn" class="button button-primary">Check out</button>
							</div>
						</form>
			<?php else : ?>
				<p><b>Checked out</b> <?= $checkedOut; ?></p>
			<?php endif;
			endif;
		endif;
	}
	
	public function assignmentViews() { ?>
		<div id="assignment_form_wrapper">
			<h1>Add Assignment</h1>
			<form action="<?= admin_url('admin.php?page=assignment.php'); ?>" method="post" enctype="multipart/form-data" >
				<div>
					<label>Title</label><br/>
					<input type="text" name="assigment_title" id="assingment_title" placeholder="Enter title..." />
				</div>
				<div>
					<label>Category</label><br/>
					<select name="assignment_category" id="assignment_category">
						<option value="">- Choose -</option>
					</select>
				</div>
				<div>
					<label>Description</label><br/>
					<textarea name="assigment_desc" id="assingment_desc" placeholder="Enter description..."></textarea>
				</div>
				<div>
					<input type="file" value="attachments" name="assignment_attachments" id="assignment_attachment" />
				</div>
				<div>
					<label>Assign to</label><br/>
					<select name="assignment_to" id="assignment_to">
						<option value="">- Choose -</option>
						<?php
							$members = get_users(['role__in' => ['trainee','stakeholder']]);
							foreach($members as $member): ?>
						
							<option value="<?= $member->ID; ?>">
								<?= $member->display_name; ?> (<?= ucfirst(implode(',',$member->roles)); ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<button type="submit" id="submit_assignment" class="button button-primary" name="submit_assignment">
						Submit
					</button>
				</div>
			</form>
		</div>
		<div>
			<?php
				$assignments = get_posts([
					'post_type' => 'assignment',
					'post_status' => 'publish',
					'posts_per_page' => 10,
				]);
				
				if( sizeof($assignments ) ) :
					foreach( $assignments as $assignment) :
			?>
				<div>
					<p><?= $assignment->post_title; ?></p>
					<div>
						<?= $assignment->post_content; ?>
					</div>
					<p>
						<?php 
							$userID = get_post_meta($assignment->ID, 'assignto', true);
							$user = get_user($userID);
							echo $user->display_name;
						?>
					</p>
				</div>
			<?php 
				endforeach;
			endif;
			?>
		</div>
	<?php }
	
	public function codeOfConductView() {
		$the_page = get_page_by_title( 'Code of Conduct' );
		$user = wp_get_current_user();
		
		$signature_date = get_option('signed_'.$user->ID);
		
		?>
		<style type="text/css">
			.heading {
				padding: 0 10px;
			}
			.form-element {
				padding: 15px;
			}
			.form-element label {
				display: block;
			}
			
			.msg-primary {
				color: #0073aa;
			}
			
			.msg-error {
				color: #f00;
			}
			.msg-success {
				color:#0f0;
			}
			
		</style>
		<div class="wrap">
			<h1>
				<?= $the_page->post_title; ?>
			</h1>
			<div class="postbox">
				<div class="inside">
					<?= $the_page->post_content; ?>
				</div>
			</div>

			<div class="postbox">
				<div class="postbox-header">
					<h2 class="heading">
						<?= $signature_date ? 'You signed the agreement' : 'Do you agree? Complete the form below.'; ?>
					</h2>
				</div>
				<div class="inside">
					<?php
						$result = false;
						if( isset( $_POST['sign'] ) ):
							if(isset($_POST['agree']) && isset( $_POST['username']) ):

								$uname = sanitize_text_field( $_POST['username'] );
								$agree = sanitize_text_field( $_POST['agree'] );

								if( $uname !== $user->user_login ): 
							?>
								<p class="msg msg-error">Wrong username.</p>
						<?php else:
								if( array_intersect(['trainee','employee', 'intern','stakeholder'], $user->roles) ) :
									$result = update_option( 'signed_'.$user->ID, date('Y-m-d H:i:s') );
								else:
						?>
							<p class="msg msg-error">Sorry you're not allow to sign this agreement.</p>
						<?php 
								endif;
							endif;
						else: ?>
							<p class="msg msg-error">Make sure all fields have been completed.</p>
						<?php endif;
					endif;
					?>
					
					<?php if( $result ) : ?>
						<p class="msg msg-success">You've signed the agreement</p>
					<?php else: 
		
							if( $signature_date ) :
		
								$modified_date = get_post_modified_time( 'Y-m-d H:i:s', true, $the_page->ID );
								$modifiedDateTime = new \DateTime( $modified_date );
								$signatureDateTime = new \DateTime( $signature_date );

								if($modifiedDateTime > $signatureDateTime ) :
					?>
									<form action="<?= admin_url( 'admin.php?page=conduct.php' ); ?>" method="post">

										<div class="form-element">
											<label for="username">Username</label>
											<input type="text" id="username" name="username" placeholder="Username"/>
										</div>

										<div class="form-element">
											<label for="agree">
												<input type="checkbox" id="agree" name="agree" value="1" /> I agree with the code of conduct
											</label>		
										</div>

										<div class="form-element">
											<input type="submit" class="button button-primary" name="sign" value="Sign" />
										</div>
									</form>
						<?php else: ?>
							<p class="msg msg-primary">You signed on <?=  $signature_date; ?></p>
						<?php endif;?>
							<?php else: ?>
									<form action="<?= admin_url( 'admin.php?page=conduct.php' ); ?>" method="post">

										<div class="form-element">
											<label for="username">Username</label>
											<input type="text" id="username" name="username" placeholder="Username"/>
										</div>

										<div class="form-element">
											<label for="agree">
												<input type="checkbox" id="agree" name="agree" value="1" /> I agree with the code of conduct
											</label>		
										</div>

										<div class="form-element">
											<input type="submit" class="button button-primary" name="sign" value="Sign" />
										</div>
									</form>
							<?php endif;
						endif; ?>
				</div>
			</div>
		</div>
	<?php
	}
}