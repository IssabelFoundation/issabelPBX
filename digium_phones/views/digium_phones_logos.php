<h2>Logos</h2>
<hr />

<script type="text/javascript">
<?php

// table of phone models & logo sizes
$phone_models=array(
	'd40' => array('name' => 'D40', 'size' => '150x45'),
	'd45' => array('name' => 'D45', 'size' => '150x45'),
	'd50' => array('name' => 'D50', 'size' => '150x45'),
	'd70' => array('name' => 'D70', 'size' => '205x85'),
);

function phone_model_options() {
	global $phone_models;
	foreach ($phone_models as $model => $data) {
		echo '<option value="'.$model.'">'.$data['name'].'</option>'."\n";
	}
}



// we need our logo stash
$logos = $digium_phones->get_logos();

$js_logos= json_encode($logos);
echo "var logos = ". $js_logos. ";\n";
?>

function add_logo_clicked()
{
	$('#diveditlogo').slideUp('fast');
	$('#divaddlogo').slideToggle('fast');
}
function edit_logo_clicked(id)
{
	$('#divaddlogo').slideUp('fast');
	if ('undefined' == typeof id) { // cancel button
		$('#diveditlogo').slideUp('fast');
	} else {
		$('#diveditlogo').slideDown('fast');
	}

	$('#edit_logo_name').val(logos[id]['name']);
	$('#edit_logo_model').val(logos[id]['model']);
	$('#edit_logo_id').val(logos[id]['id']);
}
</script>

<?php
// deal with uploaded images
if (isset($_GET['logo_upload']) && isset($_FILES['logo_upload']) && $_FILES['logo_upload']['size'] > 0) {
	$tmp_file = digium_phones_sanitize_filepath($_FILES['logo_upload']['tmp_name']);
	$filename = basename(digium_phones_sanitize_filepath($_GET['logo_upload']));

	// we need to get the id for our new logo
	foreach ($logos as $logo) {
		// original upload
		if ($_POST['logo_name'] == $logo['name']) {
			$filename = $logo['id'];
		}
		// edit upload
		if ($_POST['edit_logo_name'] == $logo['name']) {
			$filename = $logo['id'];
		}
	}

	if (empty($phone_models[$_POST['logo_model']]['size'])) {
		echo '<h3 style="color: red;">Error: unknown phone model</h3>';
		return;
	}
	$size=$phone_models[$_POST['logo_model']]['size'];
	$dest = $amp_conf['ASTETCDIR'].'/digium_phones/user_image_'.$filename.'.png';
	system('convert '.$tmp_file.' -resize '.$size.' '.$dest);
	unlink($tmp_file);


}


?>

<table style="border-collapse:collapse; border-style:outset; border-width: 1px; margin-bottom: 20px;">
	<tr>
		<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Logo Name<span>A Logo's named identifier.</span></a></th>
		<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Phone Model<span>The Digium phone model to which this logo may apply</span></a></th>
		<th style="border-style:inset; border-width:1px; width: 210px; "><a href="#" class="info">Image<span>A preview of the logo image</span></a></th>
		<th style="border-style:inset; border-width:1px; "><a href="#" class="info">Actions<span>"Edit" provides additional editing control over a selected logo. "Delete" removes the specified logo.</span></a></th>
	</tr>
<?php
if (empty($logos)) {
?>
	<tr>
		<td colspan="4">No custom logos configured.</td>
	</tr>
<?php
}
foreach ($logos as $logo) {
?>
	<tr>
		<td><?php echo $logo['name']?></td>
		<td><?php echo strtoupper($logo['model'])?></td>
<?php
	if (file_exists("{$amp_conf['ASTETCDIR']}/digium_phones/user_image_{$logo['id']}.png")) {
?>
		<td><img src="config.php?type=setup&display=digium_phones&user_image=<?php echo $logo['id']?>&quietmode=1" /></td>
<?php
	} else {
?>
		<td>not available</td>
<?php
	}
?>
		<td>
			<button type="button" onclick="edit_logo_clicked(<?php echo $logo['id']?>);">Edit</button>

			<form name="digium_phones_logos" method="post" action="config.php?type=setup&display=digium_phones&digium_phones_form=logos_edit&deletepng=<?php echo $logo['id']?>">
				<input type="submit" value="Delete">
			</form>
		</td>
	</tr>
<?php
}
?>
</table>

<?php
exec('which convert 2>/dev/null', $out, $rc);
if ($rc) {
?>
	<h3 style="color: red;">Error: ImageMagick package must be installed to upload or edit logos.</h3><br />
<?php
	return;
}
?>

<input type="submit" name="add_logo_submit" value="Add Logo" onclick="add_logo_clicked();"/>

<div id="divaddlogo" style="display: none;">
	<hr style="margin-top: 30px;"/>
	<h2>Add New Logo</h2>
	<form name="digium_phones_logos" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=logos_edit&logo_upload=yes">
		<table style="border-spacing: 4px;">
			<tr>
				<td><a href="#" class="info">Logo Name<span>A named identifier for the Logo.</span></a></td>
				<td><input type="text" id="logo_name" name="logo_name" /></td>
			</tr>
			<tr>
				<td><a href="#" class="info">Phone Model<span>Select the Digium phone model which can use this logo.</span></a></td>
				<td>
					<select id="logo_model" name="logo_model"><?php phone_model_options() ?></select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><input type="file" name="logo_upload" /></td>
			</tr>
			<tr>
				<td colspan="2"> 
					<input type="submit" name="logo_upload_submit" value="Upload"/>
					<button type="button" onclick="add_logo_clicked();">Cancel</button>
				</td>
			</tr>
		</table>
	</form>
</div>


<div id="diveditlogo" style="display: none;">
	<hr style="margin-top: 30px;"/>
	<h2>Edit Logo</h2>
	<form name="digium_phones_edit_logos" method="post" enctype="multipart/form-data" action="config.php?type=setup&display=digium_phones&digium_phones_form=logos_edit&logo_upload=yes">
		<table style="border-spacing: 4px">
			<tr>
				<td><a href="#" class="info">Logo Name<span>Named identifier for the Logo.</span></a></td>
				<td><input type="text" id="edit_logo_name" name="edit_logo_name" /></td>
			</tr>
			<tr>
				<td><a href="#" class="info">Phone Model<span>Select the Digium phone model which can use this logo.  Logo files should be PNG format, 8-bit, no transparency, and less than 10k in file size.  Dimensions for D40 and D50 logos: 150x45 pixels.  Dimensions for D70 logos: 205x85 pixels.</span></a></td>
				<td>
					<select id="edit_logo_model" name="edit_logo_model">
					<?php phone_model_options() ?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="hidden" id="edit_logo_id" name="edit_logo_id"/>
					<input type="file" id="logo_upload" name="logo_upload" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" name="edit_logo_upload_submit" value="Save"/>
					<button type="button" onclick="edit_logo_clicked();">Cancel</button>
				</td>
			</tr>
		</table>
	</form>
</div>
