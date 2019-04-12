<?php
/**
 *
 */

class Cola_Ext_Pagination
{
    public $config = array(
        'prevNums'         => 2,
        'nextNums'         => 7,
        'showSinglePage'   => false,
        'prefix'           => '<div class="pagination">',
        'first'            => '<a href="%link%">%page%...</a>',
        'last'             => '<a href="%link%">...%page%</a>',
        'prev'             => '<a class="pn" href="%link%">&lt;&lt;</a>',
        'next'             => '<a class="pn" href="%link%">&gt;&gt;</a>',
        'current'          => '<b>%page%</b>',
        'page'             => '<a href="%link%">%page%</a>',
        'suffix'           => '</div>'
    );

    public $curPage = 1;

    public $pageSize = 20;

    public $totalItems;

    public $url;

    public $totalPages;

    public $startPage;

    public $endPage;

    /**
     * Constructor
     *
     * @param int $curPage
     * @param int $pageSize
     * @param int $totalItems
     * @param string $url
     */
    public function __construct($curPage = 1, $pageSize = 20, $totalItems, $url = '')
    {
        $this->curPage      = intval($curPage);
        $this->pageSize     = intval($pageSize);
        $this->totalItems   = intval($totalItems);
        $this->url          = $url;
    }

    /**
     * Page HTML
     *
     * @param int $page
     * @param string $format
     * @return string
     */
    public function page($page, $format = null)
    {
        if (is_null($format)) $format = $this->config['page'];

        $p = array(
            '%link%'        => $this->url,
            '%curPage%'     => $this->curPage,
            '%pageSize%'    => $this->pageSize,
            '%totalItems%'  => $this->totalItems,
            '%totalPages%'  => $this->totalPages,
            '%startPage%'     => $this->startPage,
            '%endPage%'     => $this->endPage,
            '%page%'        => $page
        );

        return str_replace(array_keys($p), $p, $format);
    }

    /**
     * Pages HTML
     *
     * @return string
     */
    public function html()
    {
        $this->_init();

        if ((1 >= $this->totalPages) && (!$this->config['showSinglePage'])) {
            return '';
        }

        $html = $this->page(null, $this->config['prefix']) . $this->prev();

        if (1 == $this->startPage - 1) {
            $html .= $this->page(1);
        } elseif (1 < $this->startPage - 1) {
            $html .= $this->first();
        }

        for ($i = $this->startPage; $i <= $this->endPage; $i++) {
            $html .= ($i == $this->curPage ? $this->current() : $this->page($i));
        }

        if (1 == $this->totalPages - $this->endPage ) {
            $html .= $this->page($this->totalPages);
        } elseif (1 < $this->totalPages - $this->endPage ) {
            $html .= $this->last();
        }

        $html .= $this->next() . $this->suffix();

        return $html;
    }

    public function display()
    {
        echo $this->html();
    }

    public function _init()
    {
        (1 > $this->pageSize) && ($this->pageSize = 20);
        $this->totalPages = ceil($this->totalItems / $this->pageSize);
        (1 > $this->curPage || $this->curPage > $this->totalPages) && ($this->curPage = 1);

        $this->startPage = $this->curPage - $this->config['prevNums'];
        (1 > $this->startPage) && ($this->startPage = 1);

        $this->endPage = $this->curPage + $this->config['nextNums'];

        $less = ($this->config['prevNums'] + $this->config['nextNums']) - ($this->endPage - $this->startPage);
        (0 < $less) && ($this->endPage += $less);
        ($this->endPage > $this->totalPages) && ($this->endPage = $this->totalPages);

        $less = ($this->config['prevNums'] + $this->config['nextNums']) - ($this->endPage - $this->startPage);
        (0 < $less) && ($this->startPage -= $less);
        (1 > $this->startPage) && ($this->startPage = 1);
    }

    /**
     * Prefix HTML
     *
     * @param string $format
     * @return string
     */
    public function prefix($format = null)
    {
        if (is_null($format)) $format = $this->config['prefix'];

        return $this->page(null, $format);
    }

    /**
     * Suffix HTML
     *
     * @param string $format
     * @return string
     */
    public function suffix($format = null)
    {
        if (is_null($format)) $format = $this->config['suffix'];

        return $this->page(null, $format);
    }

    /**
     * First page HTML
     *
     * @param string $format
     * @return string
     */
    public function first($format = null)
    {
        if (is_null($format)) $format = $this->config['first'];

        return $this->page(1, $format);
    }

    /**
     * Last page HTML
     *
     * @param string $format
     * @return string
     */
    public function last($format = null)
    {
        if (is_null($format)) $format = $this->config['last'];
        return $this->page($this->totalPages, $format);
    }

    /**
     * Prev page HTML
     *
     * @param string $format
     * @return string
     */
    public function prev($format = null)
    {
        if (1 == $this->curPage) return '';

        if (is_null($format)) $format = $this->config['prev'];

        $page = $this->curPage - 1;

        return $this->page($page, $format);
    }

    /**
     * Next page HTML
     *
     * @param string $format
     * @return string
     */
    public function next($format = null)
    {
        if ($this->curPage == $this->totalPages) return '';

        if (is_null($format)) $format = $this->config['next'];

        $page = $this->curPage + 1;

        return $this->page($page, $format);
    }

    /**
     * Current page HTML
     *
     * @param string $format
     * @return string
     */
    public function current($format = null)
    {
        if (is_null($format)) {
            $format = $this->config['current'];
        }

        return $this->page($this->curPage, $format);

    }
}