<?php
class Pagination {
    public $total = 0;
    public $page = 1;
    public $limit = 20;
    public $num_links = 8;
    public $url = '';
    public $text_first = '|&lt;';
    public $text_last = '&gt;|';
    public $text_next = '<span><i aria-hidden="true" class="fa fa-angle-right"></i></span>';
    public $text_prev = '<span><i aria-hidden="true" class="fa fa-angle-left"></i></span>';

    public function render() {
        $total = $this->total;

        if ($this->page < 1) {
            $page = 1;
        } else {
            $page = $this->page;
        }

        if (!(int)$this->limit) {
            $limit = 10;
        } else {
            $limit = $this->limit;
        }

        $num_links = $this->num_links;
        $num_pages = ceil($total / $limit);

        $this->url = str_replace('%7Bpage%7D', '{page}', $this->url);
        $this->url = str_replace('&amp;page', '&page', $this->url);

        $output = '<ul class="pagination">';

        if ($page > 1) {
            // $output .= '<li><a style="padding: 9px 14px;" href="' . str_replace('&amp;page={page}', '', $this->url) . '">' . $this->text_first . '</a></li>';
            if($page - 1 === 1){
                $findPage = array('&page={page}' , '?page={page}');
                $output .= '<li><a style="padding: 9px 14px;" href="' . str_replace($findPage , '', $this->url) . '">' . $this->text_prev . '</a></li>';
            } else {
                $output .= '<li><a style="padding: 9px 14px;" href="' . str_replace('{page}', $page - 1, $this->url) . '">' . $this->text_prev . '</a></li>';
            }
        }

        if ($num_pages > 1) {
            if ($num_pages <= $num_links) {
                $start = 1;
                $end = $num_pages;
            } else {
                $start = $page - floor($num_links / 2);
                $end = $page + floor($num_links / 2);

                if ($start < 1) {
                    $end += abs($start) + 1;
                    $start = 1;
                }

                if ($end > $num_pages) {
                    $start -= ($end - $num_pages);
                    $end = $num_pages;
                }
            }

            for ($i = $start; $i <= $end; $i++) {
                if ($page == $i) {
                    $output .= '<li class="active"><a href="#" onclick="return false;">' . $i . '</a></li>';
                } else {if($i === 1){
                    $find = array('&page={page}' , '?page={page}');
                    $output .= '<li><a href="' . str_replace($find, '', $this->url) . '">' . $i . '</a></li>';
                } else {
                    $output .= '<li><a href="' . str_replace('{page}', $i, $this->url) . '">' . $i . '</a></li>';
                }
                }
            }
        }

        if ($page < $num_pages) {
            $output .= '<li><a style="padding: 9px 14px;" class="pagination__next" href="' . str_replace('{page}', $page + 1, $this->url) . '">' . $this->text_next . '</a></li>';
            //$output .= '<li><a href="' . str_replace('{page}', $num_pages, $this->url) . '">' . $this->text_last . '</a></li>';
        }

        $output .= '</ul>';

        if ($num_pages > 1) {
            return $output;
        } else {
            return '';
        }
    }
}
