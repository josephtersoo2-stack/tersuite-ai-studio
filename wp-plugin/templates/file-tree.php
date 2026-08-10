<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="tersuite-file-tree">
<?php foreach (($tree ?? []) as $name => $children): ?>
    <div class="tersuite-tree-item"><strong><?php echo esc_html($name); ?></strong></div>
<?php endforeach; ?>
</div>
