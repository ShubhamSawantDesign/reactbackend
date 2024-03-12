<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'middleware'=>'api',
    'namespace'=>'App\Http\Controllers',
    'prefix'=>'auth'],
     function($router){
         //new links
         Route::get('/get_header_data', 'HomeController@get_header_data');
         Route::get('/get_footer_data', 'HomeController@get_footer_data');
         Route::get('/home_data', 'HomeController@home_data');
         Route::get('/hot_data', 'HomeController@hot_data');
         Route::get('/trending_data', 'HomeController@trending_data');
         Route::get('/get_products_data', 'HomeController@products_data');
         Route::get('/get_products_data', 'HomeController@products_data');



        Route::post('login', 'UserController@login');
        Route::post('register', 'UserController@register');
        Route::post('logout', 'UserController@logout');
        Route::get('profile', 'UserController@user');
        Route::post('/change_password', 'UserController@change_password');
        Route::post('/enquiry', 'UserController@enquiry');
        Route::post('/forgot_password', 'UserController@forgot_password');
		Route::post('/forgot_vendor_password', 'UserController@forgot_password_vendor');
		
        Route::post('/sub_emails', 'UserController@sub_emails');
        Route::get('/view_about', 'HomeController@view_about');
        Route::post('/email-chk', 'HomeController@email_chk');

        Route::get('/cms_page/{name}', 'UserController@cms_page');
        Route::get('/policies', 'HomeController@policies');

        Route::get('/get_rates','HomeController@get_currency');
        Route::get('/getstatus/{id}','HomeController@getstatus');


        Route::get('/home_view_blogs','HomeController@home_view_blogs');
        Route::get('/view_blogs','HomeController@view_blogs');
        Route::get('/view_blog/{id}','HomeController@view_blog');

        Route::get('/view_banners','HomeController@view_banners');
        Route::get('/view_collection_banners','HomeController@view_collection_banners');
        Route::get('/view_mid_section_banners','HomeController@view_mid_section_banners');
        Route::get('/trending_templates','HomeController@trending_templates');

        Route::get('/get_categories','HomeController@get_categories');
        Route::get('/get_sub_categories','HomeController@get_sub_categories');
        Route::get('/get_templates/{id}','HomeController@get_templates');
        Route::get('/filter_templates/{category}/{file_type}/{discount}/{range}','HomeController@filter_templates');
        Route::get('/get_trending_templates','HomeController@get_trending_templates');
        Route::get('/get_hot_templates','HomeController@get_hot_templates');
        Route::get('/get_template/{id}','HomeController@get_template');

        Route::post('/add_to_cart', 'UserController@addtocart');
        Route::post('/add_to_wishlist', 'UserController@addtowishlist');
        Route::post('/wishlist_to_cart/{id}', 'UserController@wishlisttocart');
        Route::get('/get_cart/{email}', 'UserController@get_cart');
        Route::get('/get_wishlist/{email}', 'UserController@get_wishlist');
        Route::get('/delete_wishlist/{id}', 'UserController@delete_wishlist');
        Route::get('/delete_cart/{id}', 'UserController@delete_cart');
        Route::post('/place_order', 'UserController@place_order');
        Route::get('/view_order/{id}', 'UserController@view_order');

        Route::get('/view_user_orders/{email}', 'UserController@view_user_orders');
        //download 2 times
        Route::post('/update_download', 'UserController@update_download');
        //account update
        Route::post('/update_account', 'UserController@update_account');
        //admin graph
        Route::get('/get_sold_templates', 'HomeController@sold_templates');
        Route::get('/total_vendor_count', 'HomeController@total_vendor_count');

        //Vendor graph
        Route::get('/getVendorTemplatesPurchase/{id}', 'HomeController@vendor_sold_templates');
        Route::get('/vendor_total_sale/{id}', 'HomeController@vendor_total_sale');


        Route::get('/view_file_types', 'HomeController@viewfiletypes');
        Route::post('/search/{keyword}/{file_type}', 'HomeController@search_templates');
});

Route::group([
    'middleware'=>'api',
    'namespace'=>'App\Http\Controllers',
    'prefix'=>'admin_auth'],
     function($router){
        Route::post('admin_login', 'AdminController@login');
        Route::post('admin_register', 'AdminController@register');
        Route::post('admin_logout', 'AdminController@logout');
        Route::get('admin_profile', 'AdminController@user');
        Route::post('/change_password', 'AdminController@change_password');
        Route::get('/view_enquiry', 'AdminController@view_enquiry');
        Route::get('/view_newsletter', 'AdminController@view_newsletter');

        Route::post('add_category', 'AdminController@add_category');
        Route::post('edit_category', 'AdminController@edit_category');
        Route::get('edit_view_category/{id}', 'AdminController@edit_view_category');
        Route::get('view_category', 'AdminController@view_category');
        Route::get('view_main_category', 'AdminController@view_main_category');
        Route::post('delete_category', 'AdminController@delete_category');

        Route::get('view_new_template', 'AdminController@view_new_template');
        Route::get('new_filter_template/{fromdate}/{todate}', 'AdminController@new_filter_template');
        Route::get('edit_view_template/{id}', 'AdminController@edit_view_template');
        Route::post('edit_template', 'AdminController@edit_template');
        Route::post('delete_template/{id}', 'AdminController@delete_template');
        Route::post('delete_template', 'AdminController@delete_template1');
        Route::get('view_template', 'AdminController@view_template');

        Route::post('add_pages', 'AdminController@add_pages');
        Route::get('edit_view_pages/{id}', 'AdminController@edit_view_pages');
        Route::post('/edit_pages', 'AdminController@edit_pages');
        Route::post('/delete_pages', 'AdminController@delete_pages');
        Route::get('/view_pages', 'AdminController@view_pages');

        //admin blogs
        Route::post('add_blog', 'AdminController@add_blog');
        Route::get('view_blogs', 'AdminController@view_blogs');
        Route::get('edit_view_blog/{id}', 'AdminController@edit_view_blog');
        Route::post('/edit_blog', 'AdminController@editBlog');
        Route::post('/delete_blog', 'AdminController@delete_blog');

        Route::get('/view_users', 'AdminController@view_users');
        Route::post('/change_user_status', 'AdminController@users_status');
        Route::post('/delete_users', 'AdminController@delete_users');

        //File Extension
        Route::post('/add_file_extension', 'AdminController@add_file_extension');
        Route::get('/view_edit_file_extension/{id}', 'AdminController@view_edit_file_extension');
        Route::post('/edit_file_extension/{id}', 'AdminController@edit_file_extension');
        Route::get('/view_file_extension', 'AdminController@view_file_extension');
        Route::post('/delete_file_extension', 'AdminController@delete_file_extension');

        //Exchange Rates
        Route::get('/view_edit_rate/{id}', 'AdminController@view_edit_rate');
        Route::post('/edit_rate/{id}', 'AdminController@edit_rate');
        Route::get('/view_rates', 'AdminController@view_rates');

        // banner route
        Route::post('/add_banner', 'AdminController@addBanner');
        Route::post('/edit_banner', 'AdminController@editBanner');
        Route::get('/view_banners', 'AdminController@viewBanners');
        Route::get('/view_collection_banners', 'AdminController@viewCollectionBanners');
        Route::get('/view_mid_section_banners', 'AdminController@viewMidSectionBanners');
        Route::get('/edit_view_banner/{id}', 'AdminController@editViewBanner');
        Route::post('delete_banner', 'AdminController@deleteBanner');

        // contributors route
        Route::get('/view_contributors', 'AdminController@viewContributors');
        Route::get('/new_contributors', 'AdminController@newContributors');
        Route::post('/edit_contributor', 'AdminController@editContributor');
        Route::get('/edit_new_contributors/{id}', 'AdminController@editNewContributors');
        Route::get('/view_template', 'AdminController@view_template');
        Route::get('/view_filter_template/{fromdate}/{todate}', 'AdminController@view_filter_template');

        //view contributor wise templates
        Route::get('/view_contributor_templates/{id}', 'AdminController@viewContributorTemplates');
        Route::get('/view_delete_request_templates', 'AdminController@view_delete_request_templates');
        Route::post('/accept_request', 'AdminController@accept_request');
        Route::post('/delete_request', 'AdminController@delete_request');
        Route::post('/delete_user_search', 'AdminController@delete_user_search');

        Route::get('/edit_contact_details/{id}', 'AdminController@editContactDetails');
        Route::post('/update_contact_details/{id}', 'AdminController@updateContactDetails');
        Route::get('/view_contact', 'AdminController@viewContactDetails');

        Route::get('view_about', 'AdminController@viewAboutDetails');
        Route::post('/edit_about', 'AdminController@editAbout');

        Route::get('/view_orders', 'AdminController@view_orders');
        Route::get('/view_contributors_orders/{id}', 'AdminController@view_contributors_orders');
        Route::get('/view_filter_contributors_orders/{id}/{fromdate}/{todate}', 'AdminController@view_filter_contributor_orders');
        Route::get('/view_filter_orders/{fromdate}/{todate}', 'AdminController@view_filter_orders');
        Route::post('/update_order_status/{id}', 'AdminController@update_order_status');
        Route::get('/view_order/{id}', 'AdminController@view_order');
        Route::get('/view_user_orders/{id}', 'AdminController@view_user_orders');

        //reports
        Route::get('/purchase_report', 'AdminController@purchase_report');
        Route::get('/purchase_filter_report/{fromdate}/{todate}', 'AdminController@purchase_filter_report');

        //user search
        Route::get('/view_user_search', 'AdminController@viewUserSearch');
});

Route::group([
    'middleware'=>'api',
    'namespace'=>'App\Http\Controllers',
    'prefix'=>'vendor_auth'],
     function($router){
        Route::post('vendor_login', 'VendorController@login');
        Route::post('/register', 'VendorController@register');
        Route::post('vendor_logout', 'VendorController@logout');
        Route::get('vendor_profile', 'VendorController@user');
        Route::post('/change_password', 'VendorController@change_password');
        Route::post('/edit_profile', 'VendorController@edit_profile');
        Route::post('/email-chk', 'HomeController@vendor_email_chk');
        Route::post('/username-chk', 'HomeController@username_chk');
        Route::get('/chk_id/{id}', 'HomeController@chk_id');

        Route::get('view_category', 'VendorController@view_category');

        Route::get('/view_orders', 'VendorController@view_orders');
        Route::get('/view_paid_orders', 'VendorController@view_paid_orders');
        Route::get('/view_order/{id}', 'VendorController@view_order');

        //reports
        Route::get('/purchase_report', 'VendorController@purchase_report');
        Route::get('/purchase_filter_report/{fromdate}/{todate}', 'VendorController@purchase_filter_report');

        //template route
        Route::post('add_template', 'VendorController@add_template');
        Route::get('view_template', 'VendorController@view_template');
        Route::get('view_approve_template', 'VendorController@view_approve_template');
        Route::get('edit_view_template/{id}', 'VendorController@edit_view_template');
        Route::post('edit_template', 'VendorController@edit_template');
        Route::post('delete_template', 'VendorController@delete_template');
        Route::post('delete_template_request', 'VendorController@delete_template_request');
        Route::post('account_details', 'VendorController@account_details');
        Route::get('view_bank_details/{id}', 'VendorController@view_bank_details');
});
