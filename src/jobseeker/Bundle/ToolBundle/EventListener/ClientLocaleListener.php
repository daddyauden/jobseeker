<?php

namespace jobseeker\Bundle\ToolBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use jobseeker\Bundle\ToolBundle\Service\Maxmind\MaxMind;

class ClientLocaleListener implements EventSubscriberInterface
{

    private $router;
    private $defaultLocale;

    public function __construct($defaultLocale = 'en', RequestContextAwareInterface $router = null)
    {
        $this->defaultLocale = strtolower($defaultLocale);
        $this->router = $router;
    }

    public function setLocale(Request $request = null)
    {
        if (null === $request) {
            return;
        }
        if ($locale = $request->attributes->get('_locale')) {
            $lang = $locale;
        } elseif ($locale = $request->cookies->get("LANG")) {
            $lang = $locale;
        } else {
            $lang = $this->defaultLocale;
        }
        $lang = strtolower($lang);
        if (strpos($lang, "_")) {
            $locale = explode("_", $lang);
            $lang = strtolower($locale[0]) . "_" . strtoupper($locale[1]);
        }
        $request->setLocale($lang);
        $request->setDefaultLocale($lang);
        if (null !== $this->router) {
            $this->router->getContext()->setParameter('_locale', $request->getLocale());
        }
    }

    public function setCountry(Request $request = null)
    {
        if (null === $request) {
            return;
        }
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $host = $request->getHost();
        $countryCode = "";
        if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
            if ($host === "jobseeker.com" || $host === "www.jobseeker.com") {
                $ip = $request->getClientIp();
                try {
                    $countryCode = strtolower(MaxMind::getCountry($ip, "code"));
                } catch (Exception $ex) {
                    $countryCode = "ca";
                }

                if (!$countryCode) {
                    $countryCode = "ca";
                }
                $host = $scheme . '://' . $countryCode . ".jobseeker.com" . $request->getRequestUri();
                $response = new RedirectResponse($host);
                $response->send();
            } else {
                $reservedCountry = array("ae", "ao", "au", "ca", "cn", "de", "dz", "es", "fr", "gb", "gr", "hk", "id", "il", "ir", "it", "jp", "ke", "kr", "mo", "my", "ng", "nz", "ph", "pk", "pt", "sa", "sd", "sg", "th", "tr", "tw", "us", "vn", "za", "zw", "sso");
                preg_match("/(.*)\.jobseeker\.(.*)/", $host, $matches);
                if (count($matches) === 0 || (isset($matches[1]) && !in_array(strtolower($matches[1]), $reservedCountry))) {
                    $host = $scheme . "://ca.jobseeker." . $matches[2] . $request->getRequestUri();
                    $response = new RedirectResponse($host);
                    $response->send();
                }
            }
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        $this->setCountry($request);
        $request->setDefaultLocale($this->defaultLocale);
        $this->setLocale($request);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 15))
        );
    }

}
