<!--
  - SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcModal v-if="show"
		label-id="form-name"
		@close="() => $emit('update:show', false)">
		<div class="modal__content">
			<h2 id="form-name">{{ t('settings', 'Advanced deploy options') }}</h2>
			<p class="description" style="text-align: center;">
				{{ t('settings', 'Edit ExApp deploy options before installation') }}
			</p>

			<h3 v-if="environmentVariables.length > 0">{{ t('settings', 'Environment variables') }}</h3>
			<div v-for="envVar in environmentVariables" :key="envVar.envName"
				class="deploy-option">
				<NcTextField :label="envVar.displayName" :value.sync="deployOptions.environment_variables[envVar.envName]" />
				<p class="description">{{ envVar.description }}</p>
			</div>


			<h3>{{ t('settings', 'Mounts') }}</h3>
			<p class="description">{{ t('settings', 'Define host folder mounts to bind to ExApp container') }}</p>
			<p class="warning">{{ t('settings', 'Must exist prior to installing the ExApp') }}</p>

			<div v-for="mount in deployOptions.mounts"
				class="deploy-option"
				style="display: flex; align-items: center; justify-content: space-between; flex-direction: row;">
				<NcTextField :label="t('settings', 'Host path')" :value.sync="mount.hostPath" />
				<NcTextField :label="t('settings', 'Container path')" :value.sync="mount.containerPath" />
				<NcCheckboxRadioSwitch :checked.sync="mount.readonly">
					{{ t('settings', 'Read-only') }}
				</NcCheckboxRadioSwitch>
				<NcButton
					:aria-label="t('settings', 'Remove mount')"
					style="margin-top: 6px;"
					@click="removeMount(mount)">
					<template #icon>
						<NcIconSvgWrapper :path="mdiDelete" />
					</template>
				</NcButton>
			</div>

			<div v-if="addingMount" class="deploy-option">
				<h4>{{ t('settings', 'New mount') }}</h4>
				<div style="display: flex; align-items: center; justify-content: space-between; flex-direction: row;">
					<NcTextField
						:label="t('settings', 'Host path')"
						:aria-label="t('settings', 'Enter path to host folder')"
						:value.sync="newMountPoint.hostPath" />
					<NcTextField
						:label="t('settings', 'Container path')"
						:aria-label="t('settings', 'Enter path to container folder')"
						:value.sync="newMountPoint.containerPath" />
					<NcCheckboxRadioSwitch
						:checked.sync="newMountPoint.readonly"
						:aria-label="t('settings', 'Toggle read-only mode')">
						{{ t('settings', 'Read-only') }}
					</NcCheckboxRadioSwitch>
				</div>
				<div style="display: flex; align-items: center; margin-top: 4px;">
					<NcButton
						:aria-label="t('settings', 'Confirm adding new mount')"
						@click="addMountPoint">
						<template #icon>
							<NcIconSvgWrapper :path="mdiCheck" />
						</template>
						{{ t('settings', 'Confirm') }}
					</NcButton>
					<NcButton
						:aria-label="t('settings', 'Cancel adding mount')"
						style="margin-left: 4px;"
						@click="cancelAddMountPoint">
						<template #icon>
							<NcIconSvgWrapper :path="mdiClose" />
						</template>
						{{ t('settings', 'Cancel') }}
					</NcButton>
				</div>
			</div>
			<NcButton
				v-if="!addingMount"
				:aria-label="t('settings', 'Add mount')"
				style="margin-top: 5px;"
				@click="() => addingMount = true">
				<template #icon>
					<NcIconSvgWrapper :path="mdiPlus" />
				</template>
				{{ t('settings', 'Add mount') }}
			</NcButton>


			<h3>{{ t('settings', 'Port bindings') }}</h3>
			<p class="description">{{ t('settings', 'Define ports to expose from ExApp container') }}</p>

			<div v-for="port in deployOptions.ports"
				class="deploy-option"
				style="display: flex; align-items: center; justify-content: space-between; flex-direction: row;">
				<NcTextField :label="t('settings', 'Host port')" :value.sync="port.hostPort" />
				<NcTextField :label="t('settings', 'Host IP')" :value.sync="port.hostIp" />
				<NcTextField :label="t('settings', 'Container port')" :value.sync="port.containerPort" />
				<NcButton
					:aria-label="t('settings', 'Remove port binding')"
					style="margin-top: 6px;"
					@click="removePortBinding(port)">
					<template #icon>
						<NcIconSvgWrapper :path="mdiDelete" />
					</template>
				</NcButton>
			</div>

			<div v-if="addingPortBinding" class="deploy-option">
				<h4>{{ t('settings', 'New port binding') }}</h4>
				<div style="display: flex; align-items: center; justify-content: space-between; flex-direction: column; width: 100%;">
					<NcTextField
						:label="t('settings', 'Host port (e.g. 80, 443, 80/tcp, 443/udp')"
						:aria-label="t('settings', 'Enter host port (e.g. 80, 443, 80/tcp, 443/udp')"
						:value.sync="newPortBinding.hostPort" />
					<NcTextField
						:label="t('settings', 'Optional Host IP (e.g. 0.0.0.0, 127.0.0.1)')"
						:aria-label="t('settings', 'Optional: Enter host IP (e.g. 0.0.0.0, 127.0.0.1)')"
						:value.sync="newPortBinding.hostIp" />
					<NcTextField
						:label="t('settings', 'Port inside container (e.g. 8080)')"
						:aria-label="t('settings', 'Enter port inside container (e.g. 8080)')"
						:value.sync="newPortBinding.containerPort" />
				</div>
				<div style="display: flex; align-items: center; margin-top: 4px;">
					<NcButton
						:aria-label="t('settings', 'Confirm adding new port binding')"
						@click="addPortBinding">
						<template #icon>
							<NcIconSvgWrapper :path="mdiCheck" />
						</template>
						{{ t('settings', 'Confirm') }}
					</NcButton>
					<NcButton
						:aria-label="t('settings', 'Cancel adding port binding')"
						style="margin-left: 4px;"
						@click="cancelAddPortBinding">
						<template #icon>
							<NcIconSvgWrapper :path="mdiClose" />
						</template>
						{{ t('settings', 'Cancel') }}
					</NcButton>
				</div>
			</div>
			<NcButton
				v-if="!addingPortBinding"
				:aria-label="t('settings', 'Add port binding')"
				style="margin-top: 5px;"
				@click="() => addingPortBinding = true">
				<template #icon>
					<NcIconSvgWrapper :path="mdiPlus" />
				</template>
				{{ t('settings', 'Add port binding') }}
			</NcButton>


			<NcButton v-if="!app.active && (app.canInstall || app.isCompatible)"
				:title="enableButtonTooltip"
				:aria-label="enableButtonTooltip"
				type="primary"
				:disabled="!app.canInstall || installing || isLoading || !defaultDeployDaemonAccessible || isInitializing || isDeploying"
				style="margin-top: 10px;"
				@click.stop="() => {
					enable(app.id, deployOptions)
					$emit('update:show', false)
				}">
				{{ enableButtonText }}
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { computed, ref } from 'vue'

import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcIconSvgWrapper from '@nextcloud/vue/dist/Components/NcIconSvgWrapper.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

import { mdiPlus, mdiCheck, mdiClose, mdiDelete } from '@mdi/js'

import { useAppApiStore } from '../../store/app-api-store.ts'
import { useAppsStore } from '../../store/apps-store.ts'

import AppManagement from '../../mixins/AppManagement.js'

export default {
	'name': 'AppDeployOptionsModal',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcCheckboxRadioSwitch,
		NcIconSvgWrapper,
	},
	mixins: [AppManagement],
	props: {
		app: {
			type: Object,
			required: true,
		},
		show: {
			type: Boolean,
			required: true,
		},
	},
	setup(props) {
		// for AppManagement mixin
		const store = useAppsStore()
		const appApiStore = useAppApiStore()

		const environmentVariables = computed(() => {
			if (props.app?.releases?.length === 1) {
				return props.app?.releases[0]?.environmentVariables || []
			}
			return []
		})

		const deployOptions = ref({
			environment_variables: environmentVariables.value.reduce((acc, envVar) => {
				acc[envVar.envName] = envVar.default || ''
				return acc
			}, {}),
			mounts: [],
			ports: [],
		})

		return {
			environmentVariables,
			deployOptions,
			store,
			appApiStore,
			mdiPlus,
			mdiCheck,
			mdiClose,
			mdiDelete,
		}
	},
	data() {
		return {
			addingMount: false,
			newMountPoint: {
				hostPath: '',
				containerPath: '',
				readonly: false,
			},
			addingPortBinding: false,
			newPortBinding: {
				hostPort: '',
				hostIp: '',
				containerPort: '',
			},
		}
	},
	methods: {
		addMountPoint() {
			this.deployOptions.mounts.push(this.newMountPoint)
			this.newMountPoint = {
				hostPath: '',
				containerPath: '',
				readonly: false,
			}
			this.addingMount = false
		},
		cancelAddMountPoint() {
			this.newMountPoint = {
				hostPath: '',
				containerPath: '',
				readonly: false,
			}
			this.addingMount = false
		},
		removeMount(mountToRemove) {
			this.deployOptions.mounts = this.deployOptions.mounts.filter(mount => mount !== mountToRemove)
		},
		addPortBinding() {
			this.deployOptions.ports.push(this.newPortBinding)
			this.newPortBinding = {
				hostPort: '',
				hostIp: '',
				containerPort: '',
			}
			this.addingPortBinding = false
		},
		cancelAddPortBinding() {
			this.newPortBinding = {
				hostPort: '',
				hostIp: '',
				containerPort: '',
			}
			this.addingPortBinding = false
		},
		removePortBinding(portToRemove) {
			this.deployOptions.ports = this.deployOptions.ports.filter(port => port !== portToRemove)
		},
	},
}
</script>

<style scoped>
.modal__content {
	margin: 40px;
}

.modal__content h2 {
	text-align: center;
}

.deploy-option {
	margin: calc(var(--default-grid-baseline) * 4) 0;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}

.description {
	margin-top: 4px;
	font-size: 0.8em;
	color: var(--color-text-lighter);
}
</style>
