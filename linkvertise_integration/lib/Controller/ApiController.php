<?php

declare(strict_types=1);

namespace OCA\LinkvertiseIntegration\Controller;

use OCA\LinkvertiseIntegration\AppInfo\Application;
use OCA\LinkvertiseIntegration\Service\LinkvertiseService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\Constants;
use OCP\Files\Node;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Share\IManager;
use OCP\Share\IShare;
use Psr\Log\LoggerInterface;

/**
 * Handles the "Linkvertise-Link generieren" file action: creates (or reuses)
 * a public read-only share link for a file and converts it into a
 * monetized Linkvertise URL.
 */
class ApiController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private IRootFolder $rootFolder,
		private IUserSession $userSession,
		private IManager $shareManager,
		private IURLGenerator $urlGenerator,
		private IConfig $config,
		private LinkvertiseService $linkvertiseService,
		private LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function generateLink(int $fileId): DataResponse {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return new DataResponse(['message' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$userFolder = $this->rootFolder->getUserFolder($user->getUID());
		$nodes = $userFolder->getById($fileId);
		if (empty($nodes)) {
			return new DataResponse(['message' => 'File not found or not accessible'], Http::STATUS_NOT_FOUND);
		}
		$node = $nodes[0];

		// 1. Make sure the current user is actually allowed to (re-)share this node.
		if (!($node->getPermissions() & Constants::PERMISSION_SHARE)) {
			return new DataResponse(['message' => 'You are not allowed to share this file'], Http::STATUS_FORBIDDEN);
		}

		// 2. Create (or reuse) a public, read-only link share for the file.
		try {
			$share = $this->getOrCreatePublicLinkShare($node, $user->getUID());
		} catch (\Exception $e) {
			$this->logger->error('Linkvertise: failed to create public share', ['exception' => $e]);
			return new DataResponse(['message' => 'Could not create public share'], Http::STATUS_INTERNAL_SERVER_ERROR);
		}

		$shareUrl = $this->urlGenerator->linkToRouteAbsolute(
			'files_sharing.sharecontroller.showShare',
			['token' => $share->getToken()],
		);

		// 3. Optionally force the direct-download variant of the share link.
		if ($this->config->getAppValue(Application::APP_ID, 'force_download_suffix', '0') === '1') {
			$shareUrl = rtrim($shareUrl, '/') . '/download';
		}

		// 4 + 5. Convert the share URL into a monetized Linkvertise URL.
		try {
			$linkvertiseUrl = $this->linkvertiseService->monetize($shareUrl);
		} catch (\Exception $e) {
			$this->logger->error('Linkvertise: API request failed', ['exception' => $e]);
			return new DataResponse(
				['message' => 'Linkvertise API request failed: ' . $e->getMessage()],
				Http::STATUS_BAD_GATEWAY,
			);
		}

		// 6. Return the result to the frontend.
		return new DataResponse([
			'shareUrl' => $shareUrl,
			'linkvertiseUrl' => $linkvertiseUrl,
		]);
	}

	/**
	 * Reuses an existing public link share for this node when the user
	 * already created one, otherwise creates a new read-only link share.
	 */
	private function getOrCreatePublicLinkShare(Node $node, string $userId): IShare {
		$existingShares = $this->shareManager->getSharesBy($userId, IShare::SHARE_TYPE_LINK, $node, false, 1);
		if (!empty($existingShares)) {
			return $existingShares[0];
		}

		$share = $this->shareManager->newShare();
		$share->setNode($node);
		$share->setShareType(IShare::SHARE_TYPE_LINK);
		$share->setPermissions(Constants::PERMISSION_READ);
		$share->setSharedBy($userId);

		return $this->shareManager->createShare($share);
	}
}
