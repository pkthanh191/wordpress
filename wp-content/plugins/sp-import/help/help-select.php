<?php
# Help Panel - Select form
# V5
?>

	<h1>The Simple:Press V5 Data Importer</h1>

	<h3>Before you start</h3>

	<p>Please ensure that you make a full backup of the target WordPress database before using any of these data importers. We will be unable to offer
	any support for problems that may be encountered if there is no backup that can be restored.</p>

	<h3>Requirements</h3>

	<p>To use any of these data importers, Simple:Press must be active and installed. It must also have no forum groups or forums defined. We do not
	currently support importing data into a pre-existing and active Simple:Press forum.</p>
	<p>The source forum data must be available to the importer on a mySQL database. It does not have to be imported first into the target WordPress
	database and can be on a different server as long as the necessary credentials can be supplied.

	<h3>What data is imported?</h3>

	<p>Please note that not all available data may be imported for each source forum. The main items of data that each importer attempts to import
	are listed against each available source.</p>

	<h3>Sub or Child Forums</h3>

	<p>If the source forum supports 'sub' or 'child' forums these may be imported as ordinary forums. If this is the case they can be reset to sub-forums
	within the Simple:Press Administration.</p>

	<h3>Use of bbCode, smileys and other embeded items</h3>

	<p>Please note that forums which store their posts with embedded bbCode tags may use a different format or extended tag set to Simple:Press. While
	the importer will attempt to parse the tags we can not guarantee that we will convert them all. Posts with embedded smileys, images and other
	media links may not resolve after the import depending upon their location.</p>

	<h3>If your source forum is not listed</h3>

	<p>if your source forum is not available for import then we may be able to create an importer. We would usually require a copy of the source database
	(as a mySQL dump) for data mapping and testing. Any such database supplied is never made public and is destroyed when the importer has been
	confirmed to work successfully.</p>
