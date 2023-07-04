<?php

namespace App\Repositories\Focus\product;

use App\Models\product\ProductVariation;
use DB;
use App\Models\product\Product;
use App\Exceptions\GeneralException;
use App\Models\items\PurchaseItem;
use App\Models\productcategory\Productcategory;
use App\Repositories\BaseRepository;
use DateTime;
use Error;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductRepository.
 */
class ProductRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Product::class;

    /**
     *file_path .
     * @var string
     */
    protected $file_path = 'img' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;

    /**
     * Storage Class Object.
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Constructor to initialize class objects
     */
    public function __construct()
    {
        $this->storage = Storage::disk('public');
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        
        $q->when(request('warehouse_id'), function ($q) {
            $q->whereHas('variations', function ($q) {
                $q->where('warehouse_id', request('warehouse_id'));
            })->with(['variations' => fn($q) => $q->where('warehouse_id', request('warehouse_id'))]);
        })->when(request('category_id'), function ($q) {            
            $q->whereHas('category', function ($q) {
                $q->where('productcategory_id', request('category_id'));
            });
        })->when(request('status'), function ($q) {
            if (request('status') == 'in_stock') {
                $q->whereHas('variations', function ($q) {
                    $q->where('qty', '>', 0);
                })->with(['variations' => fn($q) => $q->where('qty', '>', 0)]);
            } else {
                $q->whereHas('variations', function ($q) {
                    $q->where('qty', 0);
                })->with(['variations' => fn($q) => $q->where('qty', 0)]);
            }            
        });

        $q->with('standard');

        return $q;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        // validate stock keeping unit
        $sku_exists = Product::where('sku', $input['sku'])->count();
        if (empty($input['sku']) || $sku_exists) {
            $input['sku'] = substr($input['name'], 0, 1) . substr($input['name'], -1) . rand(1, 10000);
        }

        $input['taxrate'] = numberClean($input['taxrate']);
        $result = Product::create($input);

        // units        
        if (empty($input['compound_unit_id'])) $input['compound_unit_id'] = array();    
        $result->units()->attach(array_merge([$result->unit_id], $input['compound_unit_id']));

        // product variations
        $variations = [];
        $data_items = Arr::only($input, [
            'price', 'purchase_price', 'qty', 'code', 'barcode', 'disrate', 'alert', 'expiry', 
            'warehouse_id', 'variation_name', 'image'
        ]);
        $data_items = modify_array($data_items);
        foreach ($data_items as $item) {
            if (empty($item['image'])) $item['image'] = 'example.png';
            $item['name'] = $item['variation_name'];
            unset($item['variation_name']);

            foreach ($item as $key => $val) {
                if ($key == 'image' && $val != 'example.png') $item[$key] = $this->uploadFile($val);
                if (in_array($key, ['price', 'purchase_price', 'disrate', 'qty', 'alert'])) {
                    if ($key != 'disrate' && !$val) 
                        throw ValidationException::withMessages([$key . ' is required!']);
                    $item[$key] = numberClean($val);
                }
                if ($key == 'barcode' && !$val)
                    $item[$key] =  rand(100, 999) . rand(0, 9) . rand(1000000, 9999999) . rand(0, 9);
                if ($key == 'expiry') {
                    $expiry = new DateTime(date_for_database($val));
                    $now = new DateTime(date('Y-m-d'));
                    if ($expiry > $now) $item[$key] = date_for_database($val);
                    else $item[$key] = null;
                }
                if ($key == 'code' && !$val){
                    $productcategory = Productcategory::where('id', @$input['productcategory_id'])->first();
                    if ($productcategory) {
                        $title_arr = explode(' ', $productcategory->title);
                        if (count($title_arr) > 1) {
                            $init = current($title_arr)[0];
                            $end = end($title_arr)[0];
                            $count = ProductVariation::whereHas('product', function($q) use($input) {
                                $q->where('productcategory_id', $input['productcategory_id']);
                            })->count();
                            $item[$key] = $init . $end . sprintf("%04d", $count+1);
                        }
                    }
                }
            }
            $variations[] =  array_replace($item, ['parent_id' => $result->id, 'ins' => auth()->user()->ins]);
        }
        ProductVariation::insert($variations);   
        
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Product $product
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($product, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        // validate stock keeping unit
        $sku_exists = Product::where('sku', $input['sku'])->where('id', '!=', $product->id)->count();
        if (empty($input['sku']) || $sku_exists) {
            $input['sku'] = substr($input['name'], 0, 1) . substr($input['name'], -1) . rand(1, 10000);
        }

        $input['taxrate'] = numberClean($input['taxrate']);
        $result = $product->update($input);

        // update units        
        if (empty($input['compound_unit_id'])) $input['compound_unit_id'] = array();
        $product->units()->sync(array_merge([$product->unit_id], $input['compound_unit_id']));   

        // variations data
        $data_items = Arr::only($input, [
            'v_id', 'price', 'purchase_price', 'qty', 'code', 'barcode', 'disrate', 'alert', 'expiry', 
            'warehouse_id', 'variation_name', 'image'
        ]);
        $data_items = modify_array($data_items);

        // delete omitted product variations
        $variation_ids = array_map(function ($v) { return $v['v_id']; }, $data_items);
        $product->variations()->whereNotIn('id', $variation_ids)->delete();

        // create or update product variation
        foreach ($data_items as $item) {
            if (empty($item['image'])) $item['image'] = 'example.png';
            $item['name'] = $item['variation_name'];
            unset($item['variation_name']);

            foreach ($item as $key => $val) {
                if ($key == 'image' && $val != 'example.png') $item[$key] = $this->uploadFile($val);
                if (in_array($key, ['price', 'purchase_price', 'disrate', 'qty', 'alert'])) {
                    if ($key != 'disrate' && !$val) 
                        throw ValidationException::withMessages([$key . ' is required!']);
                    $item[$key] = numberClean($val);
                }
                if ($key == 'barcode' && !$val)
                    $item[$key] =  rand(100, 999) . rand(0, 9) . rand(1000000, 9999999) . rand(0, 9);
                if ($key == 'expiry') {
                    $expiry = new DateTime(date_for_database($val));
                    $now = new DateTime(date('Y-m-d'));
                    if ($expiry > $now) $item[$key] = date_for_database($val);
                    else $item[$key] = null;
                }
                if ($key == 'code' && !$val){
                    $productcategory = Productcategory::where('id', @$input['productcategory_id'])->first();
                    if ($productcategory) {
                        $title_arr = explode(' ', $productcategory->title);
                        if (count($title_arr) > 1) {
                            $init = current($title_arr)[0];
                            $end = end($title_arr)[0];
                            $count = ProductVariation::whereHas('product', function($q) use($input) {
                                $q->where('productcategory_id', $input['productcategory_id']);
                            })->count();
                            $item[$key] = $init . $end . sprintf("%04d", $count+1);
                        }
                    }
                }
            }

            $item = array_replace($item, [
                'parent_id' => $product->id,
            ]);
            $new_item = ProductVariation::firstOrNew(['id' => $item['v_id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->v_id);
            $new_item->save();
        }

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.products.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Product $product
     * @return bool
     * @throws GeneralException
     */
    public function delete(Product $product)
    {
        $error_msg = '';
        foreach ($product->variations as $product_variation) {
            if (isset($product_variation->quote_item->quote)) {
                $quote = $product_variation->quote_item->quote;
                if ($quote) {
                    $type = $quote->bank_id? 'PI' : 'Quote';
                    $error_msg = "Product is attached to {$type} number {$quote->tid} !";
                    break;
                }
            }
            if (isset($product_variation->purchase_item->purchase)) {
                $purchase = $product_variation->purchase_item->purchase;
                if ($purchase) $error_msg = 'Product is attached to Purchase number {$purchase->tid} !';
                break;
            }
            if (isset($product_variation->purchase_order_item->purchaseorder)) {
                $purchaseorder = $product_variation->purchase_order_item->purchaseorder;
                if ($purchaseorder) $error_msg = 'Product is attached to Purchase Order number {$purchaseorder->tid} !';
                break;
            }
            if (isset($product_variation->project_stock_item->project_stock)) {
                $project_stock = $product_variation->project_stock_item->project_stock;
                if ($project_stock) $error_msg = 'Product is attached to Issued Project Stock number {$project_stock->tid} !';
                break;
            }
            if (isset($product_variation->grn_item->goodsreceivenote)) {
                $goodsreceivenote = $product_variation->grn_item->goodsreceivenote;
                if ($goodsreceivenote) $error_msg = 'Product is attached to Goods Receive Note number {$goodsreceivenote->tid} !';
                break;
            }
        }
        if ($error_msg) throw new Error($error_msg);

        DB::beginTransaction();
        
        $product->variations()->delete();
        if ($product->delete()) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.products.delete_error'));
    }

    /**
     * Upload logo image
     * @param mixed $file
     */
    public function uploadFile($file)
    {
        $file_name = time() . $file->getClientOriginalName();

        $this->storage->put($this->file_path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }

    /**
     * Remove logo or favicon icon
     * @param Product $product
     * @param string $field
     * @return bool
     */
    public function removePicture(Product $product, $field)
    {
        $file = $this->file_path . $product->type;
        if ($product->type && $this->storage->exists($file))
            $this->storage->delete($file);

        if ($product->update([$field => null]))
            return true;

        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }

    /**
     * LIFO (Last in First Out) Inventory valuation method
     * accounting principle
     * 
     * @return float
     */
    public function eval_purchase_price(int $id, float $qty, float $rate)
    {
        if ($qty == 0) return $rate;
        
        $price_cluster = PurchaseItem::select(DB::raw('rate, COUNT(*) as count'))
            ->where(['type' => 'Stock', 'item_id' => $id])
            ->groupBy('rate')->orderBy('updated_at', 'asc')->get();

        $qty_range = range(1, $qty);
        foreach ($price_cluster as $cluster) {
            $subset = array_splice($qty_range, 0, $cluster->count);
            if (!$subset) $subset = $qty_range;
            if ($qty >= current($subset) && $qty <= end($subset)) {
                $rate = $cluster->rate;
                break;
            } 
        }

        return $rate;
    }
}
