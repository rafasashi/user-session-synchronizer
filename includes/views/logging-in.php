<!DOCTYPE html>
	
	<html>
	
		<header>
		
			<title>Loading...</title>
		
		</header>
		
		<body>

			<?php $this->ussync_call_domains(); ?>

			Loading...
			
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
			
			<script>
			
				;(function($){

					$(document).ready(function(){

						$(window).load(function(){
							
							window.location.replace("<?php echo urldecode($_GET['redirect_to']); ?>");
						});
					
					});
					
				})(jQuery);			
				
			</script>			
			
		</body>
		
	</html>

<?php exit; ?>