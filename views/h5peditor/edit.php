<?php
/**
 * Add new H5P Content.
 *
 * @package   H5P
 * @author    Joubel <contact@joubel.com>
 * @license   MIT
 * @link      http://joubel.com
 * @copyright 2014 Joubel
 */
?>

<form method="post" enctype="multipart/form-data" id="h5p-content-form">
    <input type="hidden" name="library" value="<?= $library ?: 0 ?>"/>
    <input type="hidden" name="parameters" value="<?= htmlReady(json_encode($params)) ?>"/>
    <div id="post-body-content">
        <div class="h5p-create">
            <div class="h5p-editor"><?= dgettext("lernmoduleplugin","Warte auf Javascript") ?></div>
        </div>
    </div>
    <?= \Studip\Button::create(_("Erstellen")) ?>
</form>

<script>H5PIntegration = <?= json_encode($integration) ?>;</script>
