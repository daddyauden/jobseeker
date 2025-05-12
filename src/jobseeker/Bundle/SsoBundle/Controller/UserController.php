<?php

namespace jobseeker\Bundle\SsoBundle\Controller;

use jobseeker\Bundle\ToolBundle\Base;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Base
{

    protected $entityName = "SsoBundle:User";

    public function getAction()
    {
        if ($this->isPost()) {
            $client = $this->getPost();

            if (true !== $result = $this->validateRequest($client)) {
                return $result;
            }

            if (true !== $result = $this->validateAuthorize($client)) {
                return $result;
            }

            if (isset($client['source']) && isset($client['email']) && isset($client['password'])) {
                $user = $this->get("OAuth2Storage")->getUserBySEP($client['source'], $client['email'], $client['password']);
            } else if (isset($client['email']) && isset($client['password'])) {
                $user = $this->get("OAuth2Storage")->getUserByEP($client['email'], $client['password']);
            } else if (isset($client['email'])) {
                $user = $this->get("OAuth2Storage")->getUserDetail($client['email']);
            } else if (isset($client['uid'])) {
                $user = $this->get("OAuth2Storage")->getUserByUid($client['uid']);
            } else {
                $user = false;
            }

            return JsonResponse::create($user);
        } else {
            return JsonResponse::create(ApiException::getMessage("invalid_method"));
        }
    }

    public function registerAction()
    {
        if ($this->isPost()) {
            $client = $this->getPost();

            if (true !== $result = $this->validateRequest($client)) {
                return $result;
            }

            if (true !== $result = $this->validateAuthorize($client)) {
                return $result;
            }

            if (!isset($client['email'])) {
                return JsonResponse::create(ApiException::getMessage("miss_parameter"));
            }

            $userIsExist = $this->get("OAuth2Storage")->getUserDetail($client['email']);

            if ($userIsExist !== false) {
                return JsonResponse::create(ApiException::getMessage("user_exist"));
            }

            try {
                $userManager = $this->get("SsoUserManager");
                $user = $userManager->createUser();
                $user->setSource($client['source']);
                $user->setCountry($client['country']);
                $user->setEmail($client['email']);
                if (isset($client['isEncrypted']) && $client['isEncrypted'] == false) {
                    $user->setPassword($client['password'], false);
                } else {
                    $user->setPassword($client['password']);
                }
                if (strtolower($client["source"]) !== "jobseeker") {
                    $user->setStatus(1);
                    $user->setSalt(null);
                }
                $userManager->updateUser($user);
            } catch (\Exception $ex) {
                return JsonResponse::create(ApiException::getMessage("user_register_error"));
            }

            return JsonResponse::create(unserialize($user->serialize()));
        } else {
            return JsonResponse::create(ApiException::getMessage("invalid_method"));
        }
    }

    public function activeAction()
    {
        if ($this->isPost()) {
            $client = $this->getPost();

            if (true !== $result = $this->validateRequest($client)) {
                return $result;
            }

            if (true !== $result = $this->validateAuthorize($client)) {
                return $result;
            }

            if (!isset($client['salt']) || !isset($client['uid'])) {
                return JsonResponse::create(ApiException::getMessage("miss_parameter"));
            } else {
                $user = $this->get("OAuth2Storage")->getUserByUid($client['uid']);
                if (false === $user) {
                    $res = false;
                } else if (intval($user["status"]) === 1) {
                    $res = $user;
                } else if ($client['salt'] !== $user["salt"]) {
                    $res = false;
                } else if (intval($user["status"]) === 0 && $client['salt'] === $user["salt"]) {
                    try {
                        $userEntity = $this->getRepo()->findOneBy(array("uid" => intval($client['uid'])));
                        $userEntity->setStatus(1);
                        $userEntity->setSalt(null);
                        $this->getEm()->persist($userEntity);
                        $this->getEm()->flush();
                        $res = unserialize($userEntity->serialize());
                    } catch (\Exception $e) {
                        $res = false;
                    }
                } else {
                    $res = false;
                }
            }
            return JsonResponse::create($res);
        } else {
            return JsonResponse::create(ApiException::getMessage("invalid_method"));
        }
    }

    private function validateRequest(array $client = array())
    {

        if (!isset($client['client_id'])) {
            return JsonResponse::create(ApiException::getMessage("miss_client_id"));
        }

        if (!isset($client['client_secret'])) {
            return JsonResponse::create(ApiException::getMessage("miss_client_secret"));
        }

        if (!array_key_exists("email", $client) && !array_key_exists("uid", $client)) {
            return JsonResponse::create(ApiException::getMessage("miss_parameter"));
        }

        return true;
    }

    private function validateAuthorize(array $client = array())
    {
        $auth2 = $this->get("OAuth2Storage");
        if (false === $auth2->checkClientCredentials($client['client_id'], $client['client_secret'])) {
            return JsonResponse::create(ApiException::getMessage("invalid_client_id"));
        }

        return true;
    }

}
