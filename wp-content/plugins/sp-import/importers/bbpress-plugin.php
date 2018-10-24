<?php
/*
NAME	: bbPress Importer <br />(Newer plugin version)
FILE	: bbpress-plugin.php
ICON	: bbpress.png
INFO	: Imports Users, Forums, Topics, Posts and Tags<br /><b>NOTE - Tags import requires the SP Tags Plugin to be active</b>
TAG		: bbPress-plugin
*/

# ------------------------------------------------------------------
#
#	bbPress (Plugin Version) to SP Import Definition File
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
			$map['sfforums']->table			= 'posts';
			$map['sfforums']->select		= "SELECT ID, post_title AS forum_name, post_name AS forum_slug
											   FROM <%dbname%>.<%dbbasepfix%>posts WHERE post_type='forum' AND post_status='publish'
											   ORDER BY ID";
			$map['sfforums']->where			= "WHERE post_type='forum' AND post_status='publish'";
			$map['sfforums']->forum_id		= 'ID';
			$map['sfforums']->forum_name 	= 'forum_name';
			$map['sfforums']->group_id 		= '#default:1';
			$map['sfforums']->forum_seq 	= '#default:0';
			$map['sfforums']->forum_desc 	= '';
			$map['sfforums']->forum_slug 	= 'forum_slug';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'posts';
			$map['sftopics']->select		= "SELECT ID, post_date, post_author AS user_id, post_title AS topic_name, post_name AS topic_slug, post_parent AS forum_id
											   FROM <%dbname%>.<%dbbasepfix%>posts
											   WHERE post_type='topic' AND post_status='publish' AND post_parent=<%forum_id%>
											   ORDER BY ID";
			$map['sftopics']->topic_id		= 'ID';
			$map['sftopics']->topic_name	= 'topic_name';
			$map['sftopics']->topic_date	= 'post_date';
			$map['sftopics']->topic_status	= '#default:0';
			$map['sftopics']->forum_id		= 'forum_id';
			$map['sftopics']->user_id		= 'user_id';
			$map['sftopics']->topic_pinned	= '#default:0';
			$map['sftopics']->topic_opened	= '#default:0';
			$map['sftopics']->topic_slug	= 'topic_slug';
			$map['sftopics']->post_id		= 'ID';

		# SFPOSTS Table Mappings

		$map['tables']->sfposts				= true;
			$map['sfposts']->table			= 'posts';
			$map['sfposts']->select			= "SELECT ID, post_author AS user_id, post_date, post_content,
											   (SELECT meta_value FROM <%dbname%>.<%dbbasepfix%>postmeta WHERE post_id=ID AND meta_key='_bbp_forum_id') AS forum_id,
											   (SELECT meta_value FROM <%dbname%>.<%dbbasepfix%>postmeta WHERE post_id=ID AND meta_key='_bbp_topic_id') AS topic_id,
											   (SELECT meta_value FROM <%dbname%>.<%dbbasepfix%>postmeta WHERE post_id=ID AND meta_key='_bbp_anonymous_name') AS guest_name,
											   (SELECT meta_value FROM <%dbname%>.<%dbbasepfix%>postmeta WHERE post_id=ID AND meta_key='_bbp_anonymous_email') AS guest_email,
											   (SELECT meta_value FROM <%dbname%>.<%dbbasepfix%>postmeta WHERE post_id=ID AND meta_key='_bbp_author_ip') AS poster_ip
											   FROM <%dbname%>.<%dbbasepfix%>posts
											   WHERE (post_parent=<%topic_id%> AND post_status='publish') OR (ID=<%topic_id%>)
											   ORDER BY ID";
			$map['sfposts']->post_id		= 'ID';
			$map['sfposts']->topic_id		= 'topic_id';
			$map['sfposts']->forum_id		= 'forum_id';
			$map['sfposts']->post_content 	= 'post_content';
			$map['sfposts']->post_date		= 'post_date';
			$map['sfposts']->user_id		= 'user_id';
			$map['sfposts']->post_index		= '#default:0';
			$map['sfposts']->poster_ip		= 'poster_ip';
			$map['sfposts']->guest_name		= 'guest_name';
			$map['sfposts']->guest_email	= 'guest_email';

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
	<h3>Source Forum Data: bbPress ((Newer plugin version)</h3>
	<h4>To perform the import we require access to the source bbPress database. Please supply the following:</h4>
	<form name="bb2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=bbpress">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="bbpress-plugin.php"

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