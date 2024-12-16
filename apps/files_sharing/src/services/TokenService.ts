/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'

import logger from './logger.ts'

interface TokenData {
	ocs: {
		data: {
			token: string,
		}
	}
}

const { shareapi_token_length: tokenLength } = loadState('core', 'config', { shareapi_token_length: 15 })

export const generateToken = async (): Promise<string> => {
	try {
		const { data } = await axios.get<TokenData>(generateOcsUrl('/apps/files_sharing/api/v1/token'))
		return data.ocs.data.token
	} catch (error) {
		logger.error('Failed to get token from server, falling back to client-side generation', { error })

		const humanReadableChars = 'abcdefgijkmnopqrstwxyzABCDEFGHJKLMNPQRSTWXYZ23456789'
		const array = new Uint8Array(tokenLength)
		const ratio = humanReadableChars.length / 255
		window.crypto.getRandomValues(array)
		const token = array.reduce((previousValue, value, index) => {
			return previousValue + humanReadableChars.charAt(array[index] * ratio)
		}, '')
		return token
	}
}
