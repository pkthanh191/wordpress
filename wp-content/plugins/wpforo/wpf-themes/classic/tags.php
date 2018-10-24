<?php
	
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

$args = array(); $items_count = 0;
if(!empty($_GET['wpfpaged'])) $paged = intval($_GET['wpfpaged']);
$args['offset'] = ($paged - 1) * WPF()->post->options['tags_per_page'];
$args['row_count'] = WPF()->post->options['tags_per_page'];
$tags = WPF()->topic->get_tags($args, $items_count);
?>
<div class="wpforo-tags-wrap">
    <div class="wpf-head-bar">
         <h1 id="wpforo-title" style="padding-bottom:0px; margin-bottom:0px;">
            <?php wpforo_phrase('Topic Tags') ?>
         </h1>
    </div>
    <div class="wpforo-tags-content wpfr-tags wpf-tags">
        <?php if( WPF()->post->options['tags'] ): ?>
            <?php if( !empty($tags) ): ?>
                <?php foreach( $tags as $tag ): ?>
                    <tag><a href="<?php echo wpforo_home_url() . '?wpfin=tag&wpfs=' . $tag['tag'] ?>"><?php echo esc_html($tag['tag']); ?><?php if( $tag['count'] ) echo ' &nbsp;[' . $tag['count'] . ']&nbsp;'; ?></a></tag>
                <?php endforeach ?>
            <?php else: ?>
                <p class="wpf-p-error"><?php wpforo_phrase('No tags found') ?>  </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="wpf-p-error"><?php wpforo_phrase('Tags are disabled') ?>  </p>
        <?php endif; ?>
        <div class="wpf-clear"></div>
    </div>
    <div class="wpf-snavi">
    <?php WPF()->tpl->pagenavi($paged, $items_count, FALSE); ?>
    </div>
</div>