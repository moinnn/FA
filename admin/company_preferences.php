<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security =10;
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

page(_("Company Setup"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/admin/db/company_db.inc");
//-------------------------------------------------------------------------------------------------

if (isset($_POST['update']) && $_POST['update'] != "")
{

	$input_error = 0;

	if (strlen($_POST['coy_name'])==0)
	{
		$input_error = 1;
		display_error(_("The company name must be entered."));
		set_focus('coy_name');
	}
	if (isset($_FILES['pic']) && $_FILES['pic']['name'] != '')
	{
		$user_comp = user_company();
		$result = $_FILES['pic']['error'];
		$filename = $comp_path . "/$user_comp/images";
		if (!file_exists($filename))
		{
			mkdir($filename);
		}
		$filename .= "/".$_FILES['pic']['name'];

		 //But check for the worst
		if (!in_array((substr(trim($_FILES['pic']['name']),-3)), 
			array('jpg','JPG','png','PNG')))
		{
			display_error(_('Only jpg and png files are supported - a file extension of .jpg or .png is expected'));
			$input_error = 1;
		}
		elseif ( $_FILES['pic']['size'] > ($max_image_size * 1024))
		{ //File Size Check
			display_error(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $max_image_size);
			$input_error = 1;
		}
		elseif ( $_FILES['pic']['type'] == "text/plain" )
		{  //File type Check
			display_error( _('Only graphics files can be uploaded'));
			$input_error = 1;
		}
		elseif (file_exists($filename))
		{
			$result = unlink($filename);
			if (!$result)
			{
				display_error(_('The existing image could not be removed'));
				$input_error = 1;
			}
		}

		if ($input_error != 1)
		{
			$result  =  move_uploaded_file($_FILES['pic']['tmp_name'], $filename);
			$_POST['coy_logo'] = $_FILES['pic']['name'];
			if(!$result) 
				display_error(_('Error uploading logo file'));
		}
	}
	if (check_value('del_coy_logo'))
	{
		$user_comp = user_company();
		$filename = $comp_path . "/$user_comp/images/".$_POST['coy_logo'];
		if (file_exists($filename))
		{
			$result = unlink($filename);
			if (!$result)
			{
				display_error(_('The existing image could not be removed'));
				$input_error = 1;
			}
			else
				$_POST['coy_logo'] = "";
		}
	}
	if ($input_error != 1)
	{
		update_company_setup($_POST['coy_name'], $_POST['coy_no'], $_POST['gst_no'], $_POST['tax_prd'], $_POST['tax_last'],
			$_POST['postal_address'], $_POST['phone'], $_POST['fax'], $_POST['email'], $_POST['coy_logo'], $_POST['domicile'],
			$_POST['use_dimension'], $_POST['custom1_name'], $_POST['custom2_name'], $_POST['custom3_name'],
			$_POST['custom1_value'], $_POST['custom2_value'], $_POST['custom3_value'],
			$_POST['curr_default'], $_POST['f_year'], check_value('no_item_list'), check_value('no_customer_list'),
			check_value('no_supplier_list'), $_POST['base_sales']);

		display_notification_centered(_("Company setup has been updated."));
	}
	set_focus('coy_name');
	$Ajax->activate('_page_body');
} /* end of if submit */

//---------------------------------------------------------------------------------------------


start_form(true);
if (!isset($_POST['coy_name'])) {
	$myrow = get_company_prefs();

	$_POST['coy_name'] = $myrow["coy_name"];
	$_POST['gst_no'] = $myrow["gst_no"];
	$_POST['tax_prd'] = $myrow["tax_prd"];
	$_POST['tax_last'] = $myrow["tax_last"];
	$_POST['coy_no']  = $myrow["coy_no"];
	$_POST['postal_address']  = $myrow["postal_address"];
	$_POST['phone']  = $myrow["phone"];
	$_POST['fax']  = $myrow["fax"];
	$_POST['email']  = $myrow["email"];
	$_POST['coy_logo']  = $myrow["coy_logo"];
	$_POST['domicile']  = $myrow["domicile"];
	$_POST['use_dimension']  = $myrow["use_dimension"];
	$_POST['base_sales']  = $myrow["base_sales"];
	$_POST['no_item_list']  = $myrow["no_item_list"];
	$_POST['no_customer_list']  = $myrow["no_customer_list"];
	$_POST['no_supplier_list']  = $myrow["no_supplier_list"];
	$_POST['custom1_name']  = $myrow["custom1_name"];
	$_POST['custom2_name']  = $myrow["custom2_name"];
	$_POST['custom3_name']  = $myrow["custom3_name"];
	$_POST['custom1_value']  = $myrow["custom1_value"];
	$_POST['custom2_value']  = $myrow["custom2_value"];
	$_POST['custom3_value']  = $myrow["custom3_value"];
	$_POST['curr_default']  = $myrow["curr_default"];
	$_POST['f_year']  = $myrow["f_year"];
}
$_POST['del_coy_logo']  = 0;

start_outer_table($table_style2);

table_section(1);

text_row_ex(_("Name (to appear on reports):"), 'coy_name', 42, 50);
textarea_row(_("Address:"), 'postal_address', $_POST['postal_address'], 35, 5);

text_row_ex(_("Phone Number:"), 'phone', 25, 55);
text_row_ex(_("Fax Number:"), 'fax', 25);
email_row_ex(_("Email Address:"), 'email', 25, 55);

text_row_ex(_("Official Company Number:"), 'coy_no', 25);
text_row_ex(_("GSTNo:"), 'gst_no', 25);

text_row_ex(_("Tax Periods:"), 'tax_prd', 10, 10, '', null, null, _('Months.'));
text_row_ex(_("Tax Last Period:"), 'tax_last', 10, 10, '', null, null, _('Months back.'));

currencies_list_row(_("Home Currency:"), 'curr_default', $_POST['curr_default']);

table_section(2);

fiscalyears_list_row(_("Fiscal Year:"), 'f_year', $_POST['f_year']);
label_row(_("Company Logo:"), $_POST['coy_logo']);
label_row(_("New Company Logo (.jpg)") . ":", "<input type='file' id='pic' name='pic'>");
check_row(_("Delete Company Logo:"), 'del_coy_logo', $_POST['del_coy_logo']);
text_row_ex(_("Domicile:"), 'domicile', 25, 55);

number_list_row(_("Use Dimensions:"), 'use_dimension', null, 0, 2);
sales_types_list_row(_("Base for auto price calculations:"), 'base_sales', $_POST['base_sales'], false,
    _('No base price list') );

check_row(_("Search Item List"), 'no_item_list', $_POST['no_item_list']);
check_row(_("Search Customer List"), 'no_customer_list', $_POST['no_customer_list']);
check_row(_("Search Supplier List"), 'no_supplier_list', $_POST['no_supplier_list']);

start_row();
end_row();
label_row(_("Custom Field Name"), _("Custom Field Value"));

start_row();
text_cells(null, 'custom1_name', $_POST['custom1_name'], 25, 25);
text_cells(null, 'custom1_value', $_POST['custom1_value'], 30, 30);
end_row();

start_row();
text_cells(null, 'custom2_name', $_POST['custom2_name'], 25, 25);
text_cells(null, 'custom2_value', $_POST['custom2_value'], 30, 30);
end_row();

start_row();
text_cells(null, 'custom3_name', $_POST['custom3_name'], 25, 25);
text_cells(null, 'custom3_value', $_POST['custom3_value'], 30, 30);
end_row();

end_outer_table(1);

hidden('coy_logo', $_POST['coy_logo']);
submit_center('update', _("Update"), true, '', true);

end_form(2);
//-------------------------------------------------------------------------------------------------

end_page();

?>
