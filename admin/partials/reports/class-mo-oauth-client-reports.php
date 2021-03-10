<?php
	
	class Mo_OAuth_Client_Admin_Reports {
	
		public static function report() {
			self::reports_page();
		}
		
		public static function reports_page(){
			$disabled = true;
			echo'<div class="mo_oauth_premium_option_text"><span style="color:red;">*</span>This is a enterprise feature. 
				<a href="admin.php?page=mo_oauth_settings&tab=licensing">Click Here</a> to see our full list of Enterprise Features.</div>
				<div class="mo_table_layout mo_oauth_premium_option">
				<div class="mo_oauth_client_small_layout">';
			echo'<h2>Login Transactions Report</h2>
					<div class="mo_oauth_client_small_layout hidden">	
						<h3>Advanced Report</h3>
						<form method="post" action="">
							<input type="hidden" name="option" value="mo_oauth_client_advanced_reports">
							<br><input disabled type="submit" style="width:100px;" value="Search" class="button button-primary button-large">
						</form>
						<br>
					</div>
					
					<table id="login_reports" class="display" style="border-collapse: collapse;" cellspacing="0" width="100%">
						<thead>
							<tr style="border-bottom: 2px solid #000;">
								<th>IP Address</th>
								<th>Username</th>
								<th>Status</th>
								<th>TimeStamp</th>
							</tr>
						</thead>
						<tr style="border-bottom: 2px solid #000;"><td colspan="4" align="center" style="background: #f2f2f2; padding: 10px;">No Data Found in the Table.</td></tr>
						<tbody>';
						   
		echo'	        </tbody>
					</table>
				</div>
				
			</div>';

		}


	
	
	}