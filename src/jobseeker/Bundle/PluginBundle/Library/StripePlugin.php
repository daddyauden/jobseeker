<?php

namespace jobseeker\Bundle\PluginBundle\Library;

use jobseeker\Bundle\PluginBundle\DependencyInjection\PaymentScope;

class StripePlugin extends PaymentScope
{

    public function __construct()
    {
        $this->config = array(
            "secret_key" => "",
            "publishable_key" => ""
        );
    }

    public function getName()
    {
        return "stripe";
    }

    public function getLogo()
    {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkREOTAyQUU1RTE5MDExRTM4OTdFQjQ1OEFFNjBCRjQ3IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkREOTAyQUU2RTE5MDExRTM4OTdFQjQ1OEFFNjBCRjQ3Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6REQ5MDJBRTNFMTkwMTFFMzg5N0VCNDU4QUU2MEJGNDciIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6REQ5MDJBRTRFMTkwMTFFMzg5N0VCNDU4QUU2MEJGNDciLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6bxuFHAAAE7ElEQVR42uyaaWhUVxTHJ+NLY+KCrThRW/e4VusWbSVqrRg0tFZI1eJCXcB+sC1VKaIiKC0tCH4q2rrUDVxQFJHYithGW0rRDy7VVqMtrnGtS2Lqmibxf+D/5HK4b0zGTDJl7oEfmbn3zX3v/u855577SEpVVVUomS0cSnJzAjgBnABOACeAE8AJ4ARwAjgBnABOACeAE8AJkHzmZbWK1Of9O4KvQTY4BGaBc3UqQD1OvgEn/za/vwvk/dxY8F+iC9AU9ANZoDcoBktiGGOgahvA9tuJngMmgf1gNfgYvBnDGGXgD9V2HNz9PyTBnur7wxjGEDf/BPwKSsABMLsu3f95QiBLfS+PcZw/wRDwUl26fZAA3cB7oA9oBh6Ai+AI+AX8DVqB0bzGtFfBfHrU76AQzARp7L8PVoDXwQSu8nzeryW/y7NcBetBZ/A+qOSYl8AG0B98ALozVA6wvSzAu98BI7nbhLjD7AEF/kUpnVq2kL/TwVLwYoBQV0AOGAW+fYaoS8k11f4lmAPS+cDdKFRX45pToAeF2W60X+V954LGatxCXl9itLWm4KMDnnEbmCEiikqDwcookxfL4Cr2rIZXHebKmasi29sCTt5PgJWWlSsLCCnxks8tkxcbzj5zd9keZfJi48E34gAiwFQVCuL6v4GD4Abb/qHCmXS9SksOKOU1RTKw6k9RbRmWa6KZf+1NbrnaJoKX+fkjMEg92zKw0bKTDZOJd1EdO9kZ4qB5XIHH4DMmrF2gjXLDT7nSkivaBkzkXwpUzLiviQgFrBRljC1ced+ag75csGnqd2u52/jzecvoG+tZVnMoc4LEyWXwndF3gfFYbnHd05YVM+0EmAJOggq2pdZAgK3gLD+vUgL4O1OxZYfaZnzerQTIDjNmTXsFrGH7F6CD6k+zTLBBNSbwMzgKHsW41zc0Pp8xRPRNclgv9WyyuLn0imn0EtMi4gHLQT5orzolNBaCD8E8sO45t9wXanH7vseQTDfaUhmeeiucF2WctDDdSuL8e8awtgjDIC+RTrGWKjaWYqypn/2LWDTkslAZo9QMM7vuseSMUIBw8bRMi0fdDDhHSKF0PiDflIgAjehSYvtIdx50clSS8ap5pkiJw6TNmB9sucdZVQyFjDltinYYkupsB128uVGRHbY8QJXx17SBPNfnsAorj4MAGfz7Gqs400p5kpTT5XXVt4gltJnEs5nfungcMJ+c5z6eyrrdtCJOvpL7bUdVehawX7Lt3jgIIBMZR+/MVH17eV4IcTFnGn2deXQ/zoWJcGeTBHrMU+Vte8tu4NsKI95/AG9Y3N4LyBG1YRGiTarUxcb3r8AIVeA1UeH89AAoIXCMdX6QlTIB7jPalvEkFi1DN1Ht6RbB9DWNaphU/2Jdf8pou8xzQOEzfiv54pbHrN+PFWAvlr2SYe+wettpeXNzh+/wJlPZCFf+CnNHGfffNE4mlS89dU5ZwvK0nMJdiPKiZjcfWo7kt3hW2cp7ajvDk2sevaEdc8hDVrJyxP9JBPSPw3plwpZKqzpvlmrL/fMZy/rwsplVZ0UMY1p/5wXs6TW9Qbzi3hZCFTH+vqI23wnWh8WjtkhYAcJ1JYCXoALc5wvTk8z0wo9xcSv3z9JJbk4AJ4ATwAngBHACOAGcAE4AJ4ATwAngBHACOAGcAElnTwQYAAM0EWaAkQBQAAAAAElFTkSuQmCC";
    }

    public function getConfigForInstall()
    {
        $config = array();
        $config['secret_key'] = "***";
        $config['publishable_key'] = "***";
        return $config;
    }

}
