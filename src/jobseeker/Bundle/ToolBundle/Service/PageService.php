<?php

namespace jobseeker\Bundle\ToolBundle\Service;

class PageService
{

    private $current;
    private $total;
    private $offset;
    private $pattern;
    private $urls = array();
    private $translator;

    public function __construct($pattern, $current, $offset, $total, $translator)
    {
        $this->pattern = (string)$pattern;
        $this->current = max((int)$current, 1);
        $this->offset = (int)$offset;
        $this->total = (int)$total;
        $this->translator = $translator;
    }

    private function trans($mess)
    {
        return $this->translator->trans($mess);
    }

    public function getCurrentPage()
    {
        return $this->current;
    }

    public function setCurrentPage($page = 1)
    {
        $this->current = $page;
    }

    public function getPattern($pageNumber)
    {
        return sprintf($this->pattern, $pageNumber);
    }

    public function getPriviousPage()
    {
        return $this->getCurrentPage() > 1 ? $this->getCurrentPage() - 1 : 1;
    }

    public function getNextPage()
    {
        return $this->getCurrentPage() < $this->getPages() ? $this->getCurrentPage() + 1 : $this->getPages();
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function getPages()
    {
        return intval(ceil($this->getTotal() / $this->getOffset())) ?: 1;
    }

    public function generateUrl()
    {
        if ($this->getPages() > 5) {
            if ($this->getPages() > 1 && $this->getCurrentPage() != 1) {
                $this->urls["first"] = '<a href="' . $this->getPattern(1) . '" class="first">' . $this->trans("common.page.first") . '</a>';
            }
            if ($this->getPriviousPage() != $this->getCurrentPage()) {
                $this->urls["previous"] = '<a href="' . $this->getPattern($this->getPriviousPage()) . '" class="previous">' . $this->trans("common.page.previous") . '</a>';
            }

            if ($this->getCurrentPage() <= 3) {
                for ($i = 1; $i <= 3; $i++) {
                    $current = $i == $this->getCurrentPage() ? ' class="current"' : '';
                    $this->urls[$i] = '<a href="' . $this->getPattern($i) . '"' . $current . '>' . $i . '</a>';
                }
                $this->urls["link"] = "....";
            } else if ($this->getCurrentPage() > 3 && $this->getCurrentPage() <= $this->getPages() - 3) {
                $this->urls["link"] = "...";
                for ($i = $this->getPriviousPage(); $i <= $this->getPriviousPage() + 2; $i++) {
                    $current = $i == $this->getCurrentPage() ? ' class="current"' : '';
                    $this->urls[$i] = '<a href="' . $this->getPattern($i) . '"' . $current . '>' . $i . '</a>';
                }
                $this->urls["link2"] = "...";
            } else {
                $this->urls["link"] = "....";
                for ($i = $this->getPages() - 2; $i <= $this->getPages(); $i++) {
                    $current = $i == $this->getCurrentPage() ? ' class="current"' : '';
                    $this->urls[$i] = '<a href="' . $this->getPattern($i) . '"' . $current . '>' . $i . '</a>';
                }
            }

            if ($this->getCurrentPage() != $this->getPages()) {
                $this->urls["next"] = '<a href="' . $this->getPattern($this->getNextPage()) . '" class="next">' . $this->trans("common.page.next") . '</a>';
            }
            if ($this->getPages() > 1 && $this->getCurrentPage() != $this->getPages()) {
                $this->urls["last"] = '<a href="' . $this->getPattern($this->getPages()) . '" class="last">' . $this->trans("common.page.last") . '</a>';
            }
        } else {
            if ($this->getPages() > 1) {
                for ($i = 1; $i <= $this->getPages(); $i++) {
                    $current = $i == $this->getCurrentPage() ? ' class="current"' : '';
                    $this->urls[$i] = '<a href="' . $this->getPattern($i) . '"' . $current . '>' . $i . '</a>';
                }
            }
        }

        return $this->urls;
    }

    public function getUrlBaseNumber($pageNumber = NULL)
    {
        return $pageNumber === NULL ? $this->urls[$this->getCurrentPage()] : $this->urls[$pageNumber];
    }

}
