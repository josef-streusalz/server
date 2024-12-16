<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Calendar;

use OCP\Calendar\IAvailabilityResult;

class AvailabilityResult implements IAvailabilityResult {
	public function __construct(
		private readonly string $email,
		private readonly bool $available,
	) {
	}

	public function getEmailAddress(): string {
		return $this->email;
	}

	public function isAvailable(): bool {
		return $this->available;
	}
}
