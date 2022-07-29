<?php
// Heading
$_['heading_title']     = 'Users';

// Text
$_['text_success']      = 'Success: You have modified user groups!';
$_['text_users'] = 'Users';
$_['text_usergroups'] = 'Role';

// Column
$_['column_name']       = 'Role Name';
$_['column_action']     = 'Action';
$_['column_email']      = 'Email';
$_['column_date']      = 'Date';

$_['description']       = 'Role Description';
$_['created_at']       = 'Creation Date';

// Entry
$_['column_username']   = 'Username';
$_['entry_name']        = 'Role name';
$_['entry_access']      = 'Access Permission';
$_['entry_modify']      = 'Modify Permission';
$_['entry_log_info']   = 'Log Information';

$_['entry_description']   = 'Description';

// Error
$_['error_permission']  = 'Warning: You do not have permission to modify user groups!';
$_['error_name']        = 'User Group Name must be between 3 and 64 characters!';
$_['error_user']        = 'Warning: This user group cannot be deleted as it is currently assigned to %s users!';
$_['error_userGroupOrderStatuses']       = '- From Status should not be equal To Status  ';
$_['error_userGroupOrderStatusesRowsEqual']       = '- There are at least two identical rows';


$_['text_permission_custom_perms'] = 'Customer permissions';
$_['text_permission_deleteOrder'] = 'Delete order';
$_['text_permission_deleteOrder_hint'] = 'Enabling this will allow group member to delete order';

$_['permission_change_order_status'] = 'Change order status';
$_['permission_change_order_status_hint'] = 'Enabling this will allow group members to change order status';
$_['permission_select_order_status'] = 'Select Order Statuses';
$_['permission_select_order_status_hint'] = 'Order statuses this group will be allowed to change';
$_['permission_entry_order_status'] = 'Order Status';
$_['permission_entry_from_order_status'] = 'From Status';
$_['permission_entry_to_order_status'] = 'To Status';
$_['button_add_new_status'] = 'Add Status';
$_['note'] = 'Note';
$_['no_staus_warnnning'] = 'If there are no specific statuses added this will allow group members to change in all statuses ! ';
$_['permission_order_edit_customer_address'] = 'Update order customer address';
$_['permission_order_edit_customer_address_hint'] = 'Enabling this will allow group members to update customer address';
$_['permission_order_edit_customer_info'] = 'Update order customer info';
$_['permission_order_edit_customer_info_hint'] = 'Enabling this will allow group members to update customer info';

$_['permission_customer_edit_addresses'] = 'Update customer addresses';
$_['permission_customer_edit_addresses_hint'] = 'Enabling this will enable group member to update customer addresses in customer update page';
$_['permission_customer_edit_info'] = 'Update customer info';
$_['permission_customer_edit_info_hint'] = 'Enabling this will enable group member to update customer info in customer update page';

$_['permission_assign_order'] = 'Assign admins to orders';
$_['permission_assign_order_hint'] = 'Enabling this will enable group member to assign admins to orders';
$_['error_user_group'] = 'This is a super admin group , and can not be deleted';
?>
