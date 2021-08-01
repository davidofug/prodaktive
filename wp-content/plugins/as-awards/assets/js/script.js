jQuery( document ).ready( function($) {
	
	$( '#search' ).on('click', function() {
		
		if( $('#certificate-code').val() !== '' || $('#certificate-code').val() !== undefined || $('#student-code').val() !== '' || $('#student-code').val() !== undefined ) {
			
			const CERTIFICATE_CODE = $('#certificate-code').val() 
			const STUDENT_CODE = $('#student-code').val()
			
			$( '#output').html( '<p class="notice">Please wait...</p>' )
			
			$.get( `${awsAjax.url}?action=get_award&CERTIFICATE_CODE=${CERTIFICATE_CODE}&STUDENT_CODE=${STUDENT_CODE}`, function() {
			  //console.log( "success" );
			})
			  .done(function( response ) {
					//console.log( response )
					const RESPONSE = JSON.parse(response)
					
					if( RESPONSE.result == 'successful')
						$( '#output' ).html( '<p class="success"><b>Good news:</b> certificate found.</p>' )
											
			  })
			  .fail(function( error ) {
				console.log( error )
				$( '#output' ).html( '<p class="error">Encountered technical issues. Try again. If problem persist contact administrator. <p>')
			  })
			  .always( function( response ) {
				
					const RESPONSE = JSON.parse( response )
					
					if( RESPONSE.result == 'successful') {
						
						const {award: AWARD, awards: AWARDS, trainee} = RESPONSE
						
						if( typeof AWARD !== 'undefined' ) {
							const CERTIFICATE =
									`<table id="certificate-list">
										<tr>
											<th>Photo</th>
											<th>Student Code</th>
											<th>Certificate Code</th>
											<th>Name</th>
											<th>Certificate Type</th>
											<th>Program</th>
										</tr>
										<tr>
											<td>
												<img src="${AWARD.trainee.photo}" class="photo" width="50" height="50" />
											</td>
											<td>${AWARD.trainee.code}</td>
											<td>${AWARD.code}</td>
											<td>${AWARD.trainee.name.first} ${AWARD.trainee.name.last}</td>
											<td>${AWARD.type}</td>
											<td>${AWARD.in}</td>
										</tr>
								</table>`			
						
							$( '#output' ).html( CERTIFICATE )
							
						} else {
							
							let CERTIFICATES = ``
							AWARDS.forEach( AWARD => {
								CERTIFICATES += `
										<tr>
											<td>${AWARD.code}</td>
											<td>${AWARD.type}</td>
											<td>${AWARD.in}</td>
										</tr>
									`
							})
							
							const HTML_CERTIFICATES = `
								<div>
									<img src="${trainee.photo}" />
									<p>${trainee.name.first} ${trainee.name.last} (${trainee.code})</p>
								</div>
								<table id="certificate-list">
									<caption>Awards</caption>
									${CERTIFICATES}
								</table>`
							
							$( '#output' ).html( HTML_CERTIFICATES )
						}

					} else {
						if( CERTIFICATE_CODE != null )
							$( '#output' ).html( `<p class="error">Sorry certificate <strong>${CERTIFICATE_CODE}</strong> not found</p>` )
						else if( STUDENT_CODE != null )				
							$( '#output' ).html( `<p class="error">Sorry certificate for student ID <strong>${STUDENT_CODE}</strong> not found</p>` )
					}
				
			  })

		}
		
	})
	
})