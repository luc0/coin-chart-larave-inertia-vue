<?php

namespace App\Support;

use App\Dto\GithubCommitsDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

// DOCS: https://docs.github.com/en/rest/commits/commits?apiVersion=2022-11-28#get-a-commit
class GithubAPI
{
    private const API_URL = 'https://api.github.com';
    private const API_VERSION_HEADER = '2022-11-28';
    private const ACCEPT_HEADER = 'application/vnd.github+json';
    private const PER_PAGE = 100; // This is the max permitted
//    private $coinIds = [];

    function __construct()
    {
//        $this->coinIds = $this->getCoinsId();
    }

    // TODO: recorrer todas las pages.
    public function getCommitsResponse(string $owner, string $repository, ?string $since = null, ?int $currentPage = 1): GithubCommitsResponse
    {
        $response = Http::withToken('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') // TODO: token en .env del server (expira en 30d)
            ->get($this->getCommitsEndpoint($owner, $repository), [
            'since' => $since ? Carbon::parse($since)->toIso8601ZuluString() : null,
            'X-GitHub-Api-Version' => self::API_VERSION_HEADER,
            'Accept' => self::ACCEPT_HEADER,
            'per_page' => self::PER_PAGE,
            'page' => $currentPage,
        ]);

        return new GithubCommitsResponse($response);
    }

    public function getAllCommitsData(string $owner, string $repository, ?string $since = null, ?int $currentPage = 1): GithubCommitsDTO
    {
        $hasMorePages = true;

        $commitsDTO = new GithubCommitsDTO();

        $githubCommitsResponse = $this->getCommitsResponse($owner, $repository, $since, $currentPage);
        $commitsDTO->addResponse($githubCommitsResponse);

        do {
            if ($githubCommitsResponse->areRemainingDataInOtherPages(self::PER_PAGE)) {
                $githubCommitsResponse = $this->getCommitsResponse($owner, $repository, $since, ++$currentPage);
                $commitsDTO->addResponse($githubCommitsResponse);
            } else {
                $hasMorePages = false;
            }

        } while ($hasMorePages && $currentPage <= 3);

        return $commitsDTO;
    }

    private function getCommitsEndpoint($owner, $repo): string
    {
        return self::API_URL . '/repos/' . $owner . '/'. $repo .'/commits';
    }

//    private function getCoinUuid($coin): string
//    {
//        return $this->coinIds[$coin];
//    }
}