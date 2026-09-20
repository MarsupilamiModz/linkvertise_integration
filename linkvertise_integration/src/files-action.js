import { registerFileAction, FileAction, Permission } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'
import { showSuccess, showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// Simple link/coin icon, inlined so no extra asset request is needed.
const ICON_SVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
	<path fill="currentColor" d="M7 7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1S5.29 8.9 7 8.9h4V7Zm2 4h6v2H9Zm8-4h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1S18.71 15.1 17 15.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5Z" />
</svg>`

const copyToClipboard = async (text) => {
	try {
		await navigator.clipboard.writeText(text)
		return true
	} catch (e) {
		return false
	}
}

registerFileAction(new FileAction({
	id: 'linkvertise-generate-link',
	displayName: () => t('linkvertise_integration', 'Linkvertise-Link generieren'),
	iconSvgInline: () => ICON_SVG,

	// Only offer the action for a single, shareable file (not folders/multi-select).
	enabled(nodes) {
		return nodes.length === 1
			&& nodes[0].type === 'file'
			&& (nodes[0].permissions & Permission.SHARE) !== 0
	},

	async exec(node) {
		try {
			const url = generateUrl('/apps/linkvertise_integration/api/generate/{fileId}', {
				fileId: node.fileid,
			})
			const response = await axios.post(url)
			const { linkvertiseUrl } = response.data

			const copied = await copyToClipboard(linkvertiseUrl)
			showSuccess(
				copied
					? t('linkvertise_integration', 'Linkvertise-Link wurde in die Zwischenablage kopiert')
					: t('linkvertise_integration', 'Linkvertise-Link erstellt: {url}', { url: linkvertiseUrl }, undefined, { escape: false }),
			)

			return true
		} catch (error) {
			console.error('Linkvertise: link generation failed', error)
			const message = error?.response?.data?.message
			showError(
				message
					? t('linkvertise_integration', 'Linkvertise-Link konnte nicht erstellt werden: {message}', { message })
					: t('linkvertise_integration', 'Linkvertise-Link konnte nicht erstellt werden'),
			)
			return false
		}
	},

	order: 25,
}))
