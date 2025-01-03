<?php

declare(strict_types=1);

namespace whikloj\BagItTools\Profiles;

use whikloj\BagItTools\CurlInstance;
use whikloj\BagItTools\Exceptions\ProfileException;

/**
 * Factory class for generating BagItProfile objects from a URL.
 *
 * @package whikloj\BagItTools\Profiles
 * @author Jared Whiklo
 * @since 5.0.0
 */
class ProfileFactory
{
    use CurlInstance;

    /**
     * @param string $url The profile URL to parse.
     * @return BagItProfile The profile object.
     * @throws ProfileException If the URL is invalid or unable to download/parse.
     */
    public static function generateProfileFromUri(string $url): BagItProfile
    {
        $parsed_url = parse_url($url);
        if ($parsed_url === false) {
            throw new ProfileException("Invalid URL");
        }
        if (!isset($parsed_url['scheme'])) {
            throw new ProfileException("URL must have a scheme");
        }
        if (!isset($parsed_url['host'])) {
            throw new ProfileException("URL must have a host");
        }
        if (!isset($parsed_url['path'])) {
            throw new ProfileException("URL must have a path");
        }
        $curl = self::createCurl($url, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($curl);
        if (is_bool($response)) {
            // We set CURLOPT_RETURNTRANSFER to true so boolean is a request error.
            throw new ProfileException("Error downloading profile");
        }
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($http_code !== 200) {
            throw new ProfileException("Error downloading profile, HTTP code {$http_code}");
        }
        return BagItProfile::fromJson($response);
    }
}
