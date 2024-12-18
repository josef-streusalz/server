<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Testing\Conversion;

use OCP\Conversion\ConversionMimeTuple;
use OCP\Conversion\IConversionProvider;
use OCP\Files\File;

class ConversionProvider implements IConversionProvider {

	public function getName(): string {
		return 'testing';
	}

	public function getSupportedMimeTypes(): array {
		$jpegConversions = new ConversionMimeTuple('image/jpeg', [
			'image/png',
		]);

		return [$jpegConversions];
	}

	public function convertFile(File $file, string $targetMimeType): mixed {
		$image = imagecreatefromstring($file->getContent());

		imagepalettetotruecolor($image);

		ob_start();
		imagepng($image);
		return ob_get_clean();
	}
}
