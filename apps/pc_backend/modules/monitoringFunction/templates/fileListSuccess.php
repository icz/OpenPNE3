<?php slot('submenu') ?>
<?php include_component('monitoringFunction', 'subMenu') ?>
<?php end_slot() ?>

<?php slot('title', __('アップロードファイルリスト')) ?>

<?php if (!$pager->getNbResults()): ?>
<?php echo __('アップロードファイルは存在しません。') ?></p>
<?php else: ?>
<?php
$params = array();
$params['uri'] = url_for('monitoringFunction/fileList');
$params['method'] = 'get';
$params['title'] = __('表示件数');
$params['params'] = array(20, 50, 100, 500);
$params['unit'] = '件';
$params['name'] = 'size';
include_partial('global/changePageSize', array('params' => $params));
?>

<p><?php op_include_pager_navigation($pager, 'monitoringFunction/fileList?page=%d') ?></p>
<div class="fileListTable">
<?php include_partial('fileInfo', array('files' => $pager->getResults(), 'deleteBtn' => true)) ?>
</div>
<br class="clear"/>
<p><?php op_include_pager_navigation($pager, 'monitoringFunction/fileList?page=%d') ?></p>
<?php endif; ?>
