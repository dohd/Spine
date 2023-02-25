<?php

use App\Helpers\uuid;
use App\Models\account\Account;
use App\Models\hrm\Hrm;
use App\Models\nhif\Nhif;
use App\Models\Settings\Setting;
use Illuminate\Support\Facades\DB;
use App\Models\transaction\Transaction;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Henerate UUID.
 *
 * @return uuid
 */
function generateUuid()
{
    return uuid::uuid4();
}
if (!function_exists('homeRoute')) {
    /**
     * Return the route to the "home" page depending on authentication/authorization status.
     *
     * @return string
     */
    function homeRoute()
    {
        if (access()->allow('view-backend')) {
            return 'admin.dashboard';
        } elseif (auth()->check()) {
            return 'frontend.user.dashboard';
        }
        return 'frontend.index';
    }
}
/*
 * Global helpers file with misc functions.
 */
if (!function_exists('app_name')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function app_name()
    {
        return config('app.name');
    }
}
if (!function_exists('access')) {
    /**
     * Access (lol) the Access:: facade as a simple function.
     */
    function access()
    {
        return app('access');
    }
}
if (!function_exists('history')) {
    /**
     * Access the history facade anywhere.
     */
    function history()
    {
        return app('history');
    }
}
if (!function_exists('custom_ini')) {
    /**
     * Access the gravatar helper.
     */
    function custom_ini()
    {
        $file = public_path('../config.ini');
        return $config = parse_ini_file($file, true);
    }
}
function generateResponse($intent)
{
    switch ($intent->status) {
        case "requires_action":
        case "requires_source_action":
            // Card requires authentication
            return [
                'requiresAction' => true,
                'paymentIntentId' => $intent->id,
                'clientSecret' => $intent->client_secret
            ];
        case "requires_payment_method":
        case "requires_source":
            // Card was not properly authenticated, suggest a new payment method
            return [
                'error' => "Your card was denied, please provide a new payment method"
            ];
        case "succeeded":
            // Payment is complete, authentication not required
            // To cancel the payment after capture you will need to issue a Refund (https://stripe.com/docs/api/refunds)
            return ['clientSecret' => $intent->client_secret];
    }
}
if (!function_exists('gravatar')) {
    /**
     * Access the gravatar helper.
     */
    function gravatar()
    {
        return app('gravatar');
    }
}
if (!function_exists('includeRouteFiles')) {
    /**
     * Loops through a folder and requires all PHP files
     * Searches sub-directories as well.
     *
     * @param $folder
     */
    function includeRouteFiles($folder)
    {
        $directory = $folder;
        $handle = opendir($directory);
        $directory_list = [$directory];
        while (false !== ($filename = readdir($handle))) {
            if ($filename != '.' && $filename != '..' && is_dir($directory . $filename)) {
                array_push($directory_list, $directory . $filename . '/');
            }
        }
        foreach ($directory_list as $directory) {
            foreach (glob($directory . '*.php') as $filename) {
                require $filename;
            }
        }
    }
}
if (!function_exists('getRtlCss')) {
    /**
     * The path being passed is generated by Laravel Mix manifest file
     * The webpack plugin takes the css filenames and appends rtl before the .css extension
     * So we take the original and place that in and send back the path.
     *
     * @param $path
     *
     * @return string
     */
    function getRtlCss($path)
    {
        $path = explode('/', $path);
        $filename = end($path);
        array_pop($path);
        $filename = rtrim($filename, '.css');
        return implode('/', $path) . '/' . $filename . '.rtl.css';
    }
}
if (!function_exists('settings')) {
    /**
     * Access the settings helper.
     */
    function settings()
    {
        // Settings Details
        $settings = Setting::latest()->first();
        if (!empty($settings)) {
            return $settings;
        }
    }
}
function company($id = false)
{
    // Settings Details
    $settings = \App\Models\Company\Company::latest()->first();
    if (!empty($settings)) {
        return $settings;
    }
}
function business(int $id = 0)
{
    $result = array();
    if ($id) {
        $business = App\Models\Company\Company::find($id);
        $result['name'] = $business->cname;
        $result['logo'] = $business->logo;
        return $result;
    }
    $business = App\Models\Company\Company::first();
    $result['name'] = $business->cname;
    $result['logo'] = $business->logo;
    return $result;
}
if (!function_exists('escapeSlashes')) {
    /**
     * Access the escapeSlashes helper.
     */
    function escapeSlashes($path)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('//', DIRECTORY_SEPARATOR, $path);
        $path = trim($path, DIRECTORY_SEPARATOR);
        return $path;
    }
}
if (!function_exists('getMenuItems')) {
    /**
     * Converts items (json string) to array and return array.
     */
    function getMenuItems($type = 'backend', $id = null)
    {
        $menu = new \App\Models\Menu\Menu();
        $menu = $menu->where('type', $type);
        if (!empty($id)) {
            $menu = $menu->where('id', $id);
        }
        $menu = $menu->first();
        if (!empty($menu) && !empty($menu->items)) {
            return json_decode($menu->items);
        }
        return [];
    }
}
if (!function_exists('getRouteUrl')) {
    /**
     * Converts querystring params to array and use it as route params and returns URL.
     */
    function getRouteUrl($url, $url_type = 'route', $separator = '?')
    {
        $routeUrl = '';
        if (!empty($url)) {
            if ($url_type == 'route') {
                if (strpos($url, $separator) !== false) {
                    $urlArray = explode($separator, $url);
                    $url = $urlArray[0];
                    parse_str($urlArray[1], $params);
                    $routeUrl = route($url, $params);
                } else {
                    $routeUrl = route($url);
                }
            } else {
                $routeUrl = $url;
            }
        }
        return $routeUrl;
    }
}
if (!function_exists('renderMenuItems')) {
    /**
     * render sidebar menu items after permission check.
     */
    function renderMenuItems($items, $viewName = 'backend.includes.partials.sidebar-item')
    {
        foreach ($items as $item) {
            // if(!empty($item->url) && !Route::has($item->url)) {
            //     return;
            // }
            if (!empty($item->view_permission_id)) {
                if (access()->allow($item->view_permission_id)) {
                    echo view($viewName, compact('item'));
                }
            } else {
                echo view($viewName, compact('item'));
            }
        }
    }
}
if (!function_exists('isActiveMenuItem')) {
    /**
     * checks if current URL is of current menu/sub-menu.
     */
    function isActiveMenuItem($item, $separator = '?')
    {
        return false;
    }
}
if (!function_exists('checkDatabaseConnection')) {
    /**
     * @return bool
     */
    function checkDatabaseConnection()
    {
        try {
            DB::connection()->reconnect();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}
if (!function_exists('currencyFormat')) {
    /**
     * @return bool
     */
    function currencyFormat($number, $symbol = true)
    {
        $number = number_format($number, '2', '.', ',');
        return $number;
    }
}
if (!function_exists('numberClean')) {
    /**
     * @return bool
     */
    function numberClean($number)
    {
        $precision_point = config('currency.precision_point');
        $decimal_sep = config('currency.decimal_sep');
        $thousand_sep = config('currency.thousand_sep');
        $number = str_replace($thousand_sep, "", $number);
        $number = str_replace($decimal_sep, ".", $number);
        $format = '%.' . $precision_point . 'f';
        $number = sprintf($format, $number);
        return $number;
    }
}
function date_for_database($input)
{
    $timestamp = strtotime($input);
    if ($timestamp) {
        $date = new DateTime($input);
        //$date->modify('+1 day');
        $date = $date->format('Y-m-d');
        return $date;
    } else return null;
}
function datetime_for_database($input, $c = true)
{
    $date = new DateTime($input);
    if ($c) $date->modify('+1 day');
    $date = $date->format('Y-m-d H:i:s');
    return $date;
}
function amountFormat($number = 0, $currency = null)
{
    if (!$currency) {
        $precision_point = config('currency.precision_point');
        $decimal_sep = config('currency.decimal_sep');
        $thousand_sep = config('currency.thousand_sep');
        $symbol_position = config('currency.symbol_position');
        $symbol = config('currency.symbol');
    } else {
        $result = \App\Models\currency\Currency::withoutGlobalScopes()->where('id', '=', $currency)->first();
        $precision_point = $result->precision_point;
        $decimal_sep = $result->decimal_sep;
        $thousand_sep = $result->thousand_sep;
        $symbol_position = $result->symbol_position;
        $symbol = $result->symbol;
        if (config('currency.id') != $result->id) {
            $number = $number / config('currency.rate');
        }
    }
    $number = number_format($number, $precision_point, $decimal_sep, $thousand_sep);
    if ($symbol_position) {
        return $symbol . ' ' . $number;
    } else {
        return $number . ' ' . $symbol;
    }
}
function numberFormat($number = 0, $currency = null, $precision_point_off = false)
{
    if (!$currency) {
        $precision_point = config('currency.precision_point');
        $decimal_sep = config('currency.decimal_sep');
        $thousand_sep = config('currency.thousand_sep');
    } else {
        $result = \App\Models\currency\Currency::withoutGlobalScopes()->where('id', '=', $currency)->first();
        $precision_point = $result->precision_point;
        $decimal_sep = $result->decimal_sep;
        $thousand_sep = $result->thousand_sep;
    }
    if ($precision_point_off) $precision_point = 0;
    $number = (float)$number;
    $number = number_format($number, $precision_point, $decimal_sep, $thousand_sep);
    return $number;
}
function dateFormat($date = '', $local = false)
{
    if ($local and strtotime($date)) return date($local, strtotime($date));
    if (strtotime($date)) return date(config('core.main_date_format'), strtotime($date));
    return date(config('core.main_date_format'));
}
// Database date format
function db_dateformat($date = '')
{
    return date('Y-m-d', strtotime($date));
}
function dateTimeFormat($date = '', $local = false)
{
    if ($local) return date($local, strtotime($date));
    if ($date) return date(config('core.main_date_format') . ' H:i:s', strtotime($date));
}
function timeFormat($date = '')
{
    if ($date) return date('H:i:s', strtotime($date));
}
function custom_fields($fields, $col1 = 2, $col2 = 10)
{
    $html = ' ';
    if (isset($fields['text'])) {
        foreach ($fields['text'] as $row) {
            $html .= '<div class="form-group">
                    <label for="field_' . $row['id'] . '" class="col-lg-' . $col1 . ' control-label">' . $row['name'] . '</label>
                    <div class="col-lg-' . $col2 . '">
                        <input class="form-control box-size" placeholder="' . $row['name'] . '" name="custom_field[' . $row['id'] . ']" type="text" id="field_' . $row['id'] . '" value="' . htmlentities($row['default_data']) . '">
                    </div>
                </div>';
        }
    }
    if (isset($fields['number'])) {
        foreach ($fields['number'] as $row) {
            $html .= '<div class="form-group">
                    <label for="field_' . $row['id'] . '" class="col-lg-' . $col1 . ' control-label">' . $row['name'] . '</label>
                    <div class="col-lg-' . $col2 . '">
                        <input class="form-control box-size" placeholder="' . $row['name'] . '" name="custom_field[' . $row['id'] . ']" type="number" id="field_' . $row['id'] . '" value="' . $row['default_data'] . '">
                    </div>
                </div>';
        }
    }
    if (isset($fields['select'])) {
        foreach ($fields['select'] as $row) {
            $html .= '<div class="form-group">
                    <label for="field_' . $row['id'] . '" class="col-lg-' . $col1 . ' control-label">' . $row['name'] . '</label>
                    <div class="col-lg-' . $col2 . '">
                        <select class="form-control" name="custom_field[' . $row['id'] . ']" id="field_' . $row['id'] . '">
                        ' . $row['default_data'] . '
                       </select>
                    </div>
                </div>';
        }
    }
    return $html;
}
function custom_fields_view($module_id = 0, $rel_id, $default = true, $public = false, $col1 = 2, $col2 = 10)
{
    $html = ' ';
    if ($module_id > 0) {
        if ($public) {
            $fields = \App\Models\items\CustomEntry::withoutGlobalScopes()->where('ins', '=', $public)->WhereHas('customfield', function ($query) {
                return $query->where('field_view', '=', 1);
            })->where('module', $module_id)->where('rid', $rel_id)->get();
        } else {
            $fields = \App\Models\items\CustomEntry::with('customfield')->where('module', $module_id)->where('rid', $rel_id)->get();
        }
        if ($default === 2) {
            $html .= '<table width="100%">';
            foreach ($fields as $row) {
                if ($row['data']) {
                    $html .= '<tr><td style="width:100pt"><strong>' . $row->customfield->name . '</strong></td><td>
 ' . $row['data'] . '</td></tr>';
                }
            }
            $html .= '</table>';
        } elseif ($default === 3) {
            $html .= '';
            foreach ($fields as $row) {
                if ($row['data']) {
                    $html .= '<br><strong>' . $row->customfield->name . '</strong> ' . $row['data'];
                }
            }
        } elseif ($default) {
            foreach ($fields as $row) {
                if ($row['data']) {
                    $html .= '<div class="row m-2 border border-purple border-lighten-5">
    <div class="col-sm-6">
      <p>' . $row->customfield->name . '</p>
    </div>
    <div class="col-sm-6">
      <p>' . $row['data'] . '</p>
    </div>
  </div>';
                }
            }
        } else {
            foreach ($fields as $row) {
                if ($row['data']) $html .= '<li><span class="text-bold-700"> ' . $row->customfield->name . '</span> : ' . $row['data'] . '<li>';
            }
        }
    }
    return $html;
}
function get_custom_fields($module = 0, $rel_id = 0)
{
    $fields = \App\Models\customfield\Customfield::where('module_id', $module)->get()->groupBy('field_type');
    $fields_raw = array();
    if (isset($fields['text'])) {
        foreach ($fields['text'] as $row) {
            $data = \App\Models\items\CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', $module)->where('rid', '=', $rel_id)->first();
            $fields_raw['text'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
        }
    }
    if (isset($fields['number'])) {
        foreach ($fields['number'] as $row) {
            $data = \App\Models\items\CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', $module)->where('rid', '=', $rel_id)->first();
            $fields_raw['number'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
        }
    }
    return $fields_raw;
}
function save_custom_field($input, $r_id = 0, $module = 1)
{
    if (isset($input['custom_field'])) {
        foreach ($input['custom_field'] as $key => $value) {
            $fields[] = array('custom_field_id' => $key, 'rid' => $r_id, 'module' => $module, 'data' => $value, 'ins' => $input['ins']);
        }
        \App\Models\items\CustomEntry::insert($fields);
    }
}
function update_custom_field($input, $r_id = 0, $module = 0)
{
    if (isset($input['custom_field'])) {
        foreach ($input['custom_field'] as $key => $value) {
            $fields[] = array('custom_field_id' => $key, 'rid' => $r_id, 'module' => $module, 'data' => $value, 'ins' => $input['ins']);
            \App\Models\items\CustomEntry::where('custom_field_id', '=', $key)->where('rid', '=', $r_id)->delete();
        }
        \App\Models\items\CustomEntry::insert($fields);
    }
}
function bill_helper($term = 1, $module_id = 1)
{
    $accounts = \App\Models\account\Account::where('account_type', 'Liabilities')->get();
    $assert_accounts = \App\Models\account\Account::where('account_type', 'Assets')->get();
    $income_accounts = \App\Models\account\Account::where('account_type', 'Income')->get();
    $expense_accounts = \App\Models\account\Account::where('account_type', 'Expenses')->get();
    // $whts = \App\Models\account\Account::where('system', 'tax')->get();
    $whts = DB::table('account_types')->where('system', 'tax')->get();
    // $receivables = \App\Models\account\Account::where('account_type', 'Assets')->where('system', 'receivables')->get();
    $receivables = DB::table('accounts')
        ->where(['accounts.ins' => auth()->user()->ins, 'account_type' => 'Assets'])
        ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id')
        ->where(['account_types.system' => 'receivables'])
        ->get();
    $warehouses = \App\Models\warehouse\Warehouse::all();
    $projects = \App\Models\project\Project::all();
    $customers = \App\Models\customer\Customer::all();
    $branches = \App\Models\branch\Branch::all();
    $additionals = \App\Models\additional\Additional::all();
    $currencies = \App\Models\currency\Currency::all();
    $selling_prices = \App\Models\pricegroup\Pricegroup::all();
    $terms = \App\Models\term\Term::where(function ($q) use ($term) {
        $q->where('type', 0)->orWhere('type', $term);
    })->get();
    $customergroups = \App\Models\customergroup\Customergroup::all();
    $fields = custom_fields(\App\Models\customfield\Customfield::where('module_id', $module_id)->get()->groupBy('field_type'));
    $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');
    return compact('warehouses', 'additionals', 'currencies', 'terms', 'customergroups', 'fields', 'defaults', 'projects', 'customers', 'branches', 'accounts', 'assert_accounts', 'income_accounts', 'expense_accounts', 'whts', 'selling_prices', 'receivables');
}
function product_helper()
{
    $warehouses = \App\Models\warehouse\Warehouse::all();
    $product_category = \App\Models\productcategory\Productcategory::all();
    $product_variable = \App\Models\productvariable\Productvariable::all();
    $fields = custom_fields(\App\Models\customfield\Customfield::where('module_id', 3)->get()->groupBy('field_type'));
    return compact('warehouses', 'product_category', 'product_variable', 'fields');
}
function prefix($value, $public = false)
{
    if ($public) return \App\Models\items\Prefix::withoutGlobalScopes()->where('class', $value)->where('ins', '=', $public)->get('value')->first()->value;
    return \App\Models\items\Prefix::where('class', $value)->get('value')->first()->value;
}
function prefixes()
{
    return \App\Models\items\Prefix::get(array('value', 'class'));
}
function token_validator($request_token, $data, $return_token = false)
{
    $valid_token = hash_hmac('ripemd160', $data, config('master.key'));
    if ($return_token) return $valid_token;
    if (hash_equals($request_token, $valid_token)) return true;
    return false;
}
function form_return($input)
{
    if (isset($input)) return $input;
    return null;
}
function delete_file($file)
{
    \Illuminate\Support\Facades\Storage::delete($file);
    @unlink(storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $file));
    return true;
}
function languages($id = 0)
{
    $lang = '<option value="' . config('core.lang') . '">--' . config('core.lang') . '--</option><option value="english">English</option> <option value="arabic">Arabic</option><option value="bengali">Bengali</option>
                       <option value="czech">Czech</option><option value="chinese-simplified">Chinese-simplified</option> <option value="chinese-traditional">Chinese-traditional</option> <option value="dutch">Dutch</option><option value="french">French</option><option value="german">German</option><option value="greek">Greek</option><option value="hindi">Hindi</option><option value="indonesian">Indonesian</option>  <option value="italian">Italian</option><option value="japanese">Japanese</option><option value="korean">Korean</option><option value="latin">Latin</option> <option value="polish">Polish</option><option value="portuguese">Portuguese</option> <option value="russian">Russian</option> <option value="swedish">Swedish</option><option value="spanish">Spanish</option><option value="turkish">Turkish</option><option value="urdu">Urdu</option>';
    return $lang;
}
function task_status($task, $c = false)
{
    if ($c) return \App\Models\misc\Misc::withoutGlobalScopes()->where('ins', '=', $c)->where('section', '=', 2)->where('id', '=', $task)->first();
    return \App\Models\misc\Misc::where('section', '=', 2)->where('id', '=', $task)->first();
}
function status_list()
{
    return \App\Models\misc\Misc::where('section', '=', 2)->get();
}
function payment_methods()
{
    $m_types = \App\Models\Company\ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 17)->first();
    $payment_methods = json_decode($m_types->value2, true);
    return $payment_methods;
}
function project_access($project_id)
{
    if ($project_id) {
        $project = \App\Models\project\Project::find($project_id);
        $user_d = auth()->user();
        if (@$project->creator->id == $user_d->id) {
            return true;
        }
        if (@$project->users->find($user_d->id)->id and ($project->project_share == 3 or $project->project_share == 5)) {
            return true;
        }
    }
    return false;
}
function project_view($project_id)
{
    if ($project_id) {
        $project = \App\Models\project\Project::find($project_id);
        $user_d = auth()->user();
        if (@$project->creator->id == $user_d->id) {
            return true;
        }
        if (@$project->users->find($user_d->id)->id and ($project->project_share > 0)) {
            return true;
        }
    }
    return false;
}
function project_client($project_id)
{
    if ($project_id) {
        $project = \App\Models\project\Project::withoutGlobalScopes()->find($project_id);
        $user_d = auth('crm')->user();
        if (@$project->customer->id == $user_d->id and $project->project_share == 4) {
            return true;
        }
    }
    return false;
}
function user_data($id)
{
    $user = Hrm::find(1)->first();
    return $user;
}
function units()
{
    $u = \App\Models\productvariable\Productvariable::all()->toJson();
    return $u;
}
function taxes()
{
    $t = \App\Models\additional\Additional::all()->toJson();
    return $t;
}
function visual()
{
    if (session()->exists('theme')) {
        return session('theme');
    }
    session(['theme' => 'ltr']);
    return 'ltr';
}
function feature(int $id = 0)
{
    $config = \App\Models\Company\ConfigMeta::where('feature_id', '=', $id)->first();
    return $config;
}
function single_ton()
{
    if (config('standard.type') and access()->allow('super') or !config('standard.type')) return true;
    return false;
}
function business_alerts($input)
{
    $mailer = new \App\Repositories\Focus\general\RosemailerRepository();
    return $mailer->send($input['text'], $input);
}
function parse($template, $data, $return = FALSE)
{

    if ($template === '') {
        return FALSE;
    }
    $replace = array();
    foreach ($data as $key => $val) {
        $replace = array_merge(
            $replace,
            is_array($val)
                ? parse_pair($key, $val, $template)
                : parse_single($key, (string)$val, $template)
        );
    }
    unset($data);
    $template = strtr($template, $replace);
    return $template;
}
function parse_single($key, $val, $string)
{
    return array('{' . $key . '}' => (string)$val);
}
/**
 * @return array
 */
function active($request)
{
    $p_file = public_path('conf.json');
    if (!file_exists($p_file)) {
        $nf = fopen($p_file, 'wb');
        fclose($nf);
        chmod($p_file, 0755);
    }
    if (is_writeable($p_file)) {
        $config = config('version');
        $build = $config['build'];
        $zone = $config['zone'];
        $client = new \GuzzleHttp\Client(["base_uri" => $zone]);
        $options = [
            'form_params' => [
                "c" => $request['code'],
                "mail" => $request['email'],
                "u" => url('/'),
            ]
        ];
        $response = $client->post('confirm/' . $build . '/register.php', $options);
        $stream = $response->getBody();
        $contents = $stream->getContents();
        $lc_en = json_decode($contents, true);
        file_put_contents($p_file, @$lc_en['code']);
        $lc = file_get_contents($p_file);
        if (empty($lc) and $lc_en['valid']) {
            return array('flash_error' => 'Server write permissions denied');
        } else {
            if ($lc_en['valid'] and $lc_en['type'] == 'e') return array('flash_success' => 'License updated!');
            if ($lc_en['valid'] and $lc_en['type'] == 'r')  return array('flash_success' => 'License updated!');
            if (!$lc_en['valid']) {
                return array('flash_error' => 'License error! Purchase another copy</a> OR <a class="yellow" href="http://bit.ly/2IsOoFa" target="_blank"> Read the license terms!</a>');
            }
        }
    } else {
        chmod($p_file, 0755);
        return array('flash_error' => 'Server write permissions denied!');
    }
}
function parse_pair($variable, $data, $string)
{
    $replace = array();
    preg_match_all(
        '#' . preg_quote('{' . $variable . '}') . '(.+?)' . preg_quote('{' . '/' . $variable . '}') . '#s',
        $string,
        $matches,
        PREG_SET_ORDER
    );
    foreach ($matches as $match) {
        $str = '';
        foreach ($data as $row) {
            $temp = array();
            foreach ($row as $key => $val) {
                if (is_array($val)) {
                    $pair = parse_pair($key, $val, $match[1]);
                    if (!empty($pair)) {
                        $temp = array_merge($temp, $pair);
                    }
                    continue;
                }
                $temp['{' . $key . '}'] = $val;
            }
            $str .= strtr($match[1], $temp);
        }
        $replace[$match[0]] = $str;
    }
    return $replace;
}
function in_array_r($needle, $haystack, $strict = false, $columns = null)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : @$item['permission_id'] == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}
function strip_tags_deep($value, $key = null)
{
    if (is_array($value)) {
        return array_map('strip_tags_deep', $value, array_keys($value));
    } else {
        if ($key === 'valuetest') {
            return strip_tags($value);
        }
        return $value;
    }
}
// log to the browser console via a view template
function browserlog(...$logs)
{
    foreach ($logs as $log) {
        echo '<script>console.log(' . json_encode($log) . ')</script>';
    }
}
// log to the server console
function printlog(...$logs)
{
    foreach ($logs as $log) {
        error_log(print_r($log, 1));
    }
}
// modify input array
function modify_array(array $input)
{
    $output = [];
    foreach ($input as $key => $list) {
        foreach ($list as $i => $v) {
            $output[$i][$key] = $v;
        }
    }
    return $output;
}
// aggregate transaction credits and debits
function aggregate_account_transactions()
{
    $tr_totals = Transaction::select(DB::raw('account_id AS id, SUM(debit) AS debit, SUM(credit) AS credit'))
        ->groupBy('account_id')
        ->get()->toArray();
    Batch::update(new Account, $tr_totals, 'id');
    // reset accounts without transactions
    $account_ids = array_map(function ($v) {
        return $v['id'];
    }, $tr_totals);
    Account::whereNotIn('id', $account_ids)->where(function ($q) {
        $q->where('debit', '>', 0)->orWhere('credit', '>', 0);
    })->update(['debit' => 0, 'credit' => 0]);
}
// auto-generate a 4 digit number prefixed with a string e.g ID-0001 
function gen4tid($prefix='', $num=0, $count=4)
{
    if ($prefix && $num) return $prefix . sprintf('%0'.$count.'d', $num);
    return sprintf('%0'.$count.'d', $num);
}
// account numbering
function accounts_numbering($account)
{
    switch ($account) {
        case 'Asset':
            return 100;
        case 'Liability':
            return 200;
        case 'Income':
            return 400;
        case 'Expense':
            return 500;
        case 'Equity':
            return 300;
    }
}
// transaction double entry (debit, credit)
function double_entry(
    $tid,
    $pr_account_id,
    $sec_account_id,
    $opening_balance,
    $entry_type,
    $trans_category_id,
    $user_type,
    $user_id,
    $tr_date,
    $duedate,
    $tr_type,
    $note,
    $ins
) {
    $data = [
        'tid' => $tid,
        'trans_category_id' => $trans_category_id,
        'tr_date' => $tr_date,
        'due_date' => $duedate,
        'user_type' => $user_type,
        'user_id' => $user_id,
        'tr_type' => $tr_type,
        'note' => $note,
        'ins' => $ins,
    ];
    $dr_data = $data + [
        'account_id' => $pr_account_id,
        'debit' => $opening_balance,
        'tr_ref' => $pr_account_id,
        'is_primary' => 1,
    ];
    $cr_data = $data + [
        'account_id' => $sec_account_id,
        'credit' => $opening_balance,
        'tr_ref' => $sec_account_id,
        'is_primary' => 0,
    ];
    if ($entry_type == 'cr') {
        unset($dr_data['debit'], $cr_data['credit']);
        $dr_data['credit'] = $opening_balance;
        $cr_data['debit'] = $opening_balance;
    }
    Transaction::create($dr_data);
    Transaction::create($cr_data);
    aggregate_account_transactions();
    return true;
}
// handle division by zero
function div_num($numerator, $denominator) {
    return $denominator == 0? 0 : ($numerator / $denominator);
}
// Get nhif rates amount 
function nhif_rates($amount)
{
    $rate = Nhif::where('salary_from', '<=', $amount)->where('salary_to', '>=', $amount)->value('monthly_contribution');
    if ($rate) return $rate;
    return 0;
}
// Get nhif rates amount 
function calculate_paye($basicpay, $nhif, $nssf, $allowance = 0)
{
  
    if ($basicpay <= 24000){
        $net_pay = $basicpay -$nssf- $nhif + $allowance;
        return array('paye'=>0,'net_pay'=>$net_pay); 
    } 
    //the tops of each paye brackets
    $band1_top = 24000.00;
    $band2_top = 8333.00;
    $band3_top = 32333.00;
    //the paye rates of each bracket
    $band1_rate = 0.1;
    $band2_rate = 0.25;
    $band3_rate = 0.30;
    //initialize brands
    $band1 = $band2 = $band3 = 0;
    $basicpay = $basicpay - $nssf;
    $blance_rate = 0;
    if ($basicpay > $band1_top) {
        $band1 = ($band1_top * $band1_rate);
        $blance_rate = $basicpay - $band1_top;
    } else {
        $blance_rate = 0;
    }
    if ($blance_rate > 0) {
        if ($blance_rate >= $band2_top) {
            $band2 = ($band2_top * $band2_rate);
        } else {
            $band2 = ($blance_rate * $band2_rate);
        }
        $blance_rate = $basicpay - $band1_top - $band2_top;
    } else {
        $blance_rate = 0;
    }
    if ($blance_rate > 0) {
        $band3 = ($blance_rate) * $band3_rate;
    }
    $income_tax = $band1 + $band2 + $band3;
    $paye_relief = 2400;
    $nhif_relief = 0.15 * $nhif;
    $total_relief = $paye_relief + $nhif_relief;
    $paye = $income_tax - $total_relief;
    if ($income_tax <= $total_relief) {
        $paye = 0;
    }
    $paye_after_tax = $basicpay - $paye;
    $net_pay = $paye_after_tax - $nhif + $allowance;
    return array('paye'=>$paye,'net_pay'=>$net_pay); 
}
// global document prefixes
function prefixesArray(array $notes, $ins = 1)
{
    $prefixes = [];
    foreach ($notes as $val) {
        $prefix = \App\Models\items\Prefix::where('note', $val)->where('ins', $ins)->first();
        if ($prefix) $prefixes[] = $prefix->value;
        else $prefixes[] = '';
    }
    return $prefixes;
}

// query string
function sqlQuery($builder) {
    if (!$builder) return '';
    $query = str_replace(array('?'), array('\'%s\''), $builder->toSql());
    $query = vsprintf($query, $builder->getBindings());
    return $query;
}
