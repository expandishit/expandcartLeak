<?php
class ModelExtensionSection extends Model
{
    public function getPage($template_codename, $page_codename, $page_route)
    {
        $query = $this->db->query("SELECT 
                                     ecpage.id page_id, 
                                     ectemplate.id template_id
                                   FROM 
                                     ecpage 
                                   JOIN ectemplate ON ectemplate.id = ecpage.TemplateId 
                                   WHERE 
                                     ecpage.Route = '" . $page_route . "' 
                                   AND ectemplate.CodeName = '" . $template_codename . "' 
                                   AND ecpage.CodeName = '" . $page_codename . "'");

        if ($query->row) {
            return $query->row;
        } else {
            $base_query = $this->db->query("SELECT 
                                              ecpage.id page_id, 
                                              ectemplate.id template_id
                                            FROM 
                                              ecpage 
                                            JOIN ectemplate ON ectemplate.id = ecpage.TemplateId 
                                            WHERE 
                                              ecpage.Route = '' 
                                            AND ectemplate.CodeName = '" . $template_codename . "' 
                                            AND ecpage.CodeName = '" . $page_codename . "'");
            if ($base_query->row) {
                return $base_query->row;
            } else {
                return array();
            }
        }
    }

    public function getRegionSection($page_id)
    {
        $section_type = 'live';

        $this->load->library('user');

        $this->user = new User($this->registry);

        if ($this->user->isLogged() && isset($this->request->get['isdraft']))
            if ($this->request->get['isdraft'] == "1")
                $section_type = 'draft';

        $query = $this->db->query("SELECT
                                     ecregion.id region_id,
                                     ecregion.CodeName region_codename,
                                     ecsection.id section_id,
                                     ecsection.CodeName section_codename,
                                     ecsection.State section_state
                                   FROM
                                     ecregion
                                   JOIN ecsection ON ecsection.RegionId = ecregion.id
                                   WHERE
                                     ecsection.Type = '" . $section_type . "'
                                   AND ecregion.PageId = '" . (int)$page_id . "'
                                   ORDER BY
                                     ecregion.id,
                                     ecsection.SortOrder");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    public function getSectionFields($section_id, $lang_code)
    {
        $query = $this->db->query("SELECT
                                        ecobjectfield.id field_id,
                                        ecobjectfield.CodeName field_codename,
                                        ecobjectfield.Type field_type,
                                        ecobjectfieldval.`Value` field_value
                                    FROM
                                        ecobjectfield
                                    JOIN ecobjectfieldval ON ecobjectfieldval.ObjectFieldId = ecobjectfield.id
                                    WHERE
                                        ecobjectfield.ObjectId = '" . (int)$section_id . "'
                                    AND ecobjectfield.ObjectType = 'ECSECTION'
                                    AND (
                                        ecobjectfieldval.Lang = '" . $lang_code . "'
                                        OR ecobjectfieldval.Lang = ''
                                    )");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    public function getCollections($section_id, $lang_code)
    {
        $query = $this->db->query("SELECT
                                        ecobjectfield.id field_id,
                                        ecobjectfieldval.`Value` field_value,
                                        ecobjectfield.CodeName field_codename,
                                        ecobjectfield.Type field_type,
                                        eccollection.id collection_id
                                    FROM
                                        ecobjectfield
                                    JOIN ecobjectfieldval ON ecobjectfieldval.ObjectFieldId = ecobjectfield.id
                                    JOIN eccollection ON ecobjectfield.ObjectId = eccollection.id
                                    WHERE
                                        eccollection.SectionId = '" . (int)$section_id . "'
                                    AND eccollection.IsDefault = '0'
                                    AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                    AND (
                                        ecobjectfieldval.Lang = '" . $lang_code . "'
                                        OR ecobjectfieldval.Lang = ''
                                    )
                                    ORDER BY
                                        eccollection.SortOrder");

        if ($query->rows) {
            return $query->rows;
        } else {
            return array();
        }
    }
}