<?php

namespace jobseeker\Bundle\ToolBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use jobseeker\Bundle\ToolBundle\Service\Maxmind\MaxMind;

class ToolExtension extends AbstractExtension
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter("country", array($this, "getCountry")),
            new TwigFilter("city", array($this, "getCity")),
            new TwigFilter("system", array($this, "getSystem")),
            new TwigFilter("avator", array($this, "getAvator")),
            new TwigFilter("formatForJS", array($this, "getConvertDateForJS"))
        );
    }

    public function getCountry($ip, $by = "all")
    {
        return MaxMind::getCountry($ip, $by);
    }

    public function getCity($ip, $by = "all")
    {
        return MaxMind::getCity($ip, $by);
    }

    public function getSystem($field)
    {
        $prefix = strtoupper($this->container->getParameter("country"));
        if ($value = $this->container->get("snc_redis.default")->hget($prefix . ":system", $field)) {
            return $value;
        }

        return $this->container->hasParameter($field) ? $this->container->getParameter($field) : "";
    }

    public function getAvator($avator, $scale = "1x")
    {
        $upload_avator_url = $this->container->getParameter("upload_avator_url") ?: "/media/avator";
        $upload_avator_dir = $this->container->getParameter("upload_avator_dir") ?: "/media/avator";
        $avator = $avator ?: "avator.png";
        $file = ($scale ? $scale . "_" : "") . basename($avator);
        $real_file = $upload_avator_dir . "/" . dirname($avator) . '/' . $file;
        $file_exist = is_file($real_file) && file_exists($real_file);

        if ($file_exist) {
            return $upload_avator_url . "/" . dirname($avator) . "/" . $file;
        } else {
            $file = ($scale ? $scale . "_" : "") . "avator.png";
            return $this->container->getParameter("host") . $upload_avator_url . "/" . $file;
        }
    }

    private function formatForJS($s_date, $s_time)
    {
        $js_time_format = NULL;

        $js_date_format = NULL;

        if ($s_date) {
            if (strpos($s_date, '/')) {
                list($m, $d, $y) = explode("/", $s_date);
                $js_date_format = "$m$m/$d$d/$y$y$y$y";
            } else if (strpos($s_date, '-')) {
                list($d, $m, $y) = explode("-", $s_date);
                $js_date_format = "$d$d-$m$m-$y$y$y$y";
            }
        }

        if ($s_time) {
            list($h, $m, $s) = explode(":", $s_time);
            $js_time_format = "$h$h:$m$m:$s$s";
        }

        if ($js_date_format && $js_time_format) {
            return strtolower($js_date_format . " " . $js_time_format);
        } else if ($js_date_format) {
            return strtolower($js_date_format);
        } else {
            return strtolower($js_time_format);
        }

        return NULL;
    }

    private function convertDateTimeFormatForJS($s_datetime = NULL)
    {
        if (NULL == $s_datetime) {
            $s_datetime = $this->getSystem("datetime_format");
        }

        list($date, $time) = explode(" ", $s_datetime);

        return $this->formatForJS($date, $time);

    }

    private function convertDateFormatForJS($s_date = NULL)
    {
        if (NULL == $s_date) {
            $s_date = $this->getSystem("date_format");
        }

        if (NULL == $s_date) {
            return NULL;
        }

        return $this->formatForJS($s_date, NULL);
    }

    private function convertTimeFormatForJS($s_time = NULL)
    {
        if (NULL == $s_time) {
            $s_time = $this->getSystem("time_format");
        }

        if (NULL == $s_time) {
            return NULL;
        }

        return $this->formatForJS(NULL, $s_time);

    }

    public function getConvertDateForJS($format)
    {
        $format = $this->getSystem($format);
        switch ($format) {
            case "datetime_format":
                return $this->convertDateTimeFormatForJS($format);
                break;
            case "date_format":
                return $this->convertDateFormatForJS($format);
                break;
            case "time_format":
                return $this->convertTimeFormatForJS($format);
                break;
        }

        return NULL;
    }
}
