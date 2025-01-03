document.addEventListener('DOMContentLoaded', () => {
    const fieldsContainer = document.getElementById('keyvalue-fields');
    const output = document.getElementById('output');
    const folderModal = document.getElementById('folder-browser-modal');
    const folderContainer = document.getElementById('folder-container');
    const currentFolderPath = document.getElementById('current-folder-path');
    const saveToNextcloudButton = document.getElementById('save-to-nextcloud');
    const closeModalButton = document.getElementById('close-modal');
    const browseFolderButton = document.getElementById('browse-folder');
    const saveLocalButton = document.getElementById('save');
    const backButton = document.getElementById('back-button'); // Add the back button
    const fileContentContainer = document.getElementById('file-content'); // Area to display file content
    const fileViewer = document.getElementById('file-viewer'); // Add this to display file content

    let selectedFolder = '/';
    let selectedFile = null; // Variable to store the selected file
    let folderHistory = [];  // History of visited folders

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

    // Load folder structure for browsing (Including files content)
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

            // Handle folders
            if (data.folders && data.folders.length > 0) {
                data.folders.forEach(folder => {
                    const folderDiv = document.createElement('div');
                    folderDiv.className = 'folder-item';
                    folderDiv.textContent = folder.name;
                    folderDiv.dataset.path = folder.path;
                    folderDiv.addEventListener('click', () => {
                        selectedFolder = folder.path;
                        folderHistory.push(path);  // Push current path to history before navigating
                        currentFolderPath.textContent = `Current Folder: ${selectedFolder}`;
                        loadFolderStructure(folder.path); // Navigate into the folder
                    });
                    folderContainer.appendChild(folderDiv);
                });
            } else {
                folderContainer.innerHTML = '<p>No folders available.</p>';
            }

            // Handle files
            if (data.files && data.files.length > 0) {
                data.files.forEach(file => {
                    const fileDiv = document.createElement('div');
                    fileDiv.className = 'file-item';
                    fileDiv.textContent = file.name;
                    fileDiv.dataset.path = file.path;
                    fileDiv.addEventListener('click', () => {
                        selectedFile = file.path; // Store the selected file
                        displayFileContent(file.path); // Display file content
                    });
                    folderContainer.appendChild(fileDiv);
                });
            } else {
                folderContainer.innerHTML += '<p>No files available in this folder.</p>';
            }
        } catch (error) {
            console.error('Error loading folders:', error);
            folderContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        }
    }

    // Display the content of a selected file
    async function displayFileContent(filePath) {
        try {
            const response = await fetch(`/index.php/apps/metadatagenerator/api/file-content?path=${encodeURIComponent(filePath)}`);

            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const errorText = await response.text();
                console.error('Server returned non-JSON response:', errorText);
                throw new Error('Invalid response from server: Expected JSON');
            }

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            // Display the file content in the designated container
            fileContentContainer.innerHTML = `<h3>File Content:</h3><pre>${data.content}</pre>`;
        } catch (error) {
            console.error('Error displaying file content:', error);
            fileContentContainer.innerHTML = `<p>Error: ${error.message}</p>`;
        }
    }

    // Back button functionality
    backButton.addEventListener('click', () => {
        if (folderHistory.length > 0) {
            const previousPath = folderHistory.pop();  // Get the last folder in the history
            loadFolderStructure(previousPath);  // Navigate back to the previous folder
        } else {
            alert('You are already at the root folder.');
        }
    });

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
            // Sanitize the path to ensure no redundancy (remove '/admin/files/' if it already exists)
            let sanitizedPath = selectedFolder;
            if (sanitizedPath.startsWith('/admin/files/')) {
                sanitizedPath = sanitizedPath.substring('/admin/files/'.length); // Remove '/admin/files/' from the start of the path
            }

            const response = await fetch('/index.php/apps/metadatagenerator/api/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'OCS-APIREQUEST': 'true',
                },
                body: JSON.stringify({
                    folder: sanitizedPath,  // Use the sanitized path here
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


    async function fetchFolderMetadata(path) {
        try {
            const response = await fetch(`/index.php/apps/metadatagenerator/api/get-metadata?path=${encodeURIComponent(path)}`);
            const data = await response.json();
    
            if (data.error) {
                console.error('Error fetching metadata:', data.error);
                return;
            }
    
            displayMetadata(data.metadata);
        } catch (error) {
            console.error('Error fetching metadata:', error);
        }
    }
    
    
    function displayMetadata(metadata) {
        const infoPanel = document.getElementById('folder-info-panel');
        if (!infoPanel) {
            console.error('Info panel not found!');
            return;
        }
    
        infoPanel.innerHTML = '<h3>Folder Metadata</h3>';
        if (metadata && Object.keys(metadata).length > 0) {
            Object.entries(metadata).forEach(([key, value]) => {
                const metaItem = document.createElement('p');
                metaItem.textContent = `${key}: ${value}`;
                infoPanel.appendChild(metaItem);
            });
        } else {
            infoPanel.innerHTML += '<p>No metadata available.</p>';
        }
    }
    
    
    // Example: Call fetchFolderMetadata when the folder is selected
    document.addEventListener('folderSelected', (event) => {
        const folderPath = event.detail.path; // Ensure event provides folder path
        fetchFolderMetadata(folderPath);
    });
    
    document.addEventListener('DOMContentLoaded', () => {
        // Locate the Nextcloud details panel (adjust selector if necessary)
        const detailsPanel = document.querySelector('.app-sidebar-content');
    
        if (detailsPanel) {
            // Create a new div for displaying metadata
            const metadataContainer = document.createElement('div');
            metadataContainer.id = 'folder-info-panel';
            metadataContainer.innerHTML = '<h3>Folder Metadata</h3>';
            detailsPanel.appendChild(metadataContainer);
        }
    });
    

    // Event listeners
    document.getElementById('add-field').addEventListener('click', addField);
    document.getElementById('generate').addEventListener('click', generateXML);
    saveLocalButton.addEventListener('click', saveAsXML);
    browseFolderButton.addEventListener('click', openFolderBrowser);
    closeModalButton.addEventListener('click', closeFolderBrowser);
    saveToNextcloudButton.addEventListener('click', saveToNextcloud);
});
