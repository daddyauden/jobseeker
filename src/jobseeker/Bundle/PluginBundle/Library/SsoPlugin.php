<?php

namespace jobseeker\Bundle\PluginBundle\Library;

use jobseeker\Bundle\PluginBundle\DependencyInjection\UserScope;
use jobseeker\Bundle\PluginBundle\Library\JobSeeker\OAuth;

class SsoPlugin extends UserScope
{

    public function __construct()
    {
        $this->config = array(
            "client_id" => "",
            "client_secret" => "",
            "redirect_uri" => "",
            "scope" => ""
        );
    }

    public function getName()
    {
        return "jobseeker";
    }

    public function getLogo()
    {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAIAAAFSDNYfAAAKQ2lDQ1BJQ0MgcHJvZmlsZQAAeNqdU3dYk/cWPt/3ZQ9WQtjwsZdsgQAiI6wIyBBZohCSAGGEEBJAxYWIClYUFRGcSFXEgtUKSJ2I4qAouGdBiohai1VcOO4f3Ke1fXrv7e371/u855zn/M55zw+AERImkeaiagA5UoU8Otgfj09IxMm9gAIVSOAEIBDmy8JnBcUAAPADeXh+dLA//AGvbwACAHDVLiQSx+H/g7pQJlcAIJEA4CIS5wsBkFIAyC5UyBQAyBgAsFOzZAoAlAAAbHl8QiIAqg0A7PRJPgUA2KmT3BcA2KIcqQgAjQEAmShHJAJAuwBgVYFSLALAwgCgrEAiLgTArgGAWbYyRwKAvQUAdo5YkA9AYACAmUIszAAgOAIAQx4TzQMgTAOgMNK/4KlfcIW4SAEAwMuVzZdL0jMUuJXQGnfy8ODiIeLCbLFCYRcpEGYJ5CKcl5sjE0jnA0zODAAAGvnRwf44P5Dn5uTh5mbnbO/0xaL+a/BvIj4h8d/+vIwCBAAQTs/v2l/l5dYDcMcBsHW/a6lbANpWAGjf+V0z2wmgWgrQevmLeTj8QB6eoVDIPB0cCgsL7SViob0w44s+/zPhb+CLfvb8QB7+23rwAHGaQJmtwKOD/XFhbnauUo7nywRCMW735yP+x4V//Y4p0eI0sVwsFYrxWIm4UCJNx3m5UpFEIcmV4hLpfzLxH5b9CZN3DQCshk/ATrYHtctswH7uAQKLDljSdgBAfvMtjBoLkQAQZzQyefcAAJO/+Y9AKwEAzZek4wAAvOgYXKiUF0zGCAAARKCBKrBBBwzBFKzADpzBHbzAFwJhBkRADCTAPBBCBuSAHAqhGJZBGVTAOtgEtbADGqARmuEQtMExOA3n4BJcgetwFwZgGJ7CGLyGCQRByAgTYSE6iBFijtgizggXmY4EImFINJKApCDpiBRRIsXIcqQCqUJqkV1II/ItchQ5jVxA+pDbyCAyivyKvEcxlIGyUQPUAnVAuagfGorGoHPRdDQPXYCWomvRGrQePYC2oqfRS+h1dAB9io5jgNExDmaM2WFcjIdFYIlYGibHFmPlWDVWjzVjHVg3dhUbwJ5h7wgkAouAE+wIXoQQwmyCkJBHWExYQ6gl7CO0EroIVwmDhDHCJyKTqE+0JXoS+cR4YjqxkFhGrCbuIR4hniVeJw4TX5NIJA7JkuROCiElkDJJC0lrSNtILaRTpD7SEGmcTCbrkG3J3uQIsoCsIJeRt5APkE+S+8nD5LcUOsWI4kwJoiRSpJQSSjVlP+UEpZ8yQpmgqlHNqZ7UCKqIOp9aSW2gdlAvU4epEzR1miXNmxZDy6Qto9XQmmlnafdoL+l0ugndgx5Fl9CX0mvoB+nn6YP0dwwNhg2Dx0hiKBlrGXsZpxi3GS+ZTKYF05eZyFQw1zIbmWeYD5hvVVgq9ip8FZHKEpU6lVaVfpXnqlRVc1U/1XmqC1SrVQ+rXlZ9pkZVs1DjqQnUFqvVqR1Vu6k2rs5Sd1KPUM9RX6O+X/2C+mMNsoaFRqCGSKNUY7fGGY0hFsYyZfFYQtZyVgPrLGuYTWJbsvnsTHYF+xt2L3tMU0NzqmasZpFmneZxzQEOxrHg8DnZnErOIc4NznstAy0/LbHWaq1mrX6tN9p62r7aYu1y7Rbt69rvdXCdQJ0snfU6bTr3dQm6NrpRuoW623XP6j7TY+t56Qn1yvUO6d3RR/Vt9KP1F+rv1u/RHzcwNAg2kBlsMThj8MyQY+hrmGm40fCE4agRy2i6kcRoo9FJoye4Ju6HZ+M1eBc+ZqxvHGKsNN5l3Gs8YWJpMtukxKTF5L4pzZRrmma60bTTdMzMyCzcrNisyeyOOdWca55hvtm82/yNhaVFnMVKizaLx5balnzLBZZNlvesmFY+VnlW9VbXrEnWXOss623WV2xQG1ebDJs6m8u2qK2brcR2m23fFOIUjynSKfVTbtox7PzsCuya7AbtOfZh9iX2bfbPHcwcEh3WO3Q7fHJ0dcx2bHC866ThNMOpxKnD6VdnG2ehc53zNRemS5DLEpd2lxdTbaeKp26fesuV5RruutK10/Wjm7ub3K3ZbdTdzD3Ffav7TS6bG8ldwz3vQfTw91jicczjnaebp8LzkOcvXnZeWV77vR5Ps5wmntYwbcjbxFvgvct7YDo+PWX6zukDPsY+Ap96n4e+pr4i3z2+I37Wfpl+B/ye+zv6y/2P+L/hefIW8U4FYAHBAeUBvYEagbMDawMfBJkEpQc1BY0FuwYvDD4VQgwJDVkfcpNvwBfyG/ljM9xnLJrRFcoInRVaG/owzCZMHtYRjobPCN8Qfm+m+UzpzLYIiOBHbIi4H2kZmRf5fRQpKjKqLupRtFN0cXT3LNas5Fn7Z72O8Y+pjLk722q2cnZnrGpsUmxj7Ju4gLiquIF4h/hF8ZcSdBMkCe2J5MTYxD2J43MC52yaM5zkmlSWdGOu5dyiuRfm6c7Lnnc8WTVZkHw4hZgSl7I/5YMgQlAvGE/lp25NHRPyhJuFT0W+oo2iUbG3uEo8kuadVpX2ON07fUP6aIZPRnXGMwlPUit5kRmSuSPzTVZE1t6sz9lx2S05lJyUnKNSDWmWtCvXMLcot09mKyuTDeR55m3KG5OHyvfkI/lz89sVbIVM0aO0Uq5QDhZML6greFsYW3i4SL1IWtQz32b+6vkjC4IWfL2QsFC4sLPYuHhZ8eAiv0W7FiOLUxd3LjFdUrpkeGnw0n3LaMuylv1Q4lhSVfJqedzyjlKD0qWlQyuCVzSVqZTJy26u9Fq5YxVhlWRV72qX1VtWfyoXlV+scKyorviwRrjm4ldOX9V89Xlt2treSrfK7etI66Trbqz3Wb+vSr1qQdXQhvANrRvxjeUbX21K3nShemr1js20zcrNAzVhNe1bzLas2/KhNqP2ep1/XctW/a2rt77ZJtrWv913e/MOgx0VO97vlOy8tSt4V2u9RX31btLugt2PGmIbur/mft24R3dPxZ6Pe6V7B/ZF7+tqdG9s3K+/v7IJbVI2jR5IOnDlm4Bv2pvtmne1cFoqDsJB5cEn36Z8e+NQ6KHOw9zDzd+Zf7f1COtIeSvSOr91rC2jbaA9ob3v6IyjnR1eHUe+t/9+7zHjY3XHNY9XnqCdKD3x+eSCk+OnZKeenU4/PdSZ3Hn3TPyZa11RXb1nQ8+ePxd07ky3X/fJ897nj13wvHD0Ivdi2yW3S609rj1HfnD94UivW2/rZffL7Vc8rnT0Tes70e/Tf/pqwNVz1/jXLl2feb3vxuwbt24m3Ry4Jbr1+Hb27Rd3Cu5M3F16j3iv/L7a/eoH+g/qf7T+sWXAbeD4YMBgz8NZD+8OCYee/pT/04fh0kfMR9UjRiONj50fHxsNGr3yZM6T4aeypxPPyn5W/3nrc6vn3/3i+0vPWPzY8Av5i8+/rnmp83Lvq6mvOscjxx+8znk98ab8rc7bfe+477rfx70fmSj8QP5Q89H6Y8en0E/3Pud8/vwv94Tz+4A5JREAAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAADc2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS41LWMwMTQgNzkuMTUxNDgxLCAyMDEzLzAzLzEzLTEyOjA5OjE1ICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjA4MmU4YWVmLWI4MzEtNDhlNC04NGU2LTRkNjRiOWRjZGQxOSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDowQjgzRDkwOEZCNUMxMUUzQUQ1Q0Q3ODE0NDFDMDFBRCIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDowQjgzRDkwN0ZCNUMxMUUzQUQ1Q0Q3ODE0NDFDMDFBRCIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowODJlOGFlZi1iODMxLTQ4ZTQtODRlNi00ZDY0YjlkY2RkMTkiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MDgyZThhZWYtYjgzMS00OGU0LTg0ZTYtNGQ2NGI5ZGNkZDE5Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+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";
    }

    public function getConfigForInstall()
    {
        $config = array();
        $config['client_id'] = "***";
        $config['client_secret'] = "***";
        $config['redirect_uri'] = $this->container->getParameter("host") . "/auth/callback";
        $config['scope'] = "email address profile";
        return $config;
    }

    public function registerProxy()
    {
        $proxy = new OAuth($this->getConfig("client_id"), $this->getConfig("client_secret"));
        $proxy->setScopes($this->getConfig("scope"));
        $this->proxy = $proxy;
    }

    public function getLoginUri()
    {
        return array(
            $this->getName(),
            $this->getProxy()->getAuthorizeURL($this->getConfig("redirect_uri"), "code", $this->generateState())
        );
    }

    public function authenticate($code)
    {
        $keys['code'] = $code;
        $keys['redirect_uri'] = $this->getConfig("redirect_uri");
        try {
            $token = $this->getProxy()->getAccessToken("code", $keys);
            parent::saveToken(json_encode($token));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function revokeToken($token)
    {
        return $this->getProxy()->revokeToken($token);
    }

    public function getUser()
    {
        if ($this->getClient() == null) {
            $token = $this->container->get("UserPluginManager")->getToken();
            if ($token['name'] == $this->getName() && $value = $token['value']) {
                $client = new SaeTClientV2($this->getConfig("client_id"), $this->getConfig("client_secret"), $value['access_token']);
                $this->setClient($client);
            }
        }
        $user = $this->getClient()->get_uid();
        return $this->getClient()->show_user_by_id($user['uid']);
    }

    public function getUserForSso()
    {
        $user = $this->getUser();

        return;
    }

}
