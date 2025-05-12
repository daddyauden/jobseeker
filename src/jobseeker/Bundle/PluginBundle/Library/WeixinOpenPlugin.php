<?php

namespace jobseeker\Bundle\PluginBundle\Library;

use jobseeker\Bundle\PluginBundle\DependencyInjection\UserScope;
use jobseeker\Bundle\PluginBundle\Library\Weixin\WeixinOpenOAuthV2;

class WeixinOpenPlugin extends UserScope
{

    public function __construct()
    {
        $this->config = array(
            "appid" => "",
            "appsecret" => "",
            "redirect_uri" => "",
            "scope" => ""
        );
    }

    public function getName()
    {
        return "weixinopen";
    }

    public function getLogo()
    {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3NpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDoyYWEwMDg1OC0wOGQ4LTRhN2YtYWQ4My0zNzZhNDMxZTEzOTIiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NzI2NTIzODAxMjMzMTFFNEI1MERFQjkyRkM2NUIyOUYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NzI2NTIzN0YxMjMzMTFFNEI1MERFQjkyRkM2NUIyOUYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6Y2Y5NWUzYzMtOWQ2My00YzliLWEzZDQtZmNhMTJjODBhNDI1IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjJhYTAwODU4LTA4ZDgtNGE3Zi1hZDgzLTM3NmE0MzFlMTM5MiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pm8dmYUAAAzGSURBVHja7Ft5dFTVGf/eNm/2LZOZ7CF7CLusdUnYBKsgoIfqkUoUFzapYCzYHv6w57RYERCtemrrwXIq0FbpKRCQSlFPJWwFlE04GhYJScgy2TMzb++9Ly+YhMnMBDJJzoE350vy5t3vvvv97ne/73e/90IoigK380HCbX7cAeAOAHcAuL0PoieNi4qX3/Ad5+PsBhOTz7LEeEZHDWUYYhDFkIkEASxJEmZZVlpQouEkQakQBPmywEtnuYB8mOOkAwyr8/bk/utnbAx5/WYyGn0zqDVU1lvdybZ5Bgszl4033mswEAzDKkDrACgGdUrJQCDfIhG8sgJmRQazJEGMKBDDBJ6eKfIE+H2KyPuVg75W8ePa8paPrG5rQ394QI8A8De3xse4Ta+4RroWWGxg1psUYPXISEIABWRkrIxayYDP1LnQJgSDQSA0kGcAYyKR25HgUGiaC0C+v5XJtzrtv/c1y5u91f41epO+nCCIgQXAz9Y8qs8Yk7DK4qBWWh1gtNpl1WhJEYFDRodzvR+vSkinfe2hD0uCRU+DzcmYmhqIJQaLYUFznbSu9Hj571KGpwQGAgDEsk8WjXInsH+1xRB5ZmQ4UAIyWkRGy7d0Y+U6IAKCIgBGOwMmC6M3W8nVRkvK3JrKwLy3H/3jCRR3okpVQ2UB4qWdi+e5k5kSm0fIMzkDIBKtIMgB5OptAPSWyIqk9iug/k1ODuwePseVRJe8tGNRIQp8RH8AQLxcvPgXVo+y2Rkv6FkLBzwaIHZ5WZGjJrh/fB8dul9MvMBa45RNK/cseTmaIATrmFi+4/lCm0fZ5ElUCJJBRiNX7fv8TIIi6oBrZuFKqfDimzPf/0OncNJLabArAMSSvz0z2ZVCfhoTLzI0K6rRvf9ICgmywEBNBSl5r8Csdx/7YE8oEG4ZgMfXzfVkjrefQGswQW8KZTwB3lof1NZx4HKyEOMyhpucG7lEPQdV1T5wOPTgdnevj0HgfDTUX9NVXzzROGrri/+oiBYRIhOH2t4wOfgE1oRTnNTNgAg4/NX3cPL4JRU9fMsRo9Ngwn2Z7dk/LOanjl2BIyXf4RGrGrlDkqBg6mBQiGD6MuiMCpgc4E7Isa5DXzyppo9eDoJE4Z+fGM2YuHlmmwCiJIAsS0FEBu+1Rjh77DzQJAcUxam/8Tn+Hl8PrvejNNe3wvEDp4EiAtf1S89egPLLtd3q4/HgcdEm7rEFm+aN6ymFjwQA0h5PF1kcIinInJqWgkVp7GK11c2IC2iahPYbnePv8fVQUR5fr6ttAYVUbtD3htTHaZIDi1MkrR56RW9u4tSOphVNcekswhyjWdRyvBRUAA3G4TRrjt9ZHDFm9Xp3um2C9B1mzdU7CDLcrvbbvT4elxHFJcbMz5756oPu3gSASB7lmc4YeJ2IZ191w+AiIXeMjbNB7uD0Tp3g81iPTb0eSh+7s8VugJEjcjrpp6UnQfIgVxhdGfD4dEaeic2wPtxbXoCDIMWY5CmMTuw28HUksDwaxD2ThkFWTirUexvRzNtQdLICJ/s0fSV00pF5GH13rmp0bU09WO0WSEh2qPoimuVw2YTRoX22hZmI/twkSVKnNIVBCnVQFBUcAJKVhlOMHEEaUVTuHlBawRlnAFe8WV2fAam1belAZPoc0re5deDwJKr6fqmlW30a9GCj3GCkHMAQOhAtJLov/ZOvv141rOzq1XNJSUn+W/YAkhaSKFqOaIODh6jSVrTpxdtWRU1lSsQ8IJy+DoyQph8DGcaxkGTIA7su7sZOUiAV/TyOuxIFsRTN/GGe5/eUl5fvycnJae4pAIRCyhYgpAjzeJsZCvSY+3Srjx3PRQ+CsdY5MNh6HzAk27VltzGMoqlsCqhsRsfMz8rOag1wgW2tra1vetzuc4IoKhEB0J72COjbhyTYcDPphEnOZyHPPlElWbdEnQnCxLLss0ieam5p2Xb+3LlVuYMHV4ZlgpIoN6N4wgaJEdEzXlZgsGEyTI9bCnrK1JYNe28CaATCk1nZ2TMaGhoWxrpc23lBkLsDQOH8asHSRZJ9U4rCyWKycyGMd82JbsmbJB02m+3v9Q0NG+6fOvUV9JUYjAcovE85y3PQq0WO7gR5GzwQswLGxcxRl0AfCGE0mor+tWPnB/kFBXQwAKSmSv6//kBbNI7mB6ft+2xPwUjntKBsMppiNBoKt2/f/npXAqUCULr/6j5fK8HLaF2qaSkKIksKpDPj4R7343018zeIxWJdceHipVkdQcBhT/GW1omZ0wYN1VukXJ0uOjNAiSzMS10LLGUIu3YFQUCsrWdMN0IdwmQyFrjd7g/37t3rbwdAvWBPdVUbE5l5drtC9H7QU2C85XHIcdwdst21a9fgL5s3w78/2wdopiAjIx1QNA+p09TUBFu2boPi3Xvg9JkzkJKSAmazuXsESNKcnpambFi//gs8tHYAlLIjFXXpU1KzGKOSp9f3MgACA48kr0YEJ3THH23ZCrW1tSobaGxqBJTCYEheXkidHTt3wsWLF1QO4fP7oazsKowZPTqkjk6nG9LS0vKno0eO+MnrZRcA38ktZa96a4mqlpbeXfupzF1gYuxhgaqpqW5bMpoPehEY4Y7rbYg23lhdXRVWB22KHM8+99wMNVN2oOj8xf2Xr/xQ4lteXaMI/oDcKwDgtJdhGhtRW8Tj8bNEtK3GhREFstF5OJ2s7By1bZsO2prn5kZ0L4fDMR3zIKrrPqXsYGWFMy+uljDD/UYTEBR1a8FP5GUocD8NNtYVdmYy0tNB4HmgaRpGjhgJBfn5EO454aDUVNSGVMN8dlYmPDB9uqofnoYrxBtr135IBKkS65DYJr8+bqknD1bHuYG8lZjAtUiwashuMOlskfL5Hld5b0ZHFMVGu9WSSgbZrfI4uH6+6uh7V4+Jv0SBmWtukW/aA2REfvSMOWKy1KmGGEUdkiItaimgmy07hwPxV7858dHZHU2PVVWR1ZIINx0HRJHra+IXVtBmjNOe3Hdbt8ANGk5tOl/CeZl9rX64KfqLtZr4OlAG2IfjOfxChkKHKd4IOD263ebxRrZBrfr+uIaQoF0d5ilEmPVZ0XgBnIYEGEhHc3NLKQ764cKlMvfN2dl6sz8T1wxl1Z2xMgG1NVAhBOQqk40cpjcqNKvDBKMtr1A0Nlx7RYZQ4Pu64zDUc++AAqCqsvIo3geFA4C0xjGzSaZeLWk3NClQX0/5ar6T3j+6/vwHvmo/Fzs8xj7o/vhxlkTdCL2VyGIMkEiz4CEIRYeAMPJ+wtfMlcizBi8zE0AMFPuV/fv/sxd7eLgR6RbtmnVIH1N1V0WNoLRUKXvPflzz2x/2lV1C11q1Z3SUljoZTShNiA7Pfhxnz517LyExceRAsN7r9R7JTEvD1ZiakNunqUWTXKDjRpX9oJwr3Rd4cs/zJxYi479Fl+qQtBQVL8cg4CosDij4lbca7F1IKjXBT3LLkVwuLt79jpYUoJ9F2b2r+J32CQznAaYpr94z8+Dbx//nrws0akr8S7telMIxtC7v9GEP8Xxz+sz7ycnJP+3P2S8rK/t05LChC7WJ4sOWQS99WXZJ9IvNmvECmnU5ktfYDm093PkZN5JTp06emTV79oMMw1j7w3ifz1f+dOH8RWV4ywgQgAgeM7ev48iffISuQDtWFBXd+/LKlR+iLamtL43neb5x7WuvPf3Wxo0H0Gl9e4E0kkJ4b9WqsReIhw8dqqco+vToMWMno9+GvjCe47i6tze+tXTDunUlmvFCx5JYXx4YBOFgyYGaa5WVhydMmDBar9e7on3TkgMHNixf9sI/tUDNd8rz0SqCBi151XrbKXbttq1bjk0qyP/5yW++2SxJkhitcWBhWdarzTzX1aP79HV5fOfKNhDwLHgryssvTpsyec3qX//qiStXrnyOmKYYjb2Py+12aWv+hpkhovVPU8EyRUVN5xJXQqwLL0FcbcAB0fHwrNnpzy9e/EhmVlaB1WpN6+k65wKBeqPJlEjTtL4L8flkWG7OfPSnf0ABoIFAaBkCD9qEBO/TLTNmPpz80MyZwwelpWXb7PZ4o9HoZDTDBFEMoJRW19jQUHn50qXvdu/adap4185rOMbkT5wY+9s1ry3MyMx8CI1BjXF+v//bjJTk8Zi89SsA5dU13bZPdMe202ZMmlgNEFYTRgOpYxFX1KI5p0l7cMPt7UteWHbXcwsXLnd7PGOQjcKCwvnJn+3dWzVgAQgCRrtntBtOduAtSju50oBQ1zfqX0G6dIdlFfPWu+8+gLxp6ZdffP7IM4WFJ7rGgT4F4GpVdZ8E2ySPux08/AqqQ0v3OBM0aaD1DwD9cJAddqhCsExA3PnX2dv8uAPAHQBu8+P/AgwAI3P91nv8oIsAAAAASUVORK5CYII=";
    }

    public function getConfigForInstall()
    {
        $config = array();
        $config['appid'] = "***";
        $config['appsecret'] = "***";
        $config['redirect_uri'] = $this->container->getParameter("host") . "/auth/callback";
        $config['scope'] = "snsapi_login";
        return $config;
    }

    public function registerProxy()
    {
        $proxy = new WeixinOpenOAuthV2($this->getConfig("appid"), $this->getConfig("appsecret"), $this->getConfig("redirect_uri"));
        $this->proxy = $proxy;
        $proxy->setScope($this->getConfig("scope"));
        $this->proxy = $proxy;
    }

    public function getLoginUri()
    {
        return array(
            $this->getName(),
            $this->getProxy()->getAuthorizeURL("code", $this->generateState())
        );
    }

    public function authenticate($code)
    {
        $keys['code'] = $code;
        try {
            $token = $this->getProxy()->getAccessToken("code", $keys);
            parent::saveToken(json_encode($token));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function refreshToken($token)
    {
        return $this->getProxy()->refreshToken($token);
    }

    public function getUser()
    {
        $token = $this->container->get("UserPluginManager")->getToken();
        if ($this->getClient() == null) {
            if ($token['name'] == $this->getName() && $value = $token['value']) {
                $client = new WeixinOpenOAuthV2($this->getConfig("appid"), $this->getConfig("appsecret"), $this->getConfig("redirect_uri"), $value['access_token'], $value);
                $this->setClient($client);
            }
        }

        return $this->getClient()->userInfo($token['value']);
    }

    public function getUserForSso()
    {
        $user = $this->getUser();

        if (false !== $user) {
            return array(
                "source" => $this->getName(),
                "email" => $user['unionid'],
                "password" => $user['unionid'],
                
            );
        } else {
            return null;
        }
    }

}
