<?php

declare(strict_types=1);

namespace OCA\LinkvertiseIntegration\Controller;

use OCA\LinkvertiseIntegration\AppInfo\Application;
use OCA\LinkvertiseIntegration\Settings\Admin;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;

/**
 * Persists the values submitted by the Vue admin settings form.
 */
class SettingsController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * The AuthorizedAdminSetting attribute ties this endpoint to the Admin
	 * settings page, so only users allowed to open that settings page
	 * (i.e. admins) may call it - no manual permission check needed.
	 */
	#[AuthorizedAdminSetting(settings: Admin::class)]
	public function setAdminConfig(
		?string $apiKey = null,
		string $userId = '',
		bool $forceDownloadSuffix = false,
	): DataResponse {
		// Only overwrite the stored API key if the admin actually entered a
		// new value - the frontend never receives (and can therefore never
		// accidentally resubmit an empty/blanked-out) secret.
		if ($apiKey !== null && $apiKey !== '') {
			$this->config->setAppValue(Application::APP_ID, 'api_key', $apiKey);
		}

		$this->config->setAppValue(Application::APP_ID, 'user_id', $userId);
		$this->config->setAppValue(
			Application::APP_ID,
			'force_download_suffix',
			$forceDownloadSuffix ? '1' : '0',
		);

		return new DataResponse([
			'apiKeyConfigured' => $this->config->getAppValue(Application::APP_ID, 'api_key', '') !== '',
		]);
	}
}
