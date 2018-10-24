<?php
/*
NAME	: bbPress Importer <br />(Older separate site version)
FILE	: bbpress-site.php
ICON	: bbpress.png
INFO	: Imports Users, Forums, Topics, Posts and Tags<br /><b>NOTE - Tags import requires the SP Tags Plugin to be active</b>
TAG		: bbPress-site
*/

# ------------------------------------------------------------------
#
#	bbPress (Site Version) to SP Import Definition File
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
		$def->required		= 'dbname,dbuser,dbpass,dbhost,dbbasepfix,dbuserpfix,dbpassoption';
		$def->wp_tags		= true;
		$def->parse_bbcode	= false;

	update_option('spi-def', $def);

	# Table/Data mapping
	# ------------------------------------------------------------------

	$map = get_option('spi-map');

		# USERS Table Mappings

		$map['tables']->users				= true;
			$map['users']->table			= 'users';
			$map['users']->select			= '';
			$map['users']->where			= '';
			$map['users']->ID				= 'ID';
			$map['users']->user_login		= 'user_login';
			$map['users']->user_pass		= 'user_pass';
			$map['users']->user_nicename	= 'user_nicename';
			$map['users']->user_email		= 'user_email';
			$map['users']->user_url			= 'user_url';
			$map['users']->user_registered	= 'user_registered';
			$map['users']->display_name		= 'display_name';

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
			$map['sfforums']->forum_id		= 'forum_id';
			$map['sfforums']->forum_name 	= 'forum_name';
			$map['sfforums']->group_id 		= '#default:1';
			$map['sfforums']->forum_seq 	= 'forum_order';
			$map['sfforums']->forum_desc 	= 'forum_desc';
			$map['sfforums']->forum_slug 	= 'forum_name';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'topics';
			$map['sftopics']->select		= '';
			$map['sftopics']->topic_id		= 'topic_id';
			$map['sftopics']->topic_name	= 'topic_title';
			$map['sftopics']->topic_date	= 'topic_start_time';
			$map['sftopics']->topic_status	= '#default:0';
			$map['sftopics']->forum_id		= 'forum_id';
			$map['sftopics']->user_id		= 'topic_poster';
			$map['sftopics']->topic_pinned	= 'topic_sticky';
			$map['sftopics']->topic_opened	= '#default:0';
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
			$map['sfposts']->post_date		= 'post_time';
			$map['sfposts']->user_id		= 'poster_id';
			$map['sfposts']->post_index		= 'post_position';
			$map['sfposts']->poster_ip		= 'poster_ip';


		# USERMETA Table Mappings

		$map['tables']->usermeta			= true;
			$map['usermeta']->first_name	= '#query:SELECT meta_value FROM <%dbname%>.<%dbuserpfix%>usermeta WHERE meta_key = "first_name" AND user_id = <%user_id%>';
			$map['usermeta']->last_name		= '#query:SELECT meta_value FROM <%dbname%>.<%dbuserpfix%>usermeta WHERE meta_key = "last_name" AND user_id = <%user_id%>';
			$map['usermeta']->description	= '#query:SELECT meta_value FROM <%dbname%>.<%dbuserpfix%>usermeta WHERE meta_key = "description" AND user_id = <%user_id%>';

	update_option('spi-map', $map);

	# ------------------------------------------------------------------
	# Form: bbPress
	# ------------------------------------------------------------------

	$required = explode(',', $def->required);
	$bullet = '<img src="'.SPI_URL.'importers/logos/bullet.png" alr="" />';
	$icon = '<td>'.$bullet.'</td>';
	$noicon = '<td></td>';

	?>
	<div id="spiForm" class="spiMainPanel">
	<h3>Source Forum Data: bbPress (Older separate site version)</h3>
	<h4>To perform the import we require access to the source bbPress database. Please supply the following:</h4>
	<form name="bb2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=bbpress">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="bbpress-site.php"

		<p><b><?php echo($bullet); ?>&nbsp;&nbsp;Required Data</b></p>

		<table id="sourcedata" class="form-table">

			<tr valign="top">
				<th scope="row"><label for="dbname">bbPress Database Name:</label></th>
				<?php if(in_array('dbname', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbname" name="dbname" value="<?php echo($source->dbname); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuser">bbPress Database User Name:</label></th>
				<?php if(in_array('dbuser', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuser" name="dbuser" value="<?php echo($source->dbuser); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpass">bbPress Database Password:</label></th>
				<?php if(in_array('dbpass', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbpass" name="dbpass" value="<?php echo($source->dbpass); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbhost">bbPress Database Host:</label></th>
				<?php if(in_array('dbhost', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbhost" name="dbhost" value="<?php echo($source->dbhost); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbbasepfix">bbPress Table Prefix:</label></th>
				<?php if(in_array('dbbasepfix', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbbasepfix" name="dbbasepfix" value="<?php echo($source->dbbasepfix); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuserpfix">Wordpress User Table Prefix:</label></th>
				<?php if(in_array('dbuserpfix', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuserpfix" name="dbuserpfix" value="<?php echo($source->dbuserpfix); ?>" />
				<p>Set to the prefix of the database table where the users data resides.</p></td>
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