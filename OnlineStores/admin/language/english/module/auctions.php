<?php


//Heading titles
$_["heading_title"]              = "Auctions";
$_["heading_title_auctions"]     = "Auctions";
$_["heading_title_create"]       = "Create New Auction";
$_["heading_title_edit"]         = "Update An Auction";
$_["heading_title_orders"]        = "Auctions Orders";
$_["heading_title_deposit"]      = "Auctions Deposits";

$_["text_module"]                = "Apps";
$_["text_success"]               = "Saved successfully";

$_["entry_move_to_next_winner"]  = "Move to next winner";
$_["entry_move_to_next_winner_help"] = "If winning bidder didn't purchase the auction product, make the next highest bid the winning bidder";

//Form inputs & entries
$_["entry_product"]              = "Product Name";
$_["entry_starting_bid_price"]   = "Starting Bid Price";
$_["text_starting_bid_price_help"]= "Must be greater than zero";
$_["entry_start_datetime"]       = "Start Date/Time";
$_["entry_close_datetime"]       = "Close Date/Time";
$_["entry_min_deposit"]          = "Minimum Deposit Amount";
$_["entry_increment"]            = "Bidding Increment";
$_["text_increment_help"]        = "The amount to be added to the current bid amount to calculate the next minimum bid allowed. Default value is 0.25 %s";
$_["entry_min_quantity"]         = "Minimum Quantity";
$_["entry_quantity"]             = "Quantity";
$_["entry_min_quantity_help"]    = "Minimum Quantity of auction product the winner can buy with the winning bid price";
$_["entry_max_quantity"]         = "Maximum Quantity";
$_["entry_max_quantity_help"]    = "Maximum Quantity of auction product the winner can buy with the winning bid price";
$_["entry_purchase_valid_days"]  = "Purchase Valid Days";

$_["entry_auctions_timezone"]    = "Auctions Timezone";
$_["text_purchase_valid_days_help"]  = "Number of days are valid for winning bidder to purchase the auction product with the winning price. Default is 3 Days and maximum allowed days are 99 day";
$_["text_days"]                  = "Days";
$_["text_items"]                 = "Items";

//Auctions list table columns..
$_["column_product_name"]        = "Product Name";
$_["column_starting_price"]      = "Starting Price";
$_["column_increment"]           = "Increment";
$_["column_start_datetime"]      = "Start Datetime";
$_["column_close_datetime"]      = "Close Datetime";
$_["column_auction_status"]      = "Auction Status";
$_["column_biding_status"]       = "Biding Status";
$_["column_min_deposit"]         = "Minimum Deposit";
$_["column_paid_at"]             = "Paid At";
$_["column_auction_id"]          = "Auction ID";

//Bids list tab table
$_["column_bidder_name"]         = "Bidder Name";
$_["column_amount"]              = "Amount";
$_["column_is_winner"]           = "Is Winning";
$_["lbl_auction_bids"]           = "Auction Bids List";
//tabs
$_["tab_settings"]    =  "Settings";
$_["tab_orders"]      =  "Orders";
$_["tab_auctions"]    =  "Auctions";
$_["tab_auction_info"]=  "Auction Information";
$_["tab_auction_bids"]=  "Bids List";
$_["tab_deposits"]    =  "Deposits Log";

//Buttons
$_["btn_new_auction"]    =  "Add New Auction";




//Anytime picker labels
$_["text_select_datetime"] =  "Select a Date and Time";
$_["text_hour"]            =  "Hour";
$_["text_minute"]          =  "Minute";
$_["text_second"]          =  "Second";
$_["text_day_of_month"]    =  "Day of Month";
$_["text_month"]           =  "Month";
$_["text_year"]            =  "Year";
$_["text_order_order"]     =  "Order";
$_['text_auction']         =  "Auction";

//error messages
$_["error_purchase_valid_days"] =  "Purchase Valid Days is required and must be greater than zero";
$_["error_max_quantity"]        =  "Maximum Quantity must be non-zero, positive, integer number and not less than Minimum Quantity value";
$_["error_min_quantity"]        =  "Minimum Quantity must be non-zero, positive and interger number";
$_["error_close_datetime"]      =  "Close Date/Time is required and must be greater than Start Date/Time ";
$_["error_start_datetime"]      =  "Start Date/Time is required and must be equal to or greater than the current date & time";
$_["error_increment"]           =  "Increment must be non-zero and positive number";
$_["error_min_deposit"]         =  "Minimum Deposit is required and must be a positive number";
$_["error_starting_bid_price"]  =  "Starting Bid Price is required and must be non-zero and positive number";
$_["error_product"]             =  "A product name must be selected";
$_["error_warning"]             =  "Errors:";
$_["error_delete"]              =  "Cannot delete aunction/s";
$_["error_delete_bid"]          =  "Cannot delete this bid";
$_["error"] = "Error";
