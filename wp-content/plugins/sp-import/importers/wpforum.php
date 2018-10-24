<?php
/*
NAME	: WP-Forum Importer
FILE	: wpforum.php
ICON	: wpforum.png
INFO	: Imports Users, Groups, Forums, Topics and Posts
TAG		: WP-Forum
*/

# ------------------------------------------------------------------
#
#	WP-Forum to SP Import Definition File
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
		$def->wp_tags		= false;
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

		$map['tables']->sfgroups			= true;
			$map['sfgroups']->table			= 'forum_groups';
			$map['sfgroups']->select		= '';
			$map['sfgroups']->where			= '';
			$map['sfgroups']->group_id		= 'id';
			$map['sfgroups']->group_name	= 'name';
			$map['sfgroups']->group_desc	= 'description';
			$map['sfgroups']->group_seq		= 'sort';

		# SFFORUMS Table Mappings

		$map['tables']->sfforums			= true;
			$map['sfforums']->table			= 'forum_forums';
			$map['sfforums']->select		= '';
			$map['sfforums']->where			= '';
			$map['sfforums']->forum_id		= 'id';
			$map['sfforums']->forum_name 	= 'name';
			$map['sfforums']->group_id 		= 'parent_id';
			$map['sfforums']->forum_seq 	= 'sort';
			$map['sfforums']->forum_desc 	= 'description';
			$map['sfforums']->forum_slug 	= 'name';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'forum_threads';
			$map['sftopics']->select		= '';
			$map['sftopics']->topic_id		= 'id';
			$map['sftopics']->topic_name	= 'subject';
			$map['sftopics']->topic_date	= 'date';
			$map['sftopics']->topic_status	= '#default:0';
			$map['sftopics']->forum_id		= 'parent_id';
			$map['sftopics']->user_id		= 'starter';
			$map['sftopics']->topic_pinned	= '#default:0';
			$map['sftopics']->topic_opened	= 'views';
			$map['sftopics']->topic_slug	= 'subject';
			$map['sftopics']->post_id		= '#default:0';

		# SFPOSTS Table Mappings

		$map['tables']->sfposts				= true;
			$map['sfposts']->table			= 'forum_posts';
			$map['sfposts']->select			= '';
			$map['sfposts']->post_id		= 'id';
			$map['sfposts']->topic_id		= 'parent_id';
			$map['sfposts']->forum_id		= '#inherit:sfforums%forum_id';
			$map['sfposts']->post_content 	= 'text';
			$map['sfposts']->post_date		= 'date';
			$map['sfposts']->user_id		= 'author_id';
			$map['sfposts']->post_index		= '#default:0';
			$map['sfposts']->poster_ip		= '#default:null';

		# USERMETA Table Mappings

		$map['tables']->usermeta			= true;
			$map['usermeta']->first_name	= '#query:SELECT meta_value FROM <%dbname%>.<%dbuserpfix%>usermeta WHERE meta_key = "first_name" AND user_id = <%user_id%>';
			$map['usermeta']->last_name		= '#query:SELECT meta_value FROM <%dbname%>.<%dbuserpfix%>usermeta WHERE meta_key = "last_name" AND user_id = <%user_id%>';
			$map['usermeta']->description	= '#query:SELECT meta_value FROM <%dbname%>.<%dbuserpfix%>usermeta WHERE meta_key = "description" AND user_id = <%user_id%>';

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
	<h3>Source Forum Data: WP-Forum</h3>
	<h4>To perform the import we require access to the source wp-forum database. Please supply the following:</h4>
	<form name="wpforum2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=wpforum">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="wpforum.php"

		<p><b>NOTE: ALL fields are required</b></p>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="dbname">WP-Forum Database Name:</label></th>
				<?php if(in_array('dbname', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbname" name="dbname" value="<?php echo($source->dbname); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuser">WP-Forum Database User Name:</label></th>
				<?php if(in_array('dbuser', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuser" name="dbuser" value="<?php echo($source->dbuser); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpass">WP-Forum Database Password:</label></th>
				<?php if(in_array('dbpass', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbpass" name="dbpass" value="<?php echo($source->dbpass); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbhost">WP-Forum Database Host:</label></th>
				<?php if(in_array('dbhost', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbhost" name="dbhost" value="<?php echo($source->dbhost); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbbasepfix">WP-Forum Table Prefix:</label></th>
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