<?php

declare(strict_types=1);

namespace OCA\MetadataGenerator\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class PageController extends Controller {
    public function __construct(string $appName, IRequest $request) {
        parent::__construct($appName, $request);
    }

    /**
     * The main page of the app.
     *
     * @return TemplateResponse
     */
    #[NoCSRFRequired] // Disable CSRF check for this route
    #[NoAdminRequired] // Ensure no admin privileges are required


    public function main(): TemplateResponse {
        error_log("Metadata Generator app route accessed");
        error_log("PageController::main() accessed"); // Logs to the PHP error log
        return new TemplateResponse('metadatagenerator', 'main');
    }
}

