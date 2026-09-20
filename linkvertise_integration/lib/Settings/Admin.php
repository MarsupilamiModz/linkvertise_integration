<?php

declare(strict_types=1);

namespace OCA\LinkvertiseIntegration\Settings;

use OCA\LinkvertiseIntegration\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\Util;

/**
 * Renders the "Linkvertise" admin settings form (templates/admin.php),
 * which is just a mount point for the Vue app bundled in
 * js/linkvertise-admin-settings.js.
 */
class Admin implements ISettings {
	public function __construct(
		private IConfig $config,
		private IInitialState $initialState,
	) {
	}

	public function getForm(): TemplateResponse {
		// NOTE: we deliberately do NOT send the stored API key back to the
		// browser - only whether one is configured. The key is only ever
		// overwritten when the admin submits a new, non-empty value.
		$this->initialState->provideInitialState('admin-config', [
			'apiKeyConfigured' => $this->config->getAppValue(Application::APP_ID, 'api_key', '') !== '',
			'userId' => $this->config->getAppValue(Application::APP_ID, 'user_id', ''),
			'forceDownloadSuffix' => $this->config->getAppValue(Application::APP_ID, 'force_download_suffix', '0') === '1',
		]);

		Util::addScript(Application::APP_ID, 'linkvertise-admin-settings');

		return new TemplateResponse(Application::APP_ID, 'admin', [], '');
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 10;
	}
}
