<?php
use OCP\Util;

$appId = \OCA\MetadataGenerator\AppInfo\Application::APP_ID;

Util::addScript($appId, $appId . '-main');
Util::addStyle($appId, 'style');
?>

<div id="app-content">
    <div class="container">
        <h1>Metadata Generator</h1>
        <form>
            <div class="form-group">
                <label class="tooltip">Sample Name
                    <span class="tooltiptext">Unique and concise identifier for lab use. Example: ISDsoil1</span>
                </label>
                <input type="text" name="sample_name" placeholder="Enter sample name">
            </div>
            <div class="form-group">
                <label class="tooltip">Sequencing Method
                    <span class="tooltiptext">Machine name, preferably from the OBI list. Example: 454 Genome Sequencer FLX [OBI:0000702]</span>
                </label>
                <input type="text" name="seq_meth" placeholder="Enter sequencing method">
            </div>
            <!-- Add additional fields here -->
            <button type="submit">Submit</button>
        </form>
    </div>
</div>
