<?php
use OCP\Util;
$appId = 'metadatagenerator';

Util::addScript($appId, $appId . '-main');
Util::addStyle($appId, 'style');
?>

<div id="app-content">
    <div class="container">
        <h1>Metadata Generator</h1>
        <div id="keyvalue-fields">
            <div class="field">
                <input type="text" placeholder="Key" class="key">
                <input type="text" placeholder="Value" class="value">
            </div>
        </div>
        <button id="add-field">Add Field</button>
        <button id="generate">Generate XML</button>
        <button id="save">Save as XML</button>
        <button id="browse-folder">Browse Folder</button>
        <h2>Generated XML</h2>
        <pre id="output"></pre>

        <!-- Folder Browser Modal -->
        <div id="folder-browser-modal" style="display: none;">
            <div id="folder-container"></div>
            <p id="current-folder-path">Selected Folder: /</p>
            <button id="back-button">Back</button>
            <!--<button id="select-folder-button">Select Current Folder</button>-->
            <button id="save-to-nextcloud">Save to Nextcloud</button>
            <button id="close-modal">Close</button>
        </div>
    </div>
</div>

