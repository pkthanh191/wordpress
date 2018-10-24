<?php

# V5

# ------------------------------------------------------------------
# spi_header()
# Opens admin div and displays title
# ------------------------------------------------------------------
function spi_header() {
	?>
	<div class="wrap nosubsub">
	<div id="icon-plugins" class="icon32"><br /></div>
	<h2>Simple:Press Data Import</h2>
	<div style="clear: both"></div>
	<div id="spiContainer">
	<div id="spiMainHead">
	<img class="spiLeft" src="<?php echo SFADMINIMAGES; ?>SPF-badge-125.png" alt="" title="" />
	<h1>Simple:Press Data Import</h1>
	<div style="clear: both"></div>
	</div><br />
	<?php

# -- Scratchpad ----------------

# ------------------------------
}

# ------------------------------------------------------------------
# spi_footer()
# Closes admin div
# ------------------------------------------------------------------
function spi_footer() {
	?>
	</div></div>
	<?php
}

# ------------------------------------------------------------------
# spi_check_SP_status()
# Check that SP installed and unused (test sfgroups table)
# ------------------------------------------------------------------
function spi_check_SP_status()
{
	global $wpdb;
	$c = $wpdb->get_var("SELECT COUNT(*) FROM ".SFGROUPS);
	if($c) {
		spi_error('Unable to Import: Your Simple:Press forum already contains data<br />This importer requires a new and empty Simple:Press installation');
		return false;
	} else {
		return true;
	}
}

# ------------------------------------------------------------------
# spi_check_SP_user()
# Check current user is an SP Admin
# ------------------------------------------------------------------
function spi_check_SP_user()
{
	global $wpdb, $current_user;
	$c = $wpdb->get_var("SELECT admin FROM ".SFMEMBERS." WHERE user_id=".$current_user->ID);
	if(!$c) {
		spi_error('Unable to import: You must be a Simple:Press Admin');
		return false;
	} else {
		return true;
	}
}

# ------------------------------------------------------------------
# spi_check_db_settings()
# Check all db settings have been entered
# ------------------------------------------------------------------
function spi_check_db_settings($db, $def) {
	$req = explode(',', $def->required);
	$good = true;
	foreach($req as $i) {
		if(empty($db->$i)) $good = false;
	}
	if(!$good) {
		spi_error('All items are required - please complete');
	}
	return $good;
}

# ------------------------------------------------------------------
# spi_check_db_connection()
# Test the connection to the source database
# ------------------------------------------------------------------
function spi_check_db_connection($db)
{
	$conn = @mysqli_connect($db->dbhost, $db->dbuser, $db->dbpass);
	if(!$conn) {
		spi_error('Unable to connect to the source database using supplied information');
		return false;
	} else {
		return true;
	}
}

# ------------------------------------------------------------------
# spi_no_importers()
# Returns message if no import def files found
# ------------------------------------------------------------------
function spi_no_importers() {
	spi_error('No importers have been found');
}

# ------------------------------------------------------------------
# spi_error()
# Display an error message
# ------------------------------------------------------------------
function spi_error($msg) {
	?>
	<div class="spiMainPanel spiError">
	<?php echo($msg); ?>
	</div>
	<div style="clear: both"></div>
	<?php
}

# ------------------------------------------------------------------
# spi_get_importer_header()
# Read and return array from importer file header
# ------------------------------------------------------------------
function spi_get_importer_header($file) {
	$data = array();
	$fh = fopen($file, 'r');
	do {
		$theLine = fgets($fh);
		if (feof($fh)) {
			break;
		}
	} while (substr($theLine, 0, 4) != 'NAME');

	$line=explode(':', $theLine);
	$data[trim($line[0])] = trim($line[1]);

	$theEnd = false;
	do {
		if (feof($fh)) {
			break;
		}
		$theLine = fgets($fh);
		if (substr($theLine, 0, 2) == '*/') {
			$theEnd = true;
		} else {

			$line=explode(':', $theLine);
			$data[trim($line[0])] = trim($line[1]);
		}
	} while ($theEnd == false);

	fclose($fh);

	return $data;
}

# ------------------------------------------------------------------
# spi_prepare_import()
# Prepare to perform the actual data import
# ------------------------------------------------------------------
function spi_prepare_import()
{
	# grab control arrays
	$source	= get_option('spi-dbase');
	$def 	= get_option('spi-def');
	$map 	= get_option('spi-map');

	$dbConn = @mysqli_connect($source->dbhost, $source->dbuser, $source->dbpass);

	# calculate how many iterations
	$uphases = 0;
	$fphases = 0;

	# how many users passes at $def->user_batch a pop?
	$dbResource = mysqli_query($dbConn, "SELECT COUNT(".$map['users']->ID.") as num FROM `".$source->dbname."`.".$source->dbuserpfix.$map['users']->table.' '.$map['users ']->where);
	if(!$dbResource) {
		spi_error('Error: Unable to query USERS using the db settings supplied');
		return false;
	}
	$items = @mysqli_fetch_array($dbResource, MYSQLI_ASSOC);
	if(!$items) {
		spi_error('Error: No USER records have been found using the db settings supplied');
		return false;
	}
	$num = $items['num'];
	$uphases = ceil($num / $def->user_batch);

	# now count the forums
	$dbResource = mysqli_query($dbConn, "SELECT COUNT(".$map['sfforums']->forum_id.") as num FROM `".$source->dbname."`.".$source->dbbasepfix.$map['sfforums']->table.' '.$map['sfforums']->where);
	if(!$dbResource) {
		spi_error('Error: Unable to query FORUMS using the db settings supplied');
		return false;
	}
	$items = @mysqli_fetch_array($dbResource, MYSQLI_ASSOC);
	if(!$items) {
		spi_error('Error: No FORUM records have been found using the db settings supplied');
		return false;
	}
	$fphases = $items['num'];

	$phpfile = SPI_URL."engine/spimport.php";
	$image = SFCOMMONIMAGES."working.gif";

	?>
		<div class="spiMainPanel" id="spiWaitZone"></div>
		<div style="clear: both"></div>
		<div class="spiMainPanel" id="spiImportZone"></div>
		<div class="spiMainPanel" id="spiEndZone">
			<div id="spiHelp" class="spiMainPanel">
				<?php include_once(SPI_DIR.'help/help-next.php'); ?>
			</div>
		</div>

		<script type="text/javascript">
		spjPerformImport('<?php echo($phpfile); ?>', <?php echo($uphases); ?>, <?php echo($fphases); ?>, 1, '<?php echo($image); ?>');
		</script>
	<?php
	return true;
}

?>