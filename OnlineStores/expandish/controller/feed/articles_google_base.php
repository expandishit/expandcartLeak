<?php 
class ControllerFeedArticlesGoogleBase extends Controller {
	public function index() {

		if ($this->config->get('google_base_status')) { 
			$output  = '<?xml version="1.0" encoding="UTF-8" ?>';
			$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
            $output .= '<channel>';
			$output .= '<title>' . $this->config->get('config_name') . '</title>'; 
			$output .= '<description>' . $this->config->get('config_meta_description') . '</description>';
			$output .= '<link>' . HTTP_SERVER . '</link>';

			$this->initializer([
                'blog/post',
				'tool/image',
			]);
            

			$articles = $this->post->news();
			
			foreach ($articles as $article) {
				
				if ($article['description']) {
					
					$output .= '<item>';
					
					$output .= '<title>' . $article['name'] . '</title>';

					$output .= '<link>' . $this->url->link('blog/post', 'post_id=' . $article['post_id']) . '</link>';
					
					$output .= '<description>' . $article['description'] . '</description>';
					
					$output .= '<g:id>' . $article['post_id'] . '</g:id>';
					
					if (isset($article['image']) && $article['image'] ) {
					
						$output .= '<g:image_link>' . $this->image->resize($article['image'], 500, 500) . '</g:image_link>';
					
					} else {
					
						$output .= '<g:image_link>' . $this->image->resize('no_image.jpg', 500, 500) . '</g:image_link>';
					
					}
					
					$output .= '</item>';
				}
			}
			
			$output .= '</channel>'; 
			$output .= '</rss>';	
			
			$this->response->addHeader('Content-Type: application/rss+xml; charset=utf-8');
            $this->response->setOutput($output);
            
		}
	}
			
}
?>