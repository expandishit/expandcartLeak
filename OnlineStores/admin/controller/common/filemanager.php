<?php

class ControllerCommonFileManager extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('common/filemanager');

        if (isset($this->request->get['filter_name'])) {
            $filter_name = rtrim(
                str_replace(array('../', '..\\', '..', '*'), '', $this->request->get['filter_name']),
                '/'
            );
        } else {
            $filter_name = null;
        }
        
        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(
                str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']),
                '/'
            );
        } else {
            $directory = 'image/data';
        }

        if ($directory == '') {
            $directory = 'image/data';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['images'] = array();

        $this->load->model('tool/image');

        // Get directories
        $directories = glob(
            $directory . '/' . preg_replace_callback(
                '/(\w)/',
                function ($m) {
                    return '[' . strtoupper($m[1]) . strtolower($m[1]) . ']';
                },
                $filter_name
            ) . '*',
            GLOB_ONLYDIR);

        if (!$directories) {
            $directories = array();
        }
        // Get files
        $files = glob(
            $directory . '/' . preg_replace_callback(
                '/(\w)/',
                function ($m) {
                    return '[' . strtoupper($m[1]) . strtolower($m[1]) . ']';
                },
                $filter_name
            ) . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}',
            GLOB_BRACE
        );

        if (!$files) {
            $files = array();
        }

        // Merge directories and files
        // $images = array_merge($directories, $files);
        $images = \Filesystem::list($directory);

        if ($filter_name && mb_strlen($filter_name) > 0) {
            $images = array_filter($images, function ($v) use ($filter_name) {

                if (strpos(strtolower($v['basename']), strtolower($filter_name)) !== false) {
                    return $v;
                }

            });
        }

        // Get total number of files and directories
        $image_total = count($images);

        // Split the array based on current page number and max number of items per page of 10
        $images = array_splice($images, ($page - 1) * 16, 16);

        $url = '';

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $allowedImages = [
            'jpg' => true,
            'jpeg' => true,
            'png' => true,
            'gif' => true,
            'JPG' => true,
            'JPEG' => true,
            'PNG' => true,
            'GIF' => true,
        ];

        foreach ($images as $image) {
            if ($image['type'] == 'dir') {
                $data['images'][] = [
                    'thumb' => '',
                    'name' => $image['basename'],
                    'type' => 'directory',
                    'path' => $directory . '/' . $image['basename'],
                    'href' => $this->url->link(
                        'common/filemanager',
                        'directory=' . urlencode($directory . '/' . $image['basename']) . $url,
                        true
                    )
                ];
            } else if ($image['type'] == 'file' && isset($allowedImages[$image['extension']])) {
                $imageUrl = \Filesystem::getUrl($directory . '/' . $image['basename']);
                $data['images'][] = array(
                    'thumb' => $imageUrl,
                    'name' => $image['filename'],
                    'type' => 'image',
                    'size' => $image['size'],
                    'path' => utf8_substr($image['path'], utf8_strlen(\Filesystem::getPathPrefix() . 'image/')),
                    'href' => $imageUrl
                );
            }
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_upload_help'] = $this->language->get('text_upload_help');

        $data['entry_search'] = $this->language->get('entry_search');
        $data['entry_folder'] = $this->language->get('entry_folder');

        $data['button_parent'] = $this->language->get('button_parent');
        $data['button_refresh'] = $this->language->get('button_refresh');
        $data['button_upload'] = $this->language->get('button_upload');
        $data['button_folder'] = $this->language->get('button_folder');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_search'] = $this->language->get('button_search');

        $data['token'] = null;

        if (isset($this->request->get['directory'])) {
            $data['directory'] = urlencode($this->request->get['directory']);
        } else {
            $data['directory'] = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $data['filter_name'] = $this->request->get['filter_name'];
        } else {
            $data['filter_name'] = '';
        }

        // Return the target ID for the file manager to set the value
        if (isset($this->request->get['target'])) {
            $data['target'] = $this->request->get['target'];
        } else {
            $data['target'] = '';
        }

        // Return the thumbnail for the file manager to show a thumbnail
        if (isset($this->request->get['thumb'])) {
            $data['thumb'] = $this->request->get['thumb'];
        } else {
            $data['thumb'] = '';
        }

        if (isset($this->request->get['editorFunc'])) {
            $data['editorFunc'] = $this->request->get['editorFunc'];
        } else {
            $data['editorFunc'] = '';
        }

        if (isset($this->request->get['callerName'])) {
            $data['callerName'] = $this->request->get['callerName'];
        } else {
            $data['callerName'] = '';
        }

        // Parent
        $url = '';

        if (isset($this->request->get['directory'])) {
            $pos = strrpos($this->request->get['directory'], '/');

            if ($pos && $this->request->get['directory'] != 'image/data') {
                $url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
            }
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $data['parent'] = $this->url->link('common/filemanager', $url, true);

        // Refresh
        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode($this->request->get['directory']);
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $data['refresh'] = $this->url->link(
            'common/filemanager', $url, true
        );

        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode(html_entity_decode(
                    $this->request->get['directory'],
                    ENT_QUOTES,
                    'UTF-8'
                ));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode(
                    $this->request->get['filter_name'],
                    ENT_QUOTES,
                    'UTF-8'
                ));
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $pagination = new Pagination();
        $pagination->total = $image_total;
        $pagination->page = $page;
        $pagination->limit = 16;
        $pagination->url = $this->url->link(
            'common/filemanager', $url . '&page={page}', true
        );

        $data['pagination'] = $pagination->render();

        $this->data = $data;

        $this->data['title'] = $this->language->get('heading_title');

        $this->template = 'common/filemanager.expand';

        $this->response->setOutput($this->render_ecwig());

        //$this->response->setOutput($this->load->view('common/filemanager', $data));
    }

    public function show_md_manager()
    {
        $this->language->load('common/filemanager');

        if (isset($this->request->get['filter_name'])) {
            $filter_name = rtrim(
                str_replace(array('../', '..\\', '..', '*'), '', $this->request->get['filter_name']),
                '/'
            );
        } else {
            $filter_name = null;
        }

        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(
                str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']),
                '/'
            );
        } else {
            $directory = 'image/data';
        }

        if ($directory == '') {
            $directory = 'image/data';
        }

        if (isset($this->request->get['page']) && is_numeric($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['images'] = array();
        $images = \Filesystem::list($directory);

        if ($filter_name && mb_strlen($filter_name) > 0) {
            $images = array_filter($images, function ($v) use ($filter_name) {

                if (strpos(strtolower($v['basename']), strtolower($filter_name)) !== false) {
                    return $v;
                }

            });
        }

        // Get total number of files and directories
        $image_total = count($images);

        // Split the array based on current page number and max number of items per page of 10
        $images = array_splice($images, ($page - 1) * 16, 16);

        $url = '';

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $allowedImages = [
            'jpg' => true,
            'jpeg' => true,
            'png' => true,
            'gif' => true,
            'JPG' => true,
            'JPEG' => true,
            'PNG' => true,
            'GIF' => true,
        ];

        foreach ($images as $image) {
            if ($image['type'] == 'dir') {
                $data['images'][] = [
                    'thumb' => '',
                    'name' => $image['basename'],
                    'type' => 'directory',
                    'path' => $directory . '/' . $image['basename'],
                    'href' => $this->url->link(
                        'common/filemanager/show_md_manager',
                        'directory=' . urlencode($directory . '/' . $image['basename']) . $url,
                        true
                    )
                ];
            } else if ($image['type'] == 'file' && isset($allowedImages[$image['extension']])) {
                $imageUrl = \Filesystem::getUrl($directory . '/' . $image['basename']);
                $data['images'][] = array(
                    'thumb' => $imageUrl,
                    'name' => $image['filename'],
                    'type' => 'image',
                    'size' => $image['size'],
                    'path' => utf8_substr($image['path'], utf8_strlen(\Filesystem::getPathPrefix() . 'image/')),
                    'href' => $imageUrl
                );
            }
        }
        // This function returns two elements filtered images and total images
        // $return_images_array = $this->getDirectoriesImages($directory, $page, $filter_name);
        // $data['images'] = $return_images_array[0];
        // $image_total = $return_images_array[1];

        $data['heading_title'] = $this->language->get('heading_title');
        $data['pg_heading_title'] = $this->language->get('pg_heading_title');

        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_upload_help'] = $this->language->get('text_upload_help');

        $data['entry_search'] = $this->language->get('entry_search');
        $data['entry_folder'] = $this->language->get('entry_folder');

        $data['button_parent'] = $this->language->get('button_parent');
        $data['button_refresh'] = $this->language->get('button_refresh');
        $data['button_upload'] = $this->language->get('button_upload');
        $data['button_folder'] = $this->language->get('button_folder');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_search'] = $this->language->get('button_search');
        $data['link_product_option'] = $this->config->get('link_product_option') ?? 'dont_link';
        $data['token'] = null;

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('pg_heading_title'),
            'href' => $this->url->link('common/filemanager/show_md_manager', '', 'SSL'),
            'separator' => ' :: '
        );


        if (isset($this->request->get['directory'])) {
            $data['directory'] = urlencode($this->request->get['directory']);
        } else {
            $data['directory'] = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $data['filter_name'] = $this->request->get['filter_name'];
        } else {
            $data['filter_name'] = '';
        }

        // Return the target ID for the file manager to set the value
        if (isset($this->request->get['target'])) {
            $data['target'] = $this->request->get['target'];
        } else {
            $data['target'] = '';
        }

        // Return the thumbnail for the file manager to show a thumbnail
        if (isset($this->request->get['thumb'])) {
            $data['thumb'] = $this->request->get['thumb'];
        } else {
            $data['thumb'] = '';
        }

        if (isset($this->request->get['editorFunc'])) {
            $data['editorFunc'] = $this->request->get['editorFunc'];
        } else {
            $data['editorFunc'] = '';
        }

        if (isset($this->request->get['callerName'])) {
            $data['callerName'] = $this->request->get['callerName'];
        } else {
            $data['callerName'] = '';
        }

        // Parent
        $url = '';

        if (isset($this->request->get['directory'])) {
            $pos = strrpos($this->request->get['directory'], '/');

            if ($pos) {
                $url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
            }
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $data['parent'] = $this->url->link('common/filemanager/show_md_manager', $url, true);

        // Refresh
        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode($this->request->get['directory']);
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $data['refresh'] = $this->url->link(
            'common/filemanager/show_md_manager', $url, true
        );

        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode(html_entity_decode(
                    $this->request->get['directory'],
                    ENT_QUOTES,
                    'UTF-8'
                ));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode(
                    $this->request->get['filter_name'],
                    ENT_QUOTES,
                    'UTF-8'
                ));
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        if (isset($this->request->get['editorFunc'])) {
            $url .= '&editorFunc=' . $this->request->get['editorFunc'];
        }

        if (isset($this->request->get['callerName'])) {
            $url .= '&callerName=' . $this->request->get['callerName'];
        }

        $pagination = new Pagination();
        $pagination->total = $image_total;
        $pagination->page = $page;
        $pagination->limit = 16;
        $pagination->url = $this->url->link(
            'common/filemanager/show_md_manager', $url . "&page={page}", true
        );
        $data['pagination'] = $pagination->render();

        $this->data = $data;

        $this->data['title'] = $this->language->get('heading_title');

        $this->template = 'common/filemanager_page.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());


    }

    function getFilename($path)
    {
        // if there's no '/', we're probably dealing with just a filename
        // so just put an 'a' in front of it
        if (strpos($path, '/') === false) {
            $path_parts = pathinfo('a' . $path);
        } else {
            $path = str_replace('/', '/a', $path);
            $path_parts = pathinfo($path);
        }
        $filename = $path_parts["filename"];

        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr(0));

        $filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
        $filename = str_replace($special_chars, '', $filename);
        $filename = str_replace(array('%20', '+'), '-', $filename);
        $filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
        $filename = trim($filename, '.-_');

        return substr($filename, 1);
    }

    public function getDirectoriesImages($directory, $page, $filter_name)
    {
        $filter_name = trim($filter_name);
        
        $this->load->model('tool/image');
        $returned_images = array();

        $images = \Filesystem::list($directory);

        if ($filter_name && mb_strlen($filter_name) > 0) {
            $images = array_filter($images, function ($v) use ($filter_name) {

                if (strpos(strtolower($v['basename']), strtolower($filter_name)) !== false) {
                    return $v;
                }

            });
        }

        $allowedImages = [
            'jpg' => true,
            'jpeg' => true,
            'png' => true,
            'gif' => true,
            'JPG' => true,
            'JPEG' => true,
            'PNG' => true,
            'GIF' => true,
        ];

        // Get total number of files and directories
        $image_total = count($images);

        // Split the array based on current page number and max number of items per page of 10
        $images = array_splice($images, ($page - 1) * 16, 16);

        $imagesData = [];

        foreach ($images as $image) {
            if ($image['type'] == 'dir') {
                $imagesData[] = [
                    'thumb' => '',
                    'name' => $image['basename'],
                    'type' => 'directory',
                    'path' => $directory . '/' . $image['basename'],
                    'href' => $this->url->link(
                        'common/filemanager/show_md_manager',
                        'directory=' . urlencode(
                            substr($directory . '/' . $image['basename'], strlen('image/data/'))
                        ) . $url,
                        true
                    )
                ];
            } else if ($image['type'] == 'file' && isset($allowedImages[$image['extension']])) {
                $imageUrl = \Filesystem::getUrl($directory . '/' . $image['basename']);
                $imagesData[] = array(
                    'thumb' => $this->model_tool_image->resize(
                        utf8_substr($image['path'], utf8_strlen(\Filesystem::getPathPrefix() . 'image/')),
                        150,
                        150
                    ),
                    'name' => $image['filename'],
                    'type' => 'image',
                    'size' => $image['size'],
                    'path' => utf8_substr($image['path'], utf8_strlen(\Filesystem::getPathPrefix() . 'image/')),
                    'href' => $imageUrl
                );
            }
        }

        return [$imagesData, $image_total];

    }

    public function __getDirectoriesImages($directory, $page, $filter_name)
    {
        $this->load->model('tool/image');

        $returned_images = array();
        // Get directories
        $directories = glob(
            $directory . '/' . preg_replace_callback(
                '/(\w)/',
                function ($m) {
                    return '[' . strtoupper($m[1]) . strtolower($m[1]) . ']';
                },
                $filter_name
            ) . '*',
            GLOB_ONLYDIR);

        if (!$directories) {
            $directories = array();
        }

        // Get files
        $files = glob(
            $directory . '/' . preg_replace_callback(
                '/(\w)/',
                function ($m) {
                    return '[' . strtoupper($m[1]) . strtolower($m[1]) . ']';
                },
                $filter_name
            ) . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}',
            GLOB_BRACE
        );

        if (!$files) {
            $files = array();
        }

        // Merge directories and files
        $images = array_merge($directories, $files);

        // Get total number of files and directories
        $image_total = count($images);

        // Split the array based on current page number and max number of items per page of 10
        $images = array_splice($images, ($page - 1) * 16, 16);

        foreach ($images as $image) {
            $name = str_split($this->getFilename($image), 14);

            if (is_dir($image)) {
                $url = '';

                if (isset($this->request->get['target'])) {
                    $url .= '&target=' . $this->request->get['target'];
                }

                if (isset($this->request->get['thumb'])) {
                    $url .= '&thumb=' . $this->request->get['thumb'];
                }

                if (isset($this->request->get['editorFunc'])) {
                    $url .= '&editorFunc=' . $this->request->get['editorFunc'];
                }

                if (isset($this->request->get['callerName'])) {
                    $url .= '&callerName=' . $this->request->get['callerName'];
                }

                $returned_images[] = array(
                    'thumb' => '',
                    'name' => implode(' ', $name),
                    'type' => 'directory',
                    'path' => utf8_substr($image, utf8_strlen(DIR_IMAGE)),
                    'href' => $this->url->link(
                        'common/filemanager/show_md_manager',
                        'directory=' . urlencode(utf8_substr($image, utf8_strlen(DIR_IMAGE . 'data/'))) . $url,
                        true
                    )
                );
            } elseif (is_file($image)) {
                // Find which protocol to use to pass the full image link back
                if ($this->request->server['HTTPS']) {
                    $server = HTTPS_IMAGE;
                } else {
                    $server = HTTP_IMAGE;
                }

                $returned_images[] = array(
                    'thumb' => $this->model_tool_image->resize(utf8_substr($image, utf8_strlen(DIR_IMAGE)), 150, 150),
                    'name' => implode(' ', $name),
                    'type' => 'image',
                    'size' => filesize($image),
                    'path' => utf8_substr($image, utf8_strlen(DIR_IMAGE)),
                    'href' => $server . utf8_substr($image, utf8_strlen(DIR_IMAGE))
                );
            }
        }
        return array($returned_images, $image_total);

    }

    public function dropzone()
    {
        $this->language->load('common/filemanager');
        $resolveFiles = function ($files) {


            $data = $json = [];

            foreach ($files['name'] as $key => $name) {

                if (isset($name) && empty($name) == false && is_file($files['tmp_name'][$key])) {
                    $name = str_replace(' ','_',$name);
                    $filename =
                        html_entity_decode(
                            $name, ENT_QUOTES, 'UTF-8'
                        );

                    if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
                        $json['error'] = $this->language->get('error_filename');
                    }

                    if ($files['size'][$key] > 1048576) {
                        $json['error'] = $this->language->get('error_file_size');
                    }

                    $allowed = array(
                        'jpg',
                        'jpeg',
                        'gif',
                        'png'
                    );

                    if (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $allowed)) {
                        $json['error'] = $this->language->get('error_file_type');
                    }

                    $allowed = array(
                        'image/jpeg',
                        'image/pjpeg',
                        'image/png',
                        'image/x-png',
                        'image/gif'
                    );

                    if (!in_array($files['type'][$key], $allowed)) {
                        $json['error'] = $this->language->get('error_file_type');
                    }

                    if ($files['error'][$key] != UPLOAD_ERR_OK) {
                        $json['error'] = $this->language->get(
                            'error_upload_' . $this->request->files['file']['error']
                        );
                    }

                    $data[$key]['name'] = (time() . '_' . $filename);
                    $data[$key]['type'] = $files['type'][$key];
                    $data[$key]['tmp_name'] = $files['tmp_name'][$key];
                    $data[$key]['size'] = $files['size'][$key];
                }
            }

            return [
                'hasErrors' => (count($json) > 0 ? $json : false),
                'files' => $data
            ];
        };

        $json = array();

        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['createProductDirectory']) && $this->request->post['createProductDirectory'] && isset($this->request->post['product_id'])) {
            $rotate360Directory = rtrim('image/data/' .
                str_replace(['../', '..\\', '..'], '', 'products/rotate360/'), '/'
            );
            /*if (!is_dir($rotate360Directory)) {
                if (mkdir($rotate360Directory, 0777)) {
                    chmod($rotate360Directory, 0777);
                } else {
                    $rotate360Directory = DIR_IMAGE . 'data';
                }
            }*/
            $directory = $rotate360Directory .'/'. $this->request->post['product_id'] . '/';
        } else {
            $directory = rtrim(
                'image/data/' . str_replace(['../', '..\\', '..'], '', 'products/'), '/'
            );
        }

        if ($directory == '') {
            $directory = 'image/data';
        }

        /*if (!is_dir($directory)) {
            if (mkdir($directory, 0777)) {
                chmod($directory, 0777);
            } else {
                $directory = DIR_IMAGE . 'data';
            }
        }*/

        $files = $resolveFiles($this->request->files['file']);

        if ($files['hasErrors'] === false && !$json) {
            $response = [];
            foreach ($files['files'] as $key => $file) {
                // move_uploaded_file($file['tmp_name'], $directory . '/' . $file['name']);
                $fn = $directory . '/' . $file['name'];
                $uploaded = \Filesystem::setPath($fn)->upload($file['tmp_name']);

                $this->load->model('tool/image');

                $response['success'] = $this->language->get('text_uploaded');
                $response['fileData'] = array(
                    'thumb' => $this->model_tool_image->resize(
                        utf8_substr($directory . '/' . $file['name'], utf8_strlen('image/')), 120, 120
                    ),
                    'name' => $file['name'],
                    'type' => 'image',
                    'image' => utf8_substr($directory . '/' . $file['name'], utf8_strlen('image/')),
                    'href' => \Filesystem::getUrl($fn),
                    'path' => $uploaded['path'],
                );
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));
        } else {
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode(array_merge($files['hasErrors'], $json)));
        }
    }

    public function upload()
    {
        $this->language->load('common/filemanager');

        $json = array();

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(
                str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']),
                '/'
            );
        } else {
            $directory = 'image/data';
        }

        if ($directory == '') {
            $directory = 'image/data';
        }

        // Check its a directory
        /*if (!is_dir($directory)) {
            $directory = 'image';
            // $json['error'] = $this->language->get('error_directory');
        }*/

        if (!$json) {
            if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
                // Sanitize the filename
                $filename = html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8');
                $filename = str_replace(' ', '_', $filename);
                // Validate the filename length
                if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
                    $json['error'] = $this->language->get('error_filename');
                }

                if ($this->request->files['file']['size'] > 1048576) {
                    $json['error'] = $this->language->get('error_file_size');
                }

                // Allowed file extension types
                $allowed_extensions = array(
                    'jpg',
                    'jpeg',
                    'gif',
                    'png'
                );

                if (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $allowed_extensions)) {
                    $json['error'] = $this->language->get('error_file_type');
                }

                // Allowed file mime types
                $allowed = array(
                    'image/jpeg',
                    'image/pjpeg',
                    'image/png',
                    'image/x-png',
                    'image/gif'
                );

                if (!in_array($this->request->files['file']['type'], $allowed)) {
                    $json['error'] = $this->language->get('error_file_type');
                }

                // Return any upload error
                if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                    $json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
                }
            } else {
                $json['error'] = $this->language->get('error_file');
            }
        }

        if (!$json) {
            $fn = $directory . '/' . $filename;
            $u = \Filesystem::setPath($fn)->upload($this->request->files['file']['tmp_name']);
            $files = glob('../ecdata/stores/' . STORECODE . '/' . str_replace('image/', 'image/cache/', $directory) . '/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-[0-9]*x[0-9]*.' . utf8_substr($filename, utf8_strrpos($filename, '.') + 1, strlen($filename)));
            array_walk($files, function(&$value, $key) use($directory){
                $path = str_replace('../ecdata/stores/' . STORECODE . '/', '', $value);
                \Filesystem::deleteFile($path);
            });

            $this->load->model('tool/image');

            $imageUrl = \Filesystem::getUrl($directory . '/' . $u['file_name']);

            $json['success'] = $this->language->get('text_uploaded');
            $json['fileData'] = array(
                'thumb' => $imageUrl,
                'name' => implode(' ', $filename),
                'type' => 'image',
                'path' => utf8_substr($u['path'], utf8_strlen('image/')),
                'href' => $imageUrl,
            );

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            $this->response->addHeader('HTTP/1.1 500 Internal Server');
            $this->response->addHeader('Content-Type: application/json; charset=UTF-8');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function folder()
    {
        $this->language->load('common/filemanager');

        $json = array();

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(
                str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']),
                '/'
            );
        } else {
            $directory = 'image/data';
        }

        if ($directory == '') {
            $directory = 'image/data';
        }

        // Check its a directory
        /*if (!is_dir($directory)) {
            $json['error'] = $this->language->get('error_directory');
        }*/

        if (!$json) {
            // Sanitize the folder name
            $folder = str_replace(
                array('../', '..\\', '..'),
                '',
                basename(html_entity_decode($this->request->post['folder'], ENT_QUOTES, 'UTF-8'))
            );

            // Validate the filename length
            if ((utf8_strlen($folder) < 3) || (utf8_strlen($folder) > 128)) {
                $json['error'] = $this->language->get('error_folder');
            }

            // Check if directory already exists or not
            if (is_dir($directory . '/' . $folder)) {
                $json['error'] = $this->language->get('error_exists');
            }
        }

        if (!$json) {
            /*$dir = new Directory($directory . '/' . $folder, [
                'adapter' => 'gcs', 'base' => STORECODE . '/image'
            ]);
            $dir->create();*/
            // dd($directory . '/' . $folder);
            \Filesystem::create($directory . '/' . $folder);
            // mkdir($directory . '/' . $folder, 0777);
            // chmod($directory . '/' . $folder, 0777);

            $json['success'] = $this->language->get('text_directory');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete()
    {
        $this->language->load('common/filemanager');

        $json = array();

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['path'])) {
            $paths = $this->request->post['path'];
        } else {
            $paths = array();
        }

        // Loop through each path to run validations
        foreach ($paths as $path) {
            //$path=htmlspecialchars_decode($path);
            $path = rtrim(DIR_IMAGE . str_replace(array('../', '..\\', '..'), '', $path), '/');
            // Check path exsists
            if ($path == DIR_IMAGE . 'data') {
                $json['error'] = $this->language->get('error_delete');

                break;
            }
        }

        if (!$json) {
            // Loop through each path
            foreach ($paths as $path) {
                //$path=htmlspecialchars_decode($path);
                $list = \Filesystem::list($path);
                if (!is_array($list) && $list == false) {
                    \Filesystem::setPath('image/' . $path);
                    if (\Filesystem::isExists()) {
                        \Filesystem::delete();
                    } else {
                        \Filesystem::delete();
                    }
                } else {
                    \Filesystem::delete($path);
                }
            }

            $json['success'] = $this->language->get('text_delete');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteDirectory($fileDirectoryPath) {
        // Check for files
        /*if (is_file($fileDirectoryPath)) {

            // If it is file then remove by
            // using unlink function
            return unlink($fileDirectoryPath);
        }

        // If it is a directory.
        elseif (is_dir($fileDirectoryPath)) {

            // Get the list of the files in this
            // directory
            $scan = glob(rtrim($fileDirectoryPath, '/').'/*');

            // Loop through the list of files
            foreach($scan as $index=>$path) {

                // Call recursive function
                $this->deleteDirectory($path);
            }

            // Remove the directory itself
            return @rmdir($fileDirectoryPath);
        }*/

        \Filesystem::delete($fileDirectoryPath);
    }

    public function linkImageSetting(){
        $this->language->load('common/filemanager');
        $this->load->model('setting/setting');
        $this->load->model('catalog/product');
        $this->model_setting_setting->insertUpdateSetting('config',$this->request->post['link_product_option']);
        if($this->request->post['link_product_option'] == 'link_as_main_image' || $this->request->post['link_product_option'] == 'link_as_sub_image'){
            foreach($this->request->post['images_to_link_array'] as $key=>$value){
                $image_name = $value['name'];
                $image_sku = explode('_',explode('.',$image_name)[0])[0];
                $product = $this->model_catalog_product->getProductBySku($image_sku);
                if($product){
                    if($this->request->post['link_product_option'] == 'link_as_sub_image'){
                        $res = $this->model_catalog_product->AddProductImages($product[0]['product_id'] , $value['path']);
                    }else if($this->request->post['link_product_option'] == 'link_as_main_image'){
                        $res = $this->model_catalog_product->addProductMainImages($product[0]['product_id'] , $value['path']);
                    }
                }
            }
            if($res){
                $json['success'] = $this->language->get('link_done');
            }else{
                $json['success'] = $this->language->get('link_not_done_product_not_found');
            }
        }else if($this->request->post['link_product_option'] == 'dont_link'){
            $json['success'] = $this->language->get('link_not_done');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
