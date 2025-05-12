<?php

namespace jobseeker\Bundle\SsoBundle\Storage;

interface PublicKeyInterface
{

    public function getPublicKey($client_id = null);

    public function getPrivateKey($client_id = null);

    public function getEncryptionAlgorithm($client_id = null);

}
