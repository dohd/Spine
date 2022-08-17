<?php

namespace App\Repositories\Focus\product;

use App\Models\product\ProductVariation;
use DB;
use App\Models\product\Product;
use App\Exceptions\GeneralException;
use App\Models\items\PurchaseItem;
use App\Repositories\BaseRepository;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductRepository.
 */
class ProductRepository extends BaseRepository
{
    /**
     *file_path .
     *
     * @var string
     */
    protected $file_path;
    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;
    /**
     * Associated Repository Model.
     */
    const MODEL = Product::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->file_path = 'img' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
    }

    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('p_rel_id') and !request('p_rel_type'), function ($q) {
            $q->where('sub_cat_id', '=', 0);
            return $q->where('productcategory_id', '=', request('p_rel_id', 0));
        });

        $q->when(request('p_rel_id') and (request('p_rel_type') == 1), function ($q) {
            return $q->where('sub_cat_id', '=', request('p_rel_id', 0));
        });

        return $q->get(['id', 'productcategory_id', 'name', 'sub_cat_id', 'created_at']);
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
        // sanitize
        $input['taxrate'] = numberClean($input['taxrate']);
        
        // create product
        $result = Product::create($input);

        // units            
        $compound_unit_ids = isset($input['compound_unit_id'])? explode(',', $input['compound_unit_id']) : array();
        $result->units()->attach(array_merge([$result->unit_id], $compound_unit_ids));

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
            }

            $variations[] =  array_replace($item, [
                'parent_id' => $result->id,
                'ins' => auth()->user()->ins
            ]);
        }
        ProductVariation::insert($variations);   
        
        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.products.create_error'));
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

        $input['taxrate'] = numberClean($input['taxrate']);
        $result = $product->update($input);

        // update units            
        $compound_unit_ids = isset($input['compound_unit_id'])? explode(',', $input['compound_unit_id']) : array();
        $product->units()->sync(array_merge([$product->unit_id], $compound_unit_ids));   

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
        if ($product->delete()) return true;

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
        if ($product->type && $this->storage->exists($this->file_path . $product->type))
            $this->storage->delete($this->file_path . $product->type);

        if ($product->update([$field => null])) return true;

        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }

    /**
     * LIFO (Last in First Out) Inventory valuation method
     * accounting principle
     * 
     * @return float
     */
    public function compute_purchase_price(float $id, float $qty, float $rate)
    {
        if ($qty == 0) return $rate;
        
        $rate_groups = PurchaseItem::select(DB::raw('rate, COUNT(*) as count'))
            ->where('item_id', $id)
            ->orderBy('created_at', 'ASC')
            ->groupBy('rate')
            ->get();

        $set = range(1, $qty);
        foreach ($rate_groups as $group) {
            $subset = array_splice($set, 0, $group->count);
            $last_indx = count($subset) - 1;
            if ($subset && $qty >= $subset[0] && $qty <= $subset[$last_indx]) {
                $rate = $group->rate;
                break;
            }
        }

        return $rate;
    }
}
