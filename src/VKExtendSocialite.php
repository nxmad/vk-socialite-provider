<?php

namespace SocialiteProviders\VK;

use SocialiteProviders\Manager\SocialiteWasCalled;

class VKExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'vk', 'SocialiteProviders\VK\Provider'
        );
    }
}
