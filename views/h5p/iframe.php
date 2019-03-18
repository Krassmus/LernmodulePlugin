<!doctype html>
<html lang="<?php print $lang; ?>" class="h5p-iframe">
<head>
    <meta charset="utf-8">
    <title><?php print $content['title']; ?></title>
    <? foreach ($scripts as $script): ?>
        <script src="<?= $script ?>"></script>
    <? endforeach ?>
    <? foreach ($styles as $style): ?>
        <link rel="stylesheet" href="<?= $style ?>">
    <? endforeach ?>
    <style>
        .h5p-actions {
            display: none;
        }
    </style>
    <?php if (!empty($additional_embed_head_tags)): print implode("\n", $additional_embed_head_tags); endif; ?>
</head>
<body>
<div class="h5p-content" data-content-id="<?= htmlReady($content['id']) ?>"></div>
<script>
    H5PIntegration = <?= json_encode($integration) ?>;
</script>
</body>
</html>
