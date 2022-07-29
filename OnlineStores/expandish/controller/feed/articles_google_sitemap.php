<?php
class ControllerFeedArticlesGoogleSitemap extends Controller {
   public function index() {

	  if ($this->config->get('articles_google_sitemap_status')) {
		 $output  = '<?xml version="1.0" encoding="UTF-8"?>';
		 $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		 $this->initializer([
			'blog/post',
			'tool/image',
		]);

		 $articles = $this->post->news();
		 
		 foreach ($articles as $article) {
			$output .= '<url>';
			$output .= '<loc>' . $this->url->link('blog/post', 'post_id=' . $article['post_id']) . '</loc>';
			$output .= '<changefreq>weekly</changefreq>';
			$output .= '<priority>1.0</priority>';
			$output .= '</url>';   
		 }
		 
		 $output .= $this->getNcategories(0);
		 
		 $output .= '</urlset>';

		 $this->response->addHeader('Content-Type: application/xml; charset=utf-8');
		 $this->response->setOutput($output);
		}
	}
	
	protected function getNcategories($parent_id, $current_path = '') {
		
		$output = '';

		$this->initializer([
			'blog/category',
			'blog/post',
		]);

		$results = $this->category->getCategoriesByParent($parent_id);
		
		if(empty($results) ){

			return ;

		}
		
	  	foreach ($results as $result) {
		
			if (!$current_path) {

				$new_path = $result['category_id'];
		 
			} else {
		
				$new_path = $current_path . '_' . $result['category_id'];
		
			}

		 $output  .= '<url>';
		 
		 $output  .= '<loc>' . $this->url->link('blog/category', 'category_id=' . $new_path) . '</loc>';
		 
		 $output  .= '<changefreq>weekly</changefreq>';
		 
		 $output  .= '<priority>0.7</priority>';
		 
		 $output  .= '</url>';   
		 
		 

		$articles = $this->post->news( $result['category_id'] );

		 foreach ($articles as $article) {
			$output .= '<url>';
			$output .= '<loc>' . $this->url->link('blog/post', 'cat=' . $new_path . '&post_id=' . $article['post_id']) . '</loc>';
			$output .= '<changefreq>weekly</changefreq>';
			$output .= '<priority>1.0</priority>';
			$output .= '</url>';   
		 }   
		 
		   $output .= $this->getNcategories($result['category_id'], $new_path);
	  }

	  return $output;
   }      
}
?>