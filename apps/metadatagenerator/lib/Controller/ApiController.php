<?php
declare(strict_types=1);

namespace OCA\MetadataGenerator\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use Psr\Log\LoggerInterface;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

class ApiController extends Controller {
    private $rootFolder;
    private $userId;
    private $logger;

    public function __construct(
        string $appName,
        IRequest $request,
        IRootFolder $rootFolder,
        ?string $userId,
        LoggerInterface $logger
    ) {
        parent::__construct($appName, $request);
        $this->rootFolder = $rootFolder;
        $this->userId = $userId;
        $this->logger = $logger;
    }

    /**
     * Get the folder structure for the current user, including folders and files.
     *
     * @NoCSRFRequired
     * @param string $path The path to the folder (default: '/').
     * @return DataResponse JSON response with the folder structure and files.
     */
    public function getFolderStructure(string $path = '/'): DataResponse {
        $path = rtrim($path, '/'); // Ensure no trailing slashes

        try {
            $userFolder = $this->rootFolder->getUserFolder($this->userId);
            $targetFolder = $path === '/' ? $userFolder : $userFolder->get($path);

            if (!$targetFolder instanceof \OCP\Files\Folder) {
                throw new \Exception('Invalid folder path: Not a folder');
            }

            $folders = [];
            $files = [];
            
            foreach ($targetFolder->getDirectoryListing() as $item) {
                if ($item instanceof \OCP\Files\Folder) {
                    $folders[] = [
                        'name' => $item->getName(),
                        'path' => $item->getPath(),
                    ];
                } elseif ($item instanceof \OCP\Files\File) {
                    $files[] = [
                        'name' => $item->getName(),
                        'path' => $item->getPath(),
                    ];
                }
            }

            return new DataResponse(['folders' => $folders, 'files' => $files]);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching folder structure', ['exception' => $e->getMessage()]);
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Save the XML file to the selected Nextcloud folder.
     *
     * @param string $folder Path to the folder.
     * @param string $content XML content.
     * @return DataResponse JSON response with the save status.
     */
    public function save(string $folder, string $content): DataResponse {
        try {
            $this->logger->info("Saving file to folder: $folder, user: {$this->userId}");
            $userFolder = $this->rootFolder->getUserFolder($this->userId);
            $targetFolder = $folder === '/' ? $userFolder : $userFolder->get($folder);

            if (!$targetFolder instanceof \OCP\Files\Folder) {
                throw new \Exception('Invalid folder path: Not a folder');
            }

            $fileName = 'metadata.xml';
            $file = $targetFolder->newFile($fileName);
            $file->putContent($content);

            $this->logger->info("File saved successfully: $folder/$fileName");
            return new DataResponse(['message' => 'File saved successfully']);
        } catch (\Exception $e) {
            $this->logger->error('Error saving file', [
                'exception' => $e->getMessage(),
                'folder' => $folder,
                'userId' => $this->userId
            ]);
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get content of a file.
     *
     * @NoCSRFRequired
     * @param string $path The path to the file.
     * @return DataResponse JSON response with the file content.
     */
    public function getFileContent(string $path): DataResponse {
        try {
            $userFolder = $this->rootFolder->getUserFolder($this->userId);
            $file = $userFolder->get($path);
            
            if (!$file instanceof \OCP\Files\File) {
                throw new \Exception('Invalid file path: Not a file');
            }
    
            $content = $file->getContent(); // Get file content
            
            return new DataResponse(['content' => $content]);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching file content', ['exception' => $e->getMessage()]);
            return new DataResponse(['error' => $e->getMessage()], 400);
        }
    }
}
