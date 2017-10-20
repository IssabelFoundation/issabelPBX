function cdr_play(row_num, link) {
	var i = 0;
	var playbackId = "CURRENT_MSG";
	var file=encodeURIComponent(link)
	var cmTable = document.getElementById('cdr_table');
	// Only one playback row is allowed to be open at a time.
	// If one is already open, close it.
	for (i = 0; i < cmTable.rows.length; i++) {
		if (cmTable.rows[i].id == playbackId) {
			// Delete the row; it's a Playback control row.
			cmTable.deleteRow(cmTable.rows[i].rowIndex);
		}
	}
	// Make our Playback row.
	playback_src = "<iframe width='100%' height='25px' marginheight='0' marginwidth='0' frameborder='0' scrolling='no' src=" + link + "></iframe>";
	newRow = cmTable.insertRow(row_num);
	newRow.id = playbackId;
	cell_left = newRow.insertCell(0);
	cell_left.colSpan = 15;
	cell_left.innerHTML = playback_src;
}