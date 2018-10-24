
function spjPerformImport(phpUrl, userPhase, forumPhase, nextPhase, image)
{
	try {
		/* If first time in - load up message strings and initialise progress */
		if(nextPhase == 1)
		{
			/* display importing message */
			jQuery('#spiWaitZone').html('<span><p><img src="' + image + '" />&nbsp;&nbsp;&nbsp;Import in Progress<br />If your source forum is large this may take some time...<br /></p></span>');
			jQuery('#spiWaitZone').fadeIn('slow');
		}


		/* do next phase */
		var target = "#spiImportZone";
		var done = false;
		if(nextPhase <= userPhase) var thisUrl = phpUrl + '?action=userPhase&phase=' + nextPhase;
		if((nextPhase > userPhase) && (nextPhase <= (forumPhase + userPhase))) var thisUrl = phpUrl + '?action=forumPhase&phase=' + (nextPhase-userPhase);
		if(nextPhase == (userPhase + forumPhase + 1)) var thisUrl = phpUrl + '?action=tagPhase';
		if(nextPhase == (userPhase + forumPhase + 2)) var thisUrl = phpUrl + '?action=countPhase';
		if(nextPhase == (userPhase + forumPhase + 3))
		{
			var thisUrl = phpUrl + '?action=cleanPhase';
			done = true;
		}

		var currentHTML = jQuery(target).html();

		jQuery(target).load(thisUrl, function(a, b) {
			/* check for errors first */
			var retVal = a.substr(0,12);
			jQuery(target).html(currentHTML + a);
			jQuery(target).fadeIn('slow');

			if(retVal == 'Import Error')
			{
				jQuery('#spiWaitZone').html('<p>Import has failed</p>');
				return;
			}

			nextPhase ++;

			/* are we finished yet */
			if(done)
			{
//				jQuery('#spiEndZone').html('<h3>Import Complete</h3>');
				jQuery('#spiEndZone').fadeIn('slow');
				jQuery('#spiWaitZone').html('<p>Finished</p>');
				return;
			} else {
				spjPerformImport(phpUrl, userPhase, forumPhase, nextPhase, image);
			}
		});
	}

	catch(e) {
		jQuery("#spiWaitZone").html('<p>PROBLEM - The Import can not be completed</p>');
		var abortMsg = "<p>There is a problem with the JavaScript being loaded on this page which is stopping the import from being completed.<br />";
		abortMsg += "The error being reported is: " + e.message + '</p>';
		jQuery("#spiEndZone").html(abortMsg);
		jQuery("#spiEndZone").show();
	}
}
