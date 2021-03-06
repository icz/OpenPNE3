<?php
$options->addRequiredOption('pager');
$options->addRequiredOption('pager_url');
$options->addRequiredOption('item_url');

$options->setDefault('image_filename_method', 'getImageFilename');
$options->setDefault('show_images', true);
?>

<?php op_include_pager_navigation($options->getRaw('pager'), $options->getRaw('pager_url')); ?>

<div class="item"><table><tbody>
<?php foreach ($options->getRaw('pager')->getResults() as $item): ?>

<?php
$customizeOption = array('id' => $item->getId());
$getImageFilename = $options->image_filename_method;
?>

<tr>
<?php if ($options->show_images) : ?>
<?php include_customizes('id_photo', 'before', $customizeOption) ?>
<td class="photo">
<?php echo link_to(image_tag_sf_image($item->$getImageFilename(), array('size' => '76x76')), $options->item_url, $item); ?><br />
<?php echo link_to((string)$item, $options->item_url, $item) ?>
</td>
<?php include_customizes('id_photo', 'after', $customizeOption) ?>
<?php endif; ?>

<?php include_customizes('id_friend', 'before', $customizeOption) ?>
<?php foreach ($options->getRaw('menus') as $menu) : ?>
<?php if (!empty($menu['url'])): ?>
<?php if (op_have_privilege_by_uri($menu['url'], $item)): ?>
<td<?php echo !empty($menu['class']) ? ' class="'.$menu['class'].'"' : ''; ?>>
<?php echo link_to($menu['text'], $menu['url'], $item) ?>
</td>
<?php else: ?>
<td>&nbsp;</td>
<?php endif; ?>
<?php else: ?>
<td<?php echo !empty($menu['class']) ? ' class="'.$menu['class'].'"' : ''; ?>>
<?php echo $menu['text']; ?>
</td>
<?php endif; ?>
<?php endforeach; ?>
<?php include_customizes('id_friend', 'after', $customizeOption) ?>
</tr>
<?php endforeach; ?>
</tbody></table></div>

<?php op_include_pager_navigation($options->getRaw('pager'), $options->getRaw('pager_url')); ?>
