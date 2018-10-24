<?php
/*
NAME	: Simple Machines V2 Importer
FILE	: simplemachines-v2.php
ICON	: simplemachines-v2.png
INFO	: Imports Users, Groups, Forums, Topics and Posts
TAG		: Simple Machines (version 2)
*/

# ------------------------------------------------------------------
#
#	Simple Machines Version 2 to SP Import Definition File
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
			$map['users']->table			= 'members';
			$map['users']->select			= '';
			$map['users']->where			= '';
			$map['users']->ID				= 'id_member';
			$map['users']->user_login		= 'member_name';
			$map['users']->user_pass		= 'passwd';
			$map['users']->user_nicename	= 'real_name';
			$map['users']->user_email		= 'email_address';
			$map['users']->user_url			= 'website_url';
			$map['users']->user_registered	= '#timestamp:date_registered';
			$map['users']->display_name		= 'real_name';

		# SFGROUPS Table Mappings

		$map['tables']->sfgroups			= true;
			$map['sfgroups']->table			= 'categories';
			$map['sfgroups']->select		= '';
			$map['sfgroups']->where			= '';
			$map['sfgroups']->group_id		= 'id_cat';
			$map['sfgroups']->group_name	= 'name';
			$map['sfgroups']->group_desc	= '';
			$map['sfgroups']->group_seq		= 'cat_order';

		# SFFORUMS Table Mappings

		$map['tables']->sfforums			= true;
			$map['sfforums']->table			= 'boards';
			$map['sfforums']->select		= '';
			$map['sfforums']->where			= '';
			$map['sfforums']->forum_id		= 'id_board';
			$map['sfforums']->forum_name 	= 'name';
			$map['sfforums']->group_id 		= 'id_cat';
			$map['sfforums']->forum_seq 	= 'board_order';
			$map['sfforums']->forum_desc 	= 'description';
			$map['sfforums']->forum_slug 	= 'name';

		# SFTOPICS Table Mappings

		$map['tables']->sftopics			= true;
			$map['sftopics']->table			= 'topics';
			$map['sftopics']->select		= '';
			$map['sftopics']->topic_id		= 'id_topic';
			$map['sftopics']->topic_name	= '#query:SELECT subject FROM <%dbname%>.<%dbbasepfix%>messages WHERE id_topic = <%topic_id%> ORDER BY id_msg LIMIT 1';
			$map['sftopics']->topic_date	= '';
			$map['sftopics']->topic_status	= 'locked';
			$map['sftopics']->forum_id		= 'id_board';
			$map['sftopics']->user_id		= 'id_member_started';
			$map['sftopics']->topic_pinned	= 'is_sticky';
			$map['sftopics']->topic_opened	= 'num_views';
			$map['sftopics']->topic_slug	= '#query:SELECT subject FROM <%dbname%>.<%dbbasepfix%>messages WHERE id_topic = <%topic_id%> ORDER BY id_msg LIMIT 1';
			$map['sftopics']->post_id		= 'id_last_msg';

		# SFPOSTS Table Mappings

		$map['tables']->sfposts				= true;
			$map['sfposts']->table			= 'messages';
			$map['sfposts']->select			= '';
			$map['sfposts']->post_id		= 'id_msg';
			$map['sfposts']->topic_id		= 'id_topic';
			$map['sfposts']->forum_id		= 'id_board';
			$map['sfposts']->post_content 	= 'body';
			$map['sfposts']->post_date		= '#timestamp:poster_time';
			$map['sfposts']->user_id		= 'id_member';
			$map['sfposts']->post_index		= '#default:0';
			$map['sfposts']->poster_ip		= 'poster_ip';

		# USERMETA Table Mappings

		$map['tables']->usermeta			= false;
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
	<h3>Source Forum Data: Simple Machines V2</h3>
	<form name="smf2spimport" method="post" action="<?php echo(admin_url()); ?>admin.php?page=sp-import/admin/spimport-setup.php&sys=simplemachines">

		<input type="hidden" id="s2" name="s2" value="s2" />
		<input type="hidden" id="selectsource" name="selectsource" value="simplemachines-v2.php"

		<p><b><?php echo($bullet); ?>&nbsp;&nbsp;Required Data</b></p>

		<table id="sourcedata" class="form-table">

			<tr valign="top">
				<th scope="row"><label for="dbname">SMF Database Name:</label></th>
				<?php if(in_array('dbname', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbname" name="dbname" value="<?php echo($source->dbname); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbuser">SMF Database User Name:</label></th>
				<?php if(in_array('dbuser', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbuser" name="dbuser" value="<?php echo($source->dbuser); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbpass">SMF Database Password:</label></th>
				<?php if(in_array('dbpass', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbpass" name="dbpass" value="<?php echo($source->dbpass); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbhost">SMF Database Host:</label></th>
				<?php if(in_array('dbhost', $required)) {echo $icon;} else {echo $noicon;} ?>
				<td><input type="text" class="regular-text" id="dbhost" name="dbhost" value="<?php echo($source->dbhost); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="dbbasepfix">SMF Table Prefix:</label></th>
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