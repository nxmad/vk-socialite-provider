<?php

namespace SocialiteProviders\VK;

use SocialiteProviders\Manager\OAuth2\User;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider implements ProviderInterface
{
    protected $fields = [
        'id', 'first_name', 'last_name', 'screen_name', 'photo_200_orig', 'photo_max_orig'
    ];

    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'VK';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['email'];

    /**
     * Last API version.
     */
    const VERSION = '5.92';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://oauth.vk.com/authorize', $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://oauth.vk.com/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $userFields = $this->getConfig('fields', []);

        if (! is_array($userFields)) {
            $userFields = explode(',', $userFields);
        }

        $query = http_build_query([
            'access_token' => $token,
            'v'            => self::VERSION,
            'language'     => $this->getConfig('lang', 'en'),
            'fields'       => implode(',', array_merge($this->fields, $userFields)),
        ]);

        $data = $this->getHttpClient()
            ->get('https://api.vk.com/method/users.get', compact('query'))
            ->getBody()
            ->getContents();

        $data = json_decode($data, true);

        if (! is_array($data['response'][0]) || ! isset($data['response'][0]))
        {
            throw new \RuntimeException(
                sprintf('Invalid JSON response from VK: %s', $data['error']['error_msg'])
            );
        }

        return $data['response'][0];
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $name = implode(' ', array_only($user, ['first_name', 'last_name']));

        return (new User)
            ->setRaw($user)
            ->map([
                'name' => $name,
                'id' => $user['id'],
                'nickname' => $user['screen_name'],
                'email' => array_get($user, 'email'),
                'avatar' => array_get($user, 'photo_200_orig'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function additionalConfigKeys()
    {
        return ['lang', 'fields'];
    }
}
