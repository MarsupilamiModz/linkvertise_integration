<?php

declare(strict_types=1);

namespace OCA\LinkvertiseIntegration\Service;

use OCA\LinkvertiseIntegration\AppInfo\Application;
use OCP\Http\Client\IClientService;
use OCP\IConfig;

/**
 * Thin wrapper around the Linkvertise API.
 *
 * IMPORTANT: Linkvertise does not expose one single, universally documented
 * "convert this URL" REST endpoint - the exact path, payload shape and auth
 * scheme depend on which Linkvertise product/plan you have access to
 * (e.g. the Direct-Links API vs. a partner/reseller API). The request below
 * is a generic placeholder using the common pattern (Bearer token + JSON
 * body, `target_url` in, `url` out) shared by most HTTP link-shortener APIs.
 *
 * Adjust the three marked spots below to match the exact contract of your
 * Linkvertise endpoint:
 *   1. self::API_ENDPOINT
 *   2. the `json` payload keys sent in monetize()
 *   3. how the resulting URL is read out of the response body
 */
class LinkvertiseService {
	// TODO: replace with the real Linkvertise API endpoint for your plan.
	private const API_ENDPOINT = 'https://api.linkvertise.com/v1/links';

	public function __construct(
		private IClientService $clientService,
		private IConfig $config,
	) {
	}

	/**
	 * Converts a plain URL into a monetized Linkvertise URL.
	 *
	 * @throws \Exception when no API key is configured or the API call fails
	 */
	public function monetize(string $targetUrl): string {
		$apiKey = $this->config->getAppValue(Application::APP_ID, 'api_key', '');
		$userId = $this->config->getAppValue(Application::APP_ID, 'user_id', '');

		if ($apiKey === '') {
			throw new \Exception('No Linkvertise API key configured');
		}

		$client = $this->clientService->newClient();

		// --- 1/2: adjust endpoint + payload to match the real API contract ---
		$response = $client->post(self::API_ENDPOINT, [
			'headers' => [
				'Authorization' => 'Bearer ' . $apiKey,
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			],
			'json' => [
				'user_id' => $userId,
				'target_url' => $targetUrl,
			],
			'timeout' => 10,
		]);

		$body = json_decode($response->getBody(), true);
		// --- 3: adjust how the monetized URL is extracted from $body ---

		if (!is_array($body) || empty($body['url'])) {
			throw new \Exception('Unexpected response from Linkvertise API');
		}

		return (string)$body['url'];
	}
}
