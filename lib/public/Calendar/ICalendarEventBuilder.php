<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCP\Calendar;

use DateTimeInterface;

/**
 * The calendar event builder can be used to conveniently build a calendar event and then serialize
 * it to a ICS string. The ICS string can be submitted to calendar instances implementing the
 * \OCP\Calendar\ICreateFromString interface.
 *
 * @since 31.0.0
 */
interface ICalendarEventBuilder {
	/**
	 * Set the start date, time and time zone.
	 * This property is required!
	 *
	 * @since 31.0.0
	 */
	public function setStartDate(DateTimeInterface $start): void;

	/**
	 * Set the end date, time and time zone.
	 * This property is required!
	 *
	 * @since 31.0.0
	 */
	public function setEndDate(DateTimeInterface $end): void;

	/**
	 * Set the event summary or title.
	 * This property is required!
	 *
	 * @since 31.0.0
	 */
	public function setSummary(string $summary): void;

	/**
	 * Set the event description.
	 *
	 * @since 31.0.0
	 */
	public function setDescription(string $description): void;

	/**
	 * Set the event organizer.
	 * This property is required if attendees are added!
	 *
	 * @since 31.0.0
	 */
	public function setOrganizer(string $email, ?string $commonName = null): void;

	/**
	 * Add a new attendee to the event.
	 * Adding at least one attendee requires also setting the organizer!
	 *
	 * @since 31.0.0
	 */
	public function addAttendee(string $email, ?string $commonName = null): void;

	/**
	 * Serialize the built event to an ICS string if all required properties  set.
	 *
	 * @since 31.0.0
	 *
	 * @return string|null The serialized ICS string if all required properties have been set or null otherwise
	 */
	public function toIcs(): ?string;
}
