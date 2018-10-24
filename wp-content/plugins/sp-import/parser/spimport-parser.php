<?php

function spi_parse_bbcode($text) {

	# BBCode to find...
	$in = array('/\[b\](.*?)\[\/b\]/ms',
				'/\[i\](.*?)\[\/i\]/ms',
				'/\[u\](.*?)\[\/u\]/ms',
                '/\[left\](.*?)\[\/left\]/ms',
                '/\[right\](.*?)\[\/right\]/ms',
                '/\[center\](.*?)\[\/center\]/ms',
				'/\[img\](.*?)\[\/img\]/ms',
				'/\[url\="?(.*?)"?\](.*?)\[\/url\]/is',
   			    '/\[url\](.*?)\[\/url\]/is',
				'/\[quote\](.*?)\[\/quote\]/ms',
				'/\[quote\="?(.*?)"?\](.*?)\[\/quote\]/ms',
				'/\[list\=(.*?)\](.*?)\[\/list\]/ms',
				'/\[list\](.*?)\[\/list\]/ms',
				'/\[B\](.*?)\[\/B\]/ms',
				'/\[I\](.*?)\[\/I\]/ms',
				'/\[U\](.*?)\[\/U\]/ms',
                '/\[LEFT\](.*?)\[\/LEFT\]/ms',
                '/\[RIGHT\](.*?)\[\/RIGHT\]/ms',
                '/\[CENTER\](.*?)\[\/CENTER\]/ms',
				'/\[IMG\](.*?)\[\/IMG\]/ms',
				'/\[COLOR=(.*?)](.*?)\[\/COLOR]/is',
				'/\[URL\="?(.*?)"?\](.*?)\[\/URL\]/is',
				'/\[QUOTE\](.*?)\[\/QUOTE\]/ms',
				'/\[QUOTE\="?(.*?)"?\](.*?)\[\/QUOTE\]/ms',
				'/\[LIST\=(.*?)\](.*?)\[\/LIST\]/ms',
				'/\[LIST\](.*?)\[\/LIST\]/ms',
				'/\[\*\]\s?(.*?)\n/ms'
	);

	# And replace them by...
	$out = array('<strong>\1</strong>',
				'<em>\1</em>',
				'<u>\1</u>',
                '<div style="text-align:left">\1</div>',
                '<div style="text-align:right">\1</div>',
                '<div style="text-align:center">\1</div>',
				'<img src="\1" alt="\1" />',
				'<a href="\1">\2</a>',
   	  		    '\1',
				'<blockquote>\1</blockquote>',
				'<blockquote>\1 said:<br />\2</blockquote>',
				'<ol start="\1">\2</ol>',
				'<ul>\1</ul>',
				'<strong>\1</strong>',
				'<em>\1</em>',
				'<u>\1</u>',
                '<div style="text-align:left">\1</div>',
                '<div style="text-align:right">\1</div>',
                '<div style="text-align:center">\1</div>',
				'<img src="\1" alt="\1" />',
				'<span style="color: \1">\2</span>',
				'<a href="\1">\2</a>',
				'<blockquote>\1</blockquote>',
				'<blockquote>\1 said:<br />\2</blockquote>',
				'<ol start="\1">\2</ol>',
				'<ul>\1</ul>',
				'<li>\1</li>'
	);
	$text = preg_replace($in, $out, $text);

	# special case for nested quotes
	$text = str_replace('[quote]', '<blockquote>', $text);
	$text = str_replace('[/quote]', '</blockquote>', $text);

	return $text;
}

?>