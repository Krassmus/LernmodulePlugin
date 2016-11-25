<script>
    window.H5PIntegration = {
        "baseUrl": "<?= substr($GLOBALS['ABSOLUTE_URI_STUDIP'], 0, -1) ?>", // No trailing slash
        "url": "/plugins_packages/RasmusFuhse/LernmodulePlugin/moduledata/<?= $mod->getId() ?>",          // Relative to web root
        "postUserStatistics": true,         // Only if user is logged in
        "ajaxPath": "/path/to/h5p-ajax"     // Only used by older Content Types
        "ajax": {
            // Where to post user results
            "setFinished": "/interactive-contents/123/results/new",
            // Words beginning with : are placeholders
            "contentUserData": "/interactive-contents/:contentId/user-data?data_type=:dataType&subContentId=:subContentId"
        },
        "saveFreq": 30, // How often current content state should be saved. false to disable.
        "user": { // Only if logged in !
            <? $user = User::findCurrent() ?>
            "name": "<?= htmlReady($user->getFullName()) ?>",
            "mail": "<?= htmlReady($user['email']) ?>"
        },
        "siteUrl": "http://www.mysite.com", // Only if NOT logged in!
        "l10n": { // Text string translations
            "H5P": {
                "fullscreen": "Fullscreen",
                "disableFullscreen": "Disable fullscreen",
                "download": "Download",
                "copyrights": "Rights of use",
                "embed": "Embed",
                "size": "Size",
                "showAdvanced": "Show advanced",
                "hideAdvanced": "Hide advanced",
                "advancedHelp": "Include this script on your website if you want dynamic sizing of the embedded content:",
                "copyrightInformation": "Rights of use",
                "close": "Close",
                "title": "Title",
                "author": "Author",
                "year": "Year",
                "source": "Source",
                "license": "License",
                "thumbnail": "Thumbnail",
                "noCopyrights": "No copyright information available for this content.",
                "downloadDescription": "Download this content as a H5P file.",
                "copyrightsDescription": "View copyright information for this content.",
                "embedDescription": "View the embed code for this content.",
                "h5pDescription": "Visit H5P.org to check out more cool content.",
                "contentChanged": "This content has changed since you last used it.",
                "startingOver": "You'll be starting over.",
                "by": "by",
                "showMore": "Show more",
                "showLess": "Show less",
                "subLevel": "Sublevel"
            }
        },
        "loadedJs": ['multichoice.js'], // Only required when Embed Type = div
        "loadedCss": [],
        "core": { // Only required when Embed Type = iframe
            "scripts": ['jquery.js', 'h5p.js', ...],
            "styles": ['h5p.css']
        }
    };
    window.H5PIntegration.contents['cid-1234'] = {
        "library": "H5P.MultiChoice 1.5", // Library name + major version.minor version
        "jsonContent": "{\"filtered start parameters in json format\"}",
        "fullScreen": false, // No fullscreen support
        "exportUrl": "/path/to/download.h5p",
        "embedCode": "<iframe src=\"https://mysite.com/h5p/1234/embed\" width=\":w\" height=\":h\" frameborder=\"0\" allowfullscreen=\"allowfullscreen\"></iframe>",
        "resizeCode": "<script src=\"https://mysite.com/h5p-resizer.js\" charset=\"UTF-8\"></script>",
        "mainId": 1234,
        "url": "https://mysite.com/h5p/1234",
        "title": "How long is a rope?",
        "contentUserData": {
            0: { // Sub ID (0 = main content/no id)
                'state' => FALSE // Data ID
            }
        },
        "disable": 0,  // Flags for disabling frame and action buttons
        "styles": ['multichoice.css'],
        "scripts" ['multichoice.js']
    };
</script>

<div class="h5p-content" data-content-id="1234"></div>

    <div class="h5p-iframe-wrapper">
        <iframe id="h5p-iframe-1234" class="h5p-iframe" data-content-id="1234" style="height:1px" src="about:blank" frameBorder="0" scrolling="no"></iframe>
    </div>