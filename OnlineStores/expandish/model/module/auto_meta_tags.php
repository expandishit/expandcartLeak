<?php
/**
*   Model class for Auto Meta Tags Application
*   
*   @author Michael
*/
class ModelModuleAutoMetaTags extends Model 
{
    /**
    *   Get all application setting
    *   @param int $store_id the store id.
    *   @param string $group the group name.
    *   @return array $rows the settings in an array format
    */
    public function get_all_settings($store_id = 0, $group = 'auto_meta_tags')
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "setting WHERE `store_id` = '" . $this->db->escape($store_id) . "' AND `group` = '" . $this->db->escape($group) . "'";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
    *   Get a certain application setting using the key
    *
    *   @param string $key the key used to identify the setting to select
    *   @param int $store_id the store id.
    *   @param string $group the group.
    *   @return string $row the result value.
    */
    public function get_setting_by_key($key, $store_id = 0, $group = 'auto_meta_tags')
    {
         $sql = "SELECT * FROM " . DB_PREFIX . "setting WHERE `store_id` = '" . $this->db->escape($store_id) . "' AND `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "'";

        $query = $this->db->query($sql);

        return $query->row['value'];
    }

    /**
    *   Generate the custom meta description for the product.
    *
    *   @param array $product_info the product info.
    *   @return string $description the final description.
    */
    public function generate_description($product_info)
    {
        $number_of_words = $this->get_setting_by_key('auto_meta_tags_description_words_number');
        if( isset($product_info['meta_description']) && !empty($product_info['meta_description']))
        {
            $product_info['description'] = $product_info['meta_description']." ".$product_info['description'];
        }
        $stripped_description = $this->clean_html_and_characters($product_info['description']);

        return $this->get_certain_words_from_string($stripped_description, $number_of_words);
    }

    /**
    *   Generate the custom meta keywords.
    *
    *   @param array $product_info the product info.
    *   @return string $meta the final meta keywords.
    */
    public function generate_meta_keywords($product_info)
    {
        $final_meta_keywords = array();

        if (isset($product_info['tag']) && !empty($product_info['tag'])) {
            $final_meta_keywords[] = $product_info['tag'];
        }

        if (isset($product_info['model']) && !empty($product_info['model']) ) {
                $final_meta_keywords[] = $product_info['model'];
        }

        if ( isset($product_info['manufacturer']) && !empty($product_info['manufacturer']) ) {
            if ( !in_array($product_info['manufacturer'], $final_meta_keywords) ) {
                $final_meta_keywords[] = $product_info['manufacturer'];
            }
        }

        if ( isset($product_info['product_id']) && !empty($product_info['product_id']) ) {
            $product_categories = $this->prepare_for_joining($this->get_product_categories($product_info['product_id']));

            foreach ($product_categories as $cat) {
                $final_meta_keywords[] = $cat;
            }
        }

        if ( isset($product_info['name']) && !empty($product_info['name']) ) {
            $product_name_array = $this->prepare_for_joining($product_info['name']);

            foreach ($product_name_array as $name) {
                $final_meta_keywords[] = $name;
            }
        
        }
        $final_meta_keywords = array_filter(array_unique($final_meta_keywords));

        $final_meta_keywords = implode(',', $final_meta_keywords);

        return $final_meta_keywords;
    }

    /**
    *   A simple function to return a certain number of words from a string.
    *
    *   @param string $string the full string.
    *   @param int $num_of_words the number of words we want from the full string
    *   @return string $new_string the new string which contains $num_of_words of words.
    *   @author https://stackoverflow.com/questions/1112946/how-do-i-get-only-a-determined-number-of-words-from-a-string-in-php
    */
    private function get_certain_words_from_string($string, $num_of_words)
    {
        $string = preg_replace('/\s+/', ' ', trim($string));
        $words = explode(" ", $string); // an array

        // if number of words you want to get is greater than number of words in the string
        if ($num_of_words > count($words)) {
            // then use number of words in the string
            $num_of_words = count($words);
        }

        $new_string = "";
        for ($i = 0; $i < $num_of_words; $i++) {
            $new_string .= $words[$i] . " ";
        }

        return trim($new_string);
    }

    /**
    *   A function that cleans all html tags and entities.
    *
    *   @param string $unclean the unclean string
    *   @param OPTIONAL string $delimiter the delimiter to replace alpha-numeric characters with. defaults to a space.
    *   @param OPTION bool $clean_numbers remove numbers from the string to only return alphas.
    *   @return string $final_clean the clean code after cleaning.
    *   @author https://stackoverflow.com/questions/7128856/strip-out-html-and-special-characters
    */
    private function clean_html_and_characters($unclean, $delimiter = ' ', $clean_numbers = false)
    {
        // Strip HTML Tags
        $clean = strip_tags($unclean);
        // Clean up things like &amp;
        $clean = html_entity_decode($clean);
        // Strip out any url-encoded stuff
        $clean = urldecode($clean);
        // Replace non-AlNum characters with space
        $clean = preg_replace('/[^\p{L}\p{N}]/ui', $delimiter, $clean);
        // Replace Multiple spaces with single space
        $clean = preg_replace('/ +/', ' ', $clean);
        // clean numbers
        if ($clean_numbers) {
            $clean = preg_replace('/[^p{L}]/ui', $delimiter, $clean);
        }

        // Trim the string of leading/trailing space
        $clean = trim($clean);

        $final_clean = '';

        foreach (explode(' ', $clean) as $word) {
            if ( !empty($word) && strlen($word) >= 3 ) {
                $final_clean .= $word . ' ';
            }
        }
        return trim($final_clean);
    }

    /**
    *   A function to grab product parent category and sub categories using product id
    *
    *   @param int $product_id the id of the product
    *   @return array $final_categories an array of all categories and sub categories that this product belongs to.
    */
    private function get_product_categories(int $product_id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "product_to_category WHERE `product_id` = ' " . $this->db->escape($product_id) . "'";
        $query = $this->db->query($sql);

        if ( !empty($query->rows) ) {
            
            $final_categories = array();
            $language_id = $this->config->get('language_id');

            foreach ($query->rows as $row) {
                $temp_category = '';
                $sql = "SELECT * FROM " . DB_PREFIX . "category_description WHERE `category_id` = '".$this->db->escape($row['category_id'])."' AND `language_id` = '".$this->db->escape($language_id)."' LIMIT 1";
                
                $query = $this->db->query($sql);

                if ( !empty($query->row) ) {
                    $temp_category = $query->row['name'];
                } else {
                    $sql = "SELECT * FROM " . DB_PREFIX . "category_description WHERE `category_id` = '".$this->db->escape($row['category_id'])."' LIMIT 1";

                    $query = $this->db->query($sql);

                    if ( !empty($query->row) ) {
                        $temp_category = $query->row['name'];
                    }
                }

                $final_categories[] = $this->clean_html_and_characters($temp_category);
            }

            return $final_categories;
        } else {
            return false;
        }
    }

    /**
    *   A function to prepare a string/array to be sent function.
    *   The function removes non alphanum characters AND removes words < 3 characters.
    *
    *   @param array/string $value to be prepared
    *   @param string $delimiter explained above.
    *   @param bool $clean_numbers explained above.
    *   @return array $prepared an array of prepared values for joining.
    */
    private function prepare_for_joining($values, $delimiter = ' ', $clean_numbers = false)
    {
        if ( !is_array($values) )
        {
            $values = explode(' ', $values);
        }

        $prepared = array();

        foreach ($values as $value) {
            $value = $this->clean_html_and_characters($value, $delimiter, $clean_numbers);
            $prepared[] = $value;
        }

        return $prepared;
    }
}
