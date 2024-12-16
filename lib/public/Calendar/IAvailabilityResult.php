<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCP\Calendar;

/**
 * The result of an availability check for a specific attendee.
 *
 * @since 31.0.0
 */
interface IAvailabilityResult {
	/**
	 * Get the email address of the corresponding attendee.
	 *
	 * @since 31.0.0
	 */
	public function getEmailAddress(): string;

	/**
	 * Whether the attendee is available during the requested time span.
	 *
	 * @since 31.0.0
	 */
	public function isAvailable(): bool;
}
