<?php

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Core\Controller;

use OC\Core\Controller\ConversionApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\Conversion\IConversionManager;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ConversionApiControllerTest extends TestCase {
	/** @var ConversionApiController */
	private $conversionApiController;

	/** @var IRequest|MockObject */
	private $request;

	/** @var IConversionManager|MockObject */
	private $conversionManager;

	/** @var IRootFolder|MockObject */
	private $rootFolder;

	/** @var File|MockObject */
	private $file;

	/** @var Folder|MockObject */
	private $userFolder;

	/** @var string */
	private $user;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->conversionManager = $this->createMock(IConversionManager::class);
		$this->file = $this->createMock(File::class);
		$this->user = 'userid';

		$this->userFolder = $this->createMock(Folder::class);
		$this->rootFolder = $this->createMock(IRootFolder::class);
		$this->rootFolder->method('getUserFolder')->with($this->user)->willReturn($this->userFolder);

		$this->conversionApiController = new ConversionApiController(
			'core',
			$this->request,
			$this->conversionManager,
			$this->rootFolder,
			$this->user,
		);
	}

	public function testThrowsNotFoundException() {
		$this->expectException(OCSNotFoundException::class);
		$this->conversionApiController->convert(42, 'image/png');
	}

	public function testThrowsOcsException() {
		$this->userFolder->method('getFirstNodeById')->with(42)->willReturn($this->file);
		$this->conversionManager->method('convert')->willThrowException(new \Exception());

		$this->expectException(OCSException::class);
		$this->conversionApiController->convert(42, 'image/png');
	}

	public function testConvert() {
		$this->userFolder->method('getFirstNodeById')->with(42)->willReturn($this->file);
		$this->conversionManager->method('convert')->with($this->file, 'image/png')->willReturn('files/test.png');

		$actual = $this->conversionApiController->convert(42, 'image/png');
		$expected = new DataResponse([
			'path' => 'files/test.png',
		], Http::STATUS_CREATED);

		$this->assertEquals($expected, $actual);
	}
}
