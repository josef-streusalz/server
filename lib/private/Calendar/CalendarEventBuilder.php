<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Calendar;

use DateTimeInterface;
use OCP\Calendar\ICalendarEventBuilder;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;

class CalendarEventBuilder implements ICalendarEventBuilder {
	private ?DateTimeInterface $startDate = null;
	private ?DateTimeInterface $endDate = null;
	private ?string $summary = null;
	private ?string $description = null;
	private ?array $organizer = null;
	private array $attendees = [];

	public function setStartDate(DateTimeInterface $start): void {
		$this->startDate = $start;
	}

	public function setEndDate(DateTimeInterface $end): void {
		$this->endDate = $end;
	}

	public function setSummary(string $summary): void {
		$this->summary = $summary;
	}

	public function setDescription(string $description): void {
		$this->description = $description;
	}

	public function setOrganizer(string $email, ?string $commonName = null): void {
		$this->organizer = [$email, $commonName];
	}

	public function addAttendee(string $email, ?string $commonName = null): void {
		$this->attendees[] = [$email, $commonName];
	}

	public function toIcs(): ?string {
		if ($this->startDate === null || $this->endDate === null || $this->summary === null) {
			return null;
		}

		if ($this->organizer === null && $this->attendees !== []) {
			return null;
		}

		$vcalendar = new VCalendar();
		$props = [
			'SUMMARY' => $this->summary,
			'DTSTART' => $this->startDate,
			'DTEND' => $this->endDate,
		];
		if ($this->description !== null) {
			$props['DESCRIPTION'] = $this->description;
		}
		/** @var VEvent $vevent */
		$vevent = $vcalendar->add('VEVENT', $props);
		if ($this->organizer !== null) {
			self::addAttendeeToVEvent($vevent, 'ORGANIZER', $this->organizer);
		}
		foreach ($this->attendees as $attendee) {
			self::addAttendeeToVEvent($vevent, 'ATTENDEE', $attendee);
		}
		return $vcalendar->serialize();
	}

	/**
	 * @param string[] $tuple A tuple of [$email, $commonName] where $commonName may be null.
	 */
	private static function addAttendeeToVEvent(VEvent $vevent, string $name, array $tuple): void {
		[$email, $cn] = $tuple;
		if (!str_starts_with($email, 'mailto:')) {
			$email = "mailto:$email";
		}
		$params = [];
		if ($cn !== null) {
			$params['CN'] = $cn;
		}
		$vevent->add($name, $email, $params);
	}
}
