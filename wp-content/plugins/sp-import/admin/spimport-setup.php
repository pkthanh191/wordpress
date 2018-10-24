<?php

# ------------------------------------------------------------------
#
#	Select the import source
#	V5
# ------------------------------------------------------------------

include_once(SPI_DIR.'admin/spimport-support.php');

# ------------------------------------------------------------------

# Load standard header
# ------------------------------------------------------------------
spi_header();

# Entry Point
# ------------------------------------------------------------------
if(isset($_POST['s1'])) {
	if(!isset($_POST['selectsource'])) {
		spi_error('Please Select the Import Source');
		spi_load_selection();
	} else {
		# Made importer selection
		spi_load_data_form();
	}
} elseif(isset($_POST['s2'])) {
	# Completed db setting form
	$sys = $_GET['sys'];

	$source = new stdClass();
	foreach($_POST as $key=>$item) {
		$source->$key = $item;
	}
	if(!isset($source->dbuserpfix)) $source->dbuserpfix = $source->dbbasepfix;
	if(!isset($source->utf8encode)) $source->utf8encode = 0;
	if($source->utf8encode) $source->utf8encode = 1;

	update_option('spi-dbase', $source);

	# Validate Settings and Connection
	if(!spi_check_db_settings($source, get_option('spi-def'))) {
		spi_load_data_form();
	} elseif(!spi_check_db_connection($source)) {
		spi_load_data_form();
	} else {
		# All OK - let;s go with import
		if(!spi_prepare_import()) {
			spi_load_data_form();
		}
	}
} else {

	# First time in check the SP conditions
	# ------------------------------------------------------------------
	if(!spi_check_SP_status()) {
		spi_footer();
		return;
	}

	if(!spi_check_SP_user()) {
		spi_footer();
		return;
	}

	spi_load_selection();
}

spi_footer();

# ---- END -----


# Load available importer options
# ------------------------------------------------------------------
function spi_load_selection() {
	$imp = array();
	$path = SPI_DIR.'importers/';
	$dlist = opendir($path);
	while (false !== ($file = readdir($dlist))) {
		if(is_file($path.$file)) {
			$imp[] = spi_get_importer_header($path.$file);
		}
	}
	closedir($dlist);

	if(empty($imp)) {
		spi_no_importers();
		return;
	}

	# Load up the selection form
	# ------------------------------------------------------------------
	?>
		<div id="spiForm" class="spiMainPanel">
			<form name="selectimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php">
				<input type ="hidden" id="s1" name="s1" value="s1" />
				<h4>Select Import Source</h4>
				<?php foreach($imp as $i) { ?>
					<div class='spiSourcePanel'>
					<h3><?php echo($i['NAME']); ?></h3>
					<img src=<?php echo(SPI_URL.'importers/logos/'.$i['ICON']); ?> alt='' \>
					<p><input type="radio" name="selectsource" id="<?php echo($i['FILE']); ?>"value="<?php echo($i['FILE']); ?>" />
					<label for="<?php echo($i['FILE']); ?>">&nbsp;&nbsp;&nbsp;<b><?php echo($i['TAG']); ?></b></label><br />
					<?php echo($i['INFO']); ?></p></div>
				<?php } ?>
				<input type="submit" class="button-primary" name="submit" value="Select Importer" />
			</form>
		</div>

		<div id="spiHelp" class="spiMainPanel">
			<?php include_once(SPI_DIR.'help/help-select.php'); ?>
		</div>
	<?php
}

function spi_load_data_form() {
	$def = SPI_DIR.'importers/'.$_POST['selectsource'];
	include_once($def);
	spi_collect_db_settings();
	?>
	<div id="spiHelp" class="spiMainPanel">
		<?php include_once(SPI_DIR.'help/help-data.php'); ?>
	</div>
	<?php
}

?>