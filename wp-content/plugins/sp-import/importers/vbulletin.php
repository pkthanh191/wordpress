<?php
/*
NAME	: vBulletin Importer
FILE	: vbulletin.php
ICON	: vbulletin.png
INFO	: Imports Users, Groups, Forums, Topics and Posts
TAG		: vBulletin
*/

# ------------------------------------------------------------------
#
#	vBulletin to SP Import Definition File
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
			$map['users']->table			= 'user';
			$map['users']->select			= '';
			$map['users']->where			= '';
			$map['users']->ID				= 'userid';
			$map['users']->user_login		= 'username';
			$map['users']->user_pass		= 'password';
			$map['users']->user_nicename	= 'username';
			$map['users']->user_email		= 'email';
			$map['users']->user_url			= 'homepage';
			$map['users']->user_registered	= '#timestamp:joindate';
			$map['users']->display_name		= 'username';

		# SFGROUPS Table Mappings

		$map['tables']->sfgroups			= true;
			$map['sfgroups']->table			= 'forum';
			$map['sfgroups']->select		= 'SELECT * FROM <%dbname%>.<%dbbasepfix%>forum WHERE parentid = -1 ORDER BY forumid';
												# Need a SELECT since vBulletin groups are denoted with a -1
												#   in the parentid field in the forum table.
			$map['sfgroups']->where			= 'WHERE parentid = -1';
			$map['sfgroups']->group_id		= 'forumid';
			$map['sfgroups']->group_name	= 'title_clean';
			$map['sfgroups']->group_desc	= 'description_clean';
			$map['sfgroups']->group_seq		= 'displayorder';

		# SFFORUMS Table Mappings

		$map['tables']->sfforums			= true;
			$map['sfforums']->table			= 'forum';
			$map['sfforums']->select		= 'SELECT * FROM <%dbname%>.<%dbbasepfix%>forum WHERE parentid <> -1 ORDER BY forumid';
												# Need a SELECT to get all the forums that are not a group.
			$map['sfforums']->where			= 'WHERE parentid <> -1';
			$map['sfforums']->forum_id		= 'forumid';
			$map['sfforums']->forum_name 	= 'title_clean';
			$map['sfforums']->group_id 		= 'parentid';
			$map['sfforums']->forum_seq 	= 'displayorder';
			$map['sfforums']->forum_desc 	= 'description_clean';
			$map['sfforums']->forum_slug 	= 'title_clean';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'thread';
			$map['sftopics']->select		= 'SELECT threadid, title, dateline, NOT(open) AS open, forumid, postuserid, sticky, views, lastpostid
												FROM <%dbname%>.<%dbbasepfix%>thread WHERE forumid = <%forum_id%> ORDER BY threadid';
												# Need SELECT since the vBulletin 'open' column is 1=open, 0=closed,
												#    but SP 'topic_status' is 1=close, 0=open.
			$map['sftopics']->topic_id		= 'threadid';
			$map['sftopics']->topic_name	= 'title';
			$map['sftopics']->topic_date	= '#timestamp:dateline';
			$map['sftopics']->topic_status	= 'open';
			$map['sftopics']->forum_id		= 'forumid';
			$map['sftopics']->user_id		= 'postuserid';
			$map['sftopics']->topic_pinned	= 'sticky';
			$map['sftopics']->topic_opened	= 'views';
			$map['sftopics']->topic_slug	= 'title';
			$map['sftopics']->post_id		= 'lastpostid';

		# SFPOSTS Table Mappings

		$map['tables']->sfposts				= true;
			$map['sfposts']->table			= 'post';
			$map['sfposts']->select			= '';
			$map['sfposts']->post_id		= 'postid';
			$map['sfposts']->topic_id		= 'threadid';
			$map['sfposts']->forum_id		= '#query:SELECT forumid FROM <%dbname%>.<%dbuserpfix%>thread WHERE threadid = <%topic_id%>';
			$map['sfposts']->post_content 	= 'pagetext';
			$map['sfposts']->post_date		= '#timestamp:dateline';
			$map['sfposts']->user_id		= 'userid';
			$map['sfposts']->post_index		= '#default:0';
			$map['sfposts']->poster_ip		= 'ipaddress';

		# USERMETA Table Mappings

		$map['tables']->usermeta			= false; # Needs improvement. May not be working.
			$map['usermeta']->nickname		= '#query:SELECT username FROM <%dbname%>.<%dbuserpfix%>user WHERE userid = <%user_id%>';
			$map['usermeta']->description	= '#query:SELECT field1 FROM <%dbname%>.<%dbuserpfix%>userfield WHERE userid = <%user_id%>';
			$map['usermeta']->aim			= '#query:SELECT aim FROM <%dbname%>.<%dbuserpfix%>user WHERE userid = <%user_id%>';
			$map['usermeta']->yim			= '#query:SELECT yahoo FROM <%dbname%>.<%dbuserpfix%>user WHERE userid = <%user_id%>';


	update_option('spi-map', $map);

	# ------------------------------------------------------------------
	# Form: vBulletin
	# ------------------------------------------------------------------

	$required = explode(',', $def->required);
	$bullet = '<img src="'.SPI_URL.'importers/logos/bullet.png" alr="" />';
	$icon = '<td>'.$bullet.'</td>';
	$noicon = '<td></td>';

	?>
	<div id="spiForm" class="spiMainPanel">
	<h3>Before you start...</h3>
	<p>In vBulletin, the sub forums are structured differently in the database than how Simple:Press is structured.
	   If a vBulletin sub forum is imported using the Simple:Press Importing tool, they will not show up in the Simple:Press forum or
	   admin panel because this difference makes it difficult to handle sub forums.</p>
	<p>In order to work around the issue, one of two things can be done.  The easiest thing to do is change the sub forums in vBulletin
	   to normal forums before the import, then change them back to sub forums in Simple:Press after.
	   The other slightly more technical solution is to modify the WordPress database after the import if nothing has been changed beforehand.
	   This requires going into the sfforums table of the WordPress database and editing any sub forum's group_id to the appropriate group
	   (usually the same group_id as its parent).</p>
	<p>During the import, sub forums will inherit the forum_id of its parent instead of a legitimate group_id - which is why they do not show
	   up in the forum or the Simple:Press admin panel.  After editing the table, the sub forums should show up as normal forums and can
	   be changed back to sub forums in the Simple:Press admin panel.</p>


	<h3>Source Forum Data: vBulletin</h3>
	<h4>To perform the import we require access to the source vBulletin database. Please supply the following:</h4>
	<form name="vBulletin2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=vbulletin">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="vbulletin.php"

		<p><b><?php echo($bullet); ?>&nbsp;&nbsp;Required Data</b></p>

		<table id="sourcedata" class="form-table">

			<tr valign="top">
				<th scope="row"><label for="dbname">vBulletin Database Name:</label></th>
				<?php if(in_array('dbname', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbname" name="dbname" value="<?php echo($source->dbname); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuser">vBulletin Database User Name:</label></th>
				<?php if(in_array('dbuser', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuser" name="dbuser" value="<?php echo($source->dbuser); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpass">vBulletin Database Password:</label></th>
				<?php if(in_array('dbpass', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbpass" name="dbpass" value="<?php echo($source->dbpass); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbhost">vBulletin Database Host:</label></th>
				<?php if(in_array('dbhost', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbhost" name="dbhost" value="<?php echo($source->dbhost); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbbasepfix">vBulletin Table Prefix:</label></th>
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
	<br /><br />
	<p>Many thanks to Logan Gold at <a href='http://www.flexsim.com'>Flexsim Software Products, Inc.</a> for this vBulletin Import Script</p>

	</div>
	<?php
}

?>