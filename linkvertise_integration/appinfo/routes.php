<?php

declare(strict_types=1);

/**
 * App routes.
 *
 * - api#generateLink   -> creates/reuses a public share for {fileId} and returns the
 *                         Linkvertise-monetized URL for it.
 * - settings#setAdminConfig -> persists the admin settings form (API key, user id, ...).
 */
return [
	'routes' => [
		[
			'name' => 'api#generateLink',
			'url' => '/api/generate/{fileId}',
			'verb' => 'POST',
			'requirements' => ['fileId' => '\d+'],
		],
		[
			'name' => 'settings#setAdminConfig',
			'url' => '/settings/admin',
			'verb' => 'POST',
		],
	],
];
