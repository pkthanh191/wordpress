<?php
/*
NAME	: phpBB Importer
FILE	: phpbb.php
ICON	: phpbb.png
INFO	: Imports Users, Forums, Topics and Posts
TAG		: phpBB
*/

# ------------------------------------------------------------------
#
#	phpBB to SP Import Definition File
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
		$source->dbpassoption	= 1;
		$source->utf8encode	= 0;
	}

	# Misc definitions
	# ------------------------------------------------------------------

	$def = get_option('spi-def');

		$def->user_batch	= 100;
		$def->required		= 'dbname,dbuser,dbpass,dbhost,dbbasepfix,dbpassoption';
		$def->wp_tags		= false;
		$def->parse_bbcode	= true;

	update_option('spi-def', $def);

	# Table/Data mapping
	# ------------------------------------------------------------------

	$map = get_option('spi-map');

		# USERS Table Mappings

		$map['tables']->users				= true;
			$map['users']->table			= 'users';
			$map['users']->select			= '';
			$map['users']->where			= '';
			$map['users']->ID				= 'user_id';
			$map['users']->user_login		= 'username';
			$map['users']->user_pass		= 'user_password';
			$map['users']->user_nicename	= 'username_clean';
			$map['users']->user_email		= 'user_email';
			$map['users']->user_url			= '';
			$map['users']->user_registered	= '#timestamp:user_regdate';
			$map['users']->display_name		= 'username';

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
			$map['sfforums']->select		= 'SELECT * FROM <%dbname%>.<%dbbasepfix%>forums WHERE parent_id != 0 ORDER BY forum_id';
			$map['sfforums']->where			= 'WHERE parent_id != 0';
			$map['sfforums']->forum_id		= 'forum_id';
			$map['sfforums']->forum_name 	= 'forum_name';
			$map['sfforums']->group_id 		= '#default:1';
			$map['sfforums']->forum_seq 	= 'forum_id';
			$map['sfforums']->forum_desc 	= 'forum_desc';
			$map['sfforums']->forum_slug 	= 'forum_name';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'topics';
			$map['sftopics']->select		= '';
			$map['sftopics']->topic_id		= 'topic_id';
			$map['sftopics']->topic_name	= 'topic_title';
			$map['sftopics']->topic_date	= '#timestamp:topic_time';
			$map['sftopics']->topic_status	= '#default:0';
			$map['sftopics']->forum_id		= 'forum_id';
			$map['sftopics']->user_id		= 'topic_poster';
			$map['sftopics']->topic_pinned	= '#default:0';
			$map['sftopics']->topic_opened	= 'topic_views';
			$map['sftopics']->topic_slug	= 'topic_title';
			$map['sftopics']->post_id		= 'topic_last_post_id';

		# SFPOSTS Table Mappings

		$map['tables']->sfposts				= true;
			$map['sfposts']->table			= 'posts';
			$map['sfposts']->select			= '';
			$map['sfposts']->post_id		= 'post_id';
			$map['sfposts']->topic_id		= 'topic_id';
			$map['sfposts']->forum_id		= 'forum_id';
			$map['sfposts']->post_content 	= 'post_text';
			$map['sfposts']->post_date		= '#timestamp:post_time';
			$map['sfposts']->user_id		= 'poster_id';
			$map['sfposts']->post_index		= '#default:0';
			$map['sfposts']->poster_ip		= '';

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
	<h3>Source Forum Data: phpBB</h3>
	<h4>To perform the import we require access to the source phpBB database. Please supply the following:</h4>
	<form name="phpBB2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=phpbb">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="phpbb.php"

		<p><b><?php echo($bullet); ?>&nbsp;&nbsp;Required Data</b></p>

		<table id="sourcedata" class="form-table">

			<tr valign="top">
				<th scope="row"><label for="dbname">phpBB Database Name:</label></th>
				<?php if(in_array('dbname', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbname" name="dbname" value="<?php echo($source->dbname); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuser">phpBB Database User Name:</label></th>
				<?php if(in_array('dbuser', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuser" name="dbuser" value="<?php echo($source->dbuser); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpass">phpBB Database Password:</label></th>
				<?php if(in_array('dbpass', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbpass" name="dbpass" value="<?php echo($source->dbpass); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbhost">phpBB Database Host:</label></th>
				<?php if(in_array('dbhost', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbhost" name="dbhost" value="<?php echo($source->dbhost); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbbasepfix">phpBB Table Prefix:</label></th>
				<?php if(in_array('dbbasepfix', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbbasepfix" name="dbbasepfix" value="<?php echo($source->dbbasepfix); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpassoption">Password Options:</label></th>
				<?php if(in_array('dbpassoption', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td>

					<input type="radio" name="dbpassoption" id="copy" value="copy" />
					<label for="copy">&nbsp;&nbsp;&nbsp;<b>Copy source passwords from user records</b></label><br />

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