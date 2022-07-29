<?php 
use \Firebase\JWT\JWT;  
class ControllerCommonSales extends Controller {
    private $current_user_permissions = array();
    private $has_permission_to_view_settings = false;
    private $first_link_in_settings = false;
    private $settings_routes = array();


	public function index() {

        $this->language->load('common/sales');
	 
		$this->document->setTitle($this->language->get('heading_title'));

    	$this->data['heading_title'] = $this->language->get('heading_title');

		// Check install directory exists
 		if (is_dir(dirname(DIR_APPLICATION) . '/install')) {
			$this->data['error_install'] = $this->language->get('error_install');
		} else {
			$this->data['error_install'] = '';
		}

		if (\Filesystem::isWritable('image/')) {
            // Check image directory is writable
            $file = 'image/test';

            \Filesystem::setPath($file);

            \Filesystem::put('test');

            if (!\Filesystem::isExists()) {
                $this->data['error_image'] = sprintf($this->language->get('error_image'), 'image/');
            } else {
                $this->data['error_image'] = '';

                \Filesystem::deleteFile();
            }
        } else {
            $this->data['error_image'] = sprintf($this->language->get('error_image'), 'image/');
        }

		if (\Filesystem::isWritable('image/cache')) {
            // Check image cache directory is writable
            $file = 'image/cache/test';

            \Filesystem::setPath($file);

            \Filesystem::put('test');

            if (!\Filesystem::isExists($file)) {
                $this->data['error_image_cache'] = sprintf($this->language->get('error_image_cache'), 'image/cache/');
            } else {
                $this->data['error_image_cache'] = '';

                \Filesystem::deleteFile();
            }
        } else {
            $this->data['error_image_cache'] = sprintf($this->language->get('error_image_cache'), 'image/cache/');
        }

        if (is_writable(DIR_CACHE)) {

            // Check cache directory is writable
            $file = DIR_CACHE . 'test';

            $handle = fopen($file, 'a+');

            fwrite($handle, '');

            fclose($handle);

            if (!file_exists($file)) {
                $this->data['error_cache'] = sprintf($this->language->get('error_image_cache'), DIR_CACHE);
            } else {
                $this->data['error_cache'] = '';

                unlink($file);
            }
        } else {
            $this->data['error_cache'] = sprintf($this->language->get('error_image_cache'), DIR_CACHE);
        }

        if (\Filesystem::isWritable('download/')) {
            // Check download directory is writable
            $file = 'download/test';

            \Filesystem::setPath($file);

            \Filesystem::put('test');

            if (!\Filesystem::isExists($file)) {
                $this->data['error_download'] = sprintf($this->language->get('error_download'), 'download/');
            } else {
                $this->data['error_download'] = '';

                \Filesystem::deleteFile();
            }
        } else {
            $this->data['error_download'] = sprintf($this->language->get('error_download'), 'download/');
        }

        if (is_writable(DIR_LOGS)) {
            // Check logs directory is writable
            $file = DIR_LOGS . 'test';

            $handle = fopen($file, 'a+');

            fwrite($handle, '');

            fclose($handle);

            if (!file_exists($file)) {
                $this->data['error_logs'] = sprintf($this->language->get('error_logs'), DIR_LOGS);
            } else {
                $this->data['error_logs'] = '';

                unlink($file);
            }
        } else {
            $this->data['error_logs'] = sprintf($this->language->get('error_logs'), DIR_LOGS);
        }
										
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/sales', '', 'SSL'),
      		'separator' => false
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => false,
            'separator' => false
        );

		$this->template = 'common/sales.expand';
		$this->base = 'common/base';
		$this->response->setOutput($this->render_ecwig());
  	}

}
?>
