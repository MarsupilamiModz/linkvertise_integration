<template>
	<NcSettingsSection :name="t('linkvertise_integration', 'Linkvertise')"
		:description="t('linkvertise_integration', 'Verbinde deinen Linkvertise-Account, um öffentliche Freigabelinks automatisch zu monetarisieren.')">
		<form @submit.prevent="save">
			<NcTextField v-model="apiKey"
				type="password"
				:label="t('linkvertise_integration', 'Linkvertise API-Schlüssel')"
				:placeholder="apiKeyPlaceholder" />

			<NcTextField v-model="userId"
				:label="t('linkvertise_integration', 'User ID')" />

			<NcCheckboxRadioSwitch v-model="forceDownloadSuffix">
				{{ t('linkvertise_integration', 'Download-Suffix erzwingen (/download anhängen)') }}
			</NcCheckboxRadioSwitch>

			<p class="linkvertise-settings__hint">
				{{ t('linkvertise_integration', 'Hängt /download an den Freigabelink an, damit der Download beim Öffnen sofort startet.') }}
			</p>

			<NcButton type="primary" native-type="submit" :disabled="saving">
				{{ saving ? t('linkvertise_integration', 'Speichern …') : t('linkvertise_integration', 'Speichern') }}
			</NcButton>

			<NcNoteCard v-if="savedMessage" type="success" class="linkvertise-settings__note">
				{{ savedMessage }}
			</NcNoteCard>
			<NcNoteCard v-if="errorMessage" type="error" class="linkvertise-settings__note">
				{{ errorMessage }}
			</NcNoteCard>
		</form>
	</NcSettingsSection>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import { translate as t } from '@nextcloud/l10n'

import NcSettingsSection from '@nextcloud/vue/dist/Components/NcSettingsSection.js'
import NcTextField from '@nextcloud/vue/dist/Components/NcTextField.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'

const initial = loadState('linkvertise_integration', 'admin-config')

export default {
	name: 'AdminSettings',

	components: {
		NcSettingsSection,
		NcTextField,
		NcCheckboxRadioSwitch,
		NcButton,
		NcNoteCard,
	},

	data() {
		return {
			apiKey: '',
			apiKeyConfigured: initial.apiKeyConfigured,
			userId: initial.userId,
			forceDownloadSuffix: initial.forceDownloadSuffix,
			saving: false,
			savedMessage: '',
			errorMessage: '',
		}
	},

	computed: {
		apiKeyPlaceholder() {
			return this.apiKeyConfigured
				? t('linkvertise_integration', '•••••••• (gesetzt – leer lassen, um ihn beizubehalten)')
				: t('linkvertise_integration', 'API-Schlüssel eingeben')
		},
	},

	methods: {
		t,

		async save() {
			this.saving = true
			this.savedMessage = ''
			this.errorMessage = ''

			try {
				const response = await axios.post(generateUrl('/apps/linkvertise_integration/settings/admin'), {
					apiKey: this.apiKey,
					userId: this.userId,
					forceDownloadSuffix: this.forceDownloadSuffix,
				})

				this.apiKeyConfigured = response.data.apiKeyConfigured
				this.apiKey = ''
				this.savedMessage = t('linkvertise_integration', 'Einstellungen gespeichert')
			} catch (error) {
				console.error('Linkvertise: saving settings failed', error)
				this.errorMessage = t('linkvertise_integration', 'Einstellungen konnten nicht gespeichert werden')
			} finally {
				this.saving = false
			}
		},
	},
}
</script>

<style scoped lang="scss">
.linkvertise-settings__hint {
	color: var(--color-text-maxcontrast);
	margin-bottom: 12px;
}

.linkvertise-settings__note {
	margin-top: 12px;
	max-width: 400px;
}
</style>
