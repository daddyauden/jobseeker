<?php

namespace jobseeker\Bundle\SsoBundle\Service\OAuth2\OpenID\Storage;

interface UserClaimsInterface
{

    const VALID_CLAIMS = 'profile email address';
    const PROFILE_CLAIM_VALUES = 'username first_name last_name addtime';
    const EMAIL_CLAIM_VALUES = 'email';
    const ADDRESS_CLAIM_VALUES = 'country';

    public function getUserClaims($email, $scope);

}
