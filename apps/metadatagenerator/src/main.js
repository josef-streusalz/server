document.addEventListener('DOMContentLoaded', () => {
    const fieldsContainer = document.getElementById('keyvalue-fields');
    const output = document.getElementById('output');
    const folderModal = document.getElementById('folder-browser-modal');
    const folderContainer = document.getElementById('folder-container');
    const currentFolderPath = document.getElementById('current-folder-path');
    const selectFolderButton = document.getElementById('select-folder-button');
    const saveToNextcloudButton = document.getElementById('save-to-nextcloud');
    const closeModalButton = document.getElementById('close-modal');
    const browseFolderButton = document.getElementById('browse-folder');
    const saveLocalButton = document.getElementById('save');

    let selectedFolder = '/';

    // Add key-value fields dynamically
    function addField() {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'field';

        const keyInput = document.createElement('input');
        keyInput.placeholder = 'Key';
        keyInput.className = 'key';

        const valueInput = document.createElement('input');
        valueInput.placeholder = 'Value';
        valueInput.className = 'value';

        fieldDiv.appendChild(keyInput);
        fieldDiv.appendChild(valueInput);

        fieldsContainer.appendChild(fieldDiv);
    }

    // Generate XML content from key-value fields
    function generateXML() {
        const fields = document.querySelectorAll('.field');
        let xml = '<?xml version="1.0" encoding="UTF-8"?>\n<resource>';

        fields.forEach(field => {
            const key = field.querySelector('.key').value.trim();
            const value = field.querySelector('.value').value.trim();
            if (key && value) {
                xml += `\n\t<${key}>${value}</${key}>`;
            }
        });

        xml += '\n</resource>';
        output.textContent = xml;
    }

    // Save XML content locally
    function saveAsXML() {
        const xmlContent = output.textContent;

        if (!xmlContent.trim()) {
            alert('No XML content to save.');
            return;
        }

        const blob = new Blob([xmlContent], { type: 'application/xml' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'metadata.xml';
        link.click();
    }

    // Load folder structure for browsing
    async function loadFolderStructure(path = '/') {
        try {
            folderContainer.innerHTML = '<p>Loading...</p>'; // Show a loading message
    
            // Sanitize the path to ensure no redundancy (remove '/admin/files/' if it already exists)
            if (path.startsWith('/admin/files/')) {
                path = path.substring('/admin/files/'.length); // Remove '/admin/files/' from the start of the path
            }
    
            const response = await fetch(`/index.php/apps/metadatagenerator/api/folder-structure?path=${encodeURIComponent(path)}`);
    
            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const errorText = await response.text(); // Get the actual error content
                console.error('Server returned non-JSON response:', errorText);
                throw new Error('Invalid response from server: Expected JSON');
            }
    
            const data = await response.json();
    
            if (data.error) {
                throw new Error(data.error);
            }
    
            folderContainer.innerHTML = ''; // Clear previous content
            if (data.folders && data.folders.length > 0) {
                data.folders.forEach(folder => {
                    const folderDiv = document.createElement('div');
                    folderDiv.className = 'folder-item';
                    folderDiv.textContent = folder.name;
                    folderDiv.dataset.path = folder.path;
                    folderDiv.addEventListener('click', () => {
                        selectedFolder = folder.path;
                        currentFolderPath.textContent = `Current Folder: ${selectedFolder}`;
                        loadFolderStructure(folder.path); // Navigate into the folder
                    });
                    folderContainer.appendChild(folderDiv);
                });
            } else {
                folderContainer.innerHTML = '<p>No folders available.</p>';
            }
        } catch (error) {
            console.error('Error loading folders:', error);
            folderContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        }
    }
    

    // Open the folder browser modal
    function openFolderBrowser() {
        folderModal.style.display = 'block';
        loadFolderStructure('/'); // Load root folder
    }

    // Close the folder browser modal
    function closeFolderBrowser() {
        folderModal.style.display = 'none';
    }

    // Save XML content to selected Nextcloud folder
    async function saveToNextcloud() {
        const xmlContent = output.textContent;

        if (!xmlContent.trim()) {
            alert('No XML content to save.');
            return;
        }

        if (!selectedFolder) {
            alert('No folder selected.');
            return;
        }

        try {
            const response = await fetch('/index.php/apps/metadatagenerator/api/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'OCS-APIREQUEST': 'true',
                },
                body: JSON.stringify({
                    folder: selectedFolder,
                    content: xmlContent,
                }),
            });
            const result = await response.json();
            if (result.message) {
                alert(result.message);
                closeFolderBrowser();
            } else {
                alert(result.error || 'Error saving file.');
            }
        } catch (error) {
            console.error('Error saving file:', error);
            alert('Error saving file.');
        }
    }

    // Event listeners
    document.getElementById('add-field').addEventListener('click', addField);
    document.getElementById('generate').addEventListener('click', generateXML);
    saveLocalButton.addEventListener('click', saveAsXML);
    browseFolderButton.addEventListener('click', openFolderBrowser);
    closeModalButton.addEventListener('click', closeFolderBrowser);
    selectFolderButton.addEventListener('click', () => {
        alert(`Selected Folder: ${selectedFolder}`);
        closeFolderBrowser();
    });
    saveToNextcloudButton.addEventListener('click', saveToNextcloud);
});
