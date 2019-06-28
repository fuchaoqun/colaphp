<?php

namespace Cola\Pagination;

class Paginator
{
    protected $_config = [
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
    ];

    protected $_curPage = 1;

    protected $_pageSize = 20;

    protected $_totalItems;

    protected $_url;

    protected $_totalPages;

    protected $_startPage;

    protected $_endPage;

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
        $this->_curPage      = intval($curPage);
        $this->_pageSize     = intval($pageSize);
        $this->_totalItems   = intval($totalItems);
        $this->_url          = $url;
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
        if (is_null($format)) $format = $this->_config['page'];

        $p = [
            '%link%'        => $this->_url,
            '%curPage%'     => $this->_curPage,
            '%pageSize%'    => $this->_pageSize,
            '%totalItems%'  => $this->_totalItems,
            '%totalPages%'  => $this->_totalPages,
            '%startPage%'   => $this->_startPage,
            '%endPage%'     => $this->_endPage,
            '%page%'        => $page
        ];

        return str_replace(array_keys($p), array_values($p), $format);
    }

    /**
     * Pages HTML
     *
     * @return string
     */
    public function html()
    {
        $this->_init();

        if ((1 >= $this->_totalPages) && (!$this->_config['showSinglePage'])) {
            return '';
        }

        $html = $this->page(null, $this->_config['prefix']) . $this->prev();

        if (1 == $this->_startPage - 1) {
            $html .= $this->page(1);
        } elseif (1 < $this->_startPage - 1) {
            $html .= $this->first();
        }

        for ($i = $this->_startPage; $i <= $this->_endPage; $i++) {
            $html .= ($i == $this->_curPage ? $this->current() : $this->page($i));
        }

        if (1 == $this->_totalPages - $this->_endPage ) {
            $html .= $this->page($this->_totalPages);
        } elseif (1 < $this->_totalPages - $this->_endPage ) {
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
        (1 > $this->_pageSize) && ($this->_pageSize = 20);
        $this->_totalPages = ceil($this->_totalItems / $this->_pageSize);
        (1 > $this->_curPage || $this->_curPage > $this->_totalPages) && ($this->_curPage = 1);

        $this->_startPage = $this->_curPage - $this->_config['prevNums'];
        (1 > $this->_startPage) && ($this->_startPage = 1);

        $this->_endPage = $this->_curPage + $this->_config['nextNums'];

        $less = ($this->_config['prevNums'] + $this->_config['nextNums']) - ($this->_endPage - $this->_startPage);
        (0 < $less) && ($this->_endPage += $less);
        ($this->_endPage > $this->_totalPages) && ($this->_endPage = $this->_totalPages);

        $less = ($this->_config['prevNums'] + $this->_config['nextNums']) - ($this->_endPage - $this->_startPage);
        (0 < $less) && ($this->_startPage -= $less);
        (1 > $this->_startPage) && ($this->_startPage = 1);
    }

    /**
     * Prefix HTML
     *
     * @param string $format
     * @return string
     */
    public function prefix($format = null)
    {
        if (is_null($format)) $format = $this->_config['prefix'];

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
        if (is_null($format)) $format = $this->_config['suffix'];

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
        if (is_null($format)) $format = $this->_config['first'];

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
        if (is_null($format)) $format = $this->_config['last'];
        return $this->page($this->_totalPages, $format);
    }

    /**
     * Prev page HTML
     *
     * @param string $format
     * @return string
     */
    public function prev($format = null)
    {
        if (1 == $this->_curPage) return '';

        if (is_null($format)) $format = $this->_config['prev'];

        $page = $this->_curPage - 1;

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
        if ($this->_curPage == $this->_totalPages) return '';

        if (is_null($format)) $format = $this->_config['next'];

        $page = $this->_curPage + 1;

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
            $format = $this->_config['current'];
        }

        return $this->page($this->_curPage, $format);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * @return int
     */
    public function getCurPage()
    {
        return $this->_curPage;
    }

    /**
     * @param int $curPage
     */
    public function setCurPage($curPage)
    {
        $this->_curPage = $curPage;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->_pageSize = $pageSize;
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->_totalItems;
    }

    /**
     * @param int $totalItems
     */
    public function setTotalItems($totalItems)
    {
        $this->_totalItems = $totalItems;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * @return mixed
     */
    public function getTotalPages()
    {
        return $this->_totalPages;
    }

    /**
     * @param mixed $totalPages
     */
    public function setTotalPages($totalPages)
    {
        $this->_totalPages = $totalPages;
    }

    /**
     * @return mixed
     */
    public function getStartPage()
    {
        return $this->_startPage;
    }

    /**
     * @param mixed $startPage
     */
    public function setStartPage($startPage)
    {
        $this->_startPage = $startPage;
    }

    /**
     * @return mixed
     */
    public function getEndPage()
    {
        return $this->_endPage;
    }

    /**
     * @param mixed $endPage
     */
    public function setEndPage($endPage)
    {
        $this->_endPage = $endPage;
    }
}