<?php
# Help Panel - Data form
# V5
?>

	<h1>The Simple:Press V5 Data Importer</h1>

	<h3>Source database access</h3>

	<p>The Simple:Press Data Importer requires the necessary credentials along with any other pertinent informartion, to access the source
	forums's database. Please complete this form and note that fields marked with the red bullet are required to be filled.</p>

	<h3>If the source forum is a WordPress plugin</h3>

	<p>If your source forum is another WordPress plugin then the importer will require both the table prefix used for the forum source tables
	as well as the table prefix used for the WordPress 'Users' table - even if these are the same and the current source tables reside in the
	same database as the target Simple:Press tables.</p>

	<h3>Password handling</h3>
	<p>If a user being imported is found to already exist in the target database (by their login name) neither their password or their capabilities
	data will be changed by the importer. If not their capability will be set to 'subscriber'. Passwords imported from non-WordPress databases can
	be copied into the new user record but they will not work and will require the user to call for a password reset.</p>

	<h3>Encode results to utf-8</h3>
	<p>This option is usually not required but following the import if characters do not display correctly or posts are truncated then try again with this option turned on</p>