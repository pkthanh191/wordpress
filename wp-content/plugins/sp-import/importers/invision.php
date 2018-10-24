<?php
/*
NAME	: Invision PB Importer
FILE	: invision.php
ICON	: invision.png
INFO	: Imports Users, Forums, Topics and Posts
TAG		: Invision
*/

# ------------------------------------------------------------------
#
#	Invision to SP Import Definition File
#
# ------------------------------------------------------------------

# ------------------------------------------------------------------
# spi_collect_db_settings()
# The form to collect the source data settings
# ------------------------------------------------------------------
function spi_collect_db_settings() {

	# Source DB Settings
	# ------------------------------------------------------------------

	$source = get_option('spi-dbase');
	if(empty($source)) {

		# Source DB Connection Settings

		$source->dbname			= '';
		$source->dbhost			= '';
		$source->dbuser			= '';
		$source->dbpass			= '';
		$source->dbbasepfix		= '';
		$source->dbuserpfix		= '';
		$source->dbpassoption	= 'ulogin';
		$source->utf8encode	= 0;
	}

	# Misc definitions
	# ------------------------------------------------------------------

	$def = get_option('spi-def');

		$def->user_batch	= 100;
		$def->required		= 'dbname,dbuser,dbpass,dbhost,dbpassoption';
		$def->wp_tags		= false;
		$def->parse_bbcode	= true;

	update_option('spi-def', $def);

	# Table/Data mapping
	# ------------------------------------------------------------------

	$map = get_option('spi-map');

		# USERS Table Mappings

		# NOTE: On some versions if Invision the mapping for ID below is actually just:
		# id
		# and NOT
		# member_id

		$map['tables']->users				= true;
			$map['users']->table			= 'members';
			$map['users']->select			= '';
			$map['users']->where			= '';
			$map['users']->ID				= 'member_id';
			$map['users']->user_login		= 'name';
			$map['users']->user_pass		= '';
			$map['users']->user_nicename	= 'members_display_name';
			$map['users']->user_email		= 'email';
			$map['users']->user_url			= '';
			$map['users']->user_registered	= '#timestamp:joined';
			$map['users']->display_name		= 'members_display_name';

		# SFGROUPS Table Mappings

		$map['tables']->sfgroups			= false;
			$map['sfgroups']->table			= '';
			$map['sfgroups']->select		= '';
			$map['sfgroups']->where			= '';
			$map['sfgroups']->group_id		= '';
			$map['sfgroups']->group_name	= '';
			$map['sfgroups']->group_desc	= '';
			$map['sfgroups']->group_seq		= '';

		# SFFORUMS Table Mappings

		$map['tables']->sfforums			= true;
			$map['sfforums']->table			= 'forums';
			$map['sfforums']->select		= '';
			$map['sfforums']->where			= '';
			$map['sfforums']->forum_id		= 'id';
			$map['sfforums']->forum_name 	= 'name';
			$map['sfforums']->group_id 		= '#default:1';
			$map['sfforums']->forum_seq 	= 'position';
			$map['sfforums']->forum_desc 	= 'description';
			$map['sfforums']->forum_slug 	= 'name';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'topics';
			$map['sftopics']->select		= '';
			$map['sftopics']->topic_id		= 'tid';
			$map['sftopics']->topic_name	= 'title';
			$map['sftopics']->topic_date	= '#timestamp:start_date';
			$map['sftopics']->topic_status	= '#default:0';
			$map['sftopics']->forum_id		= 'forum_id';
			$map['sftopics']->user_id		= 'last_poster_id';
			$map['sftopics']->topic_pinned	= 'pinned';
			$map['sftopics']->topic_opened	= 'views';
			$map['sftopics']->topic_slug	= 'title';
			$map['sftopics']->post_id		= '#default:0';

		# SFPOSTS Table Mappings

		$map['tables']->sfposts				= true;
			$map['sfposts']->table			= 'posts';
			$map['sfposts']->select			= '';
			$map['sfposts']->post_id		= 'pid';
			$map['sfposts']->topic_id		= 'topic_id';
			$map['sfposts']->forum_id		= '#inherit:sfforums%forum_id';
			$map['sfposts']->post_content 	= 'post';
			$map['sfposts']->post_date		= '#timestamp:post_date';
			$map['sfposts']->user_id		= 'author_id';
			$map['sfposts']->post_index		= '#default:0';
			$map['sfposts']->poster_ip		= 'ip_address';

		# USERMETA Table Mappings

		$map['tables']->usermeta			= false;

	update_option('spi-map', $map);

	# ------------------------------------------------------------------
	# Form: WP Forum
	# ------------------------------------------------------------------

	$required = explode(',', $def->required);
	$bullet = '<img src="'.SPI_URL.'importers/logos/bullet.png" alr="" />';
	$icon = '<td>'.$bullet.'</td>';
	$noicon = '<td></td>';

	?>
	<div id="spiForm" class="spiMainPanel">
	<h3>Source Forum Data: Invision</h3>
	<h4>To perform the import we require access to the source Invision database. Please supply the following:</h4>
	<form name="Invision2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=Invision">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="invision.php"

		<p><b><?php echo($bullet); ?>&nbsp;&nbsp;Required Data</b></p>

		<table id="sourcedata" class="form-table">

			<tr valign="top">
				<th scope="row"><label for="dbname">Invision Database Name:</label></th>
				<?php if(in_array('dbname', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbname" name="dbname" value="<?php echo($source->dbname); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuser">Invision Database User Name:</label></th>
				<?php if(in_array('dbuser', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuser" name="dbuser" value="<?php echo($source->dbuser); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpass">Invision Database Password:</label></th>
				<?php if(in_array('dbpass', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbpass" name="dbpass" value="<?php echo($source->dbpass); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbhost">Invision Database Host:</label></th>
				<?php if(in_array('dbhost', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbhost" name="dbhost" value="<?php echo($source->dbhost); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbbasepfix">Invision Table Prefix:</label></th>
				<?php if(in_array('dbbasepfix', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbbasepfix" name="dbbasepfix" value="<?php echo($source->dbbasepfix); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpassoption">Password Options:</label></th>
				<?php if(in_array('dbpassoption', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td>

					<input type="radio" name="dbpassoption" id="create" value="create" />
					<label for="create">&nbsp;&nbsp;&nbsp;<b>Let WordPress create random passwords</b></label><br />

					<input type="radio" name="dbpassoption" id="ulogin" value="ulogin" />
					<label for="ulogin">&nbsp;&nbsp;&nbsp;<b>Create passwords from login name</b></label><br />

				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="utf8encode">UTF-8 encode results</label></th>
				<td>

					<input type="checkbox" name="utf8encode" id="utf8encode" />

				</td>
			</tr>

		</table><br />

		<div style="clear: both"></div>

		<input type="submit" class="button-primary" id="sbutton" name="goimport" value="Perform Import" />

	</form>
	</div>
	<?php
}

?>