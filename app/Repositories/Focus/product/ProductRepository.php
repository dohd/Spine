<?php

namespace App\Repositories\Focus\product;

use App\Models\product\ProductVariation;
use DB;
use App\Models\product\Product;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use DateTime;
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
        dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        $data['taxrate'] = numberClean($data['taxrate']);
        $result = Product::create($data);

        $product_variations = [];
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            if (empty($item['image'])) $item['image'] = 'example.png';
            $name = $item['variation_name'];
            unset($item['variation_name']);

            foreach ($item as $key => $val) {
                if ($key == 'image' && !empty($val)) $item[$key] = $this->uploadFile($val);
                if (in_array($key, ['price', 'purchase_price', 'disrate', 'qty', 'alert'])) {
                    if ($key != 'disrate' && !$val) throw ValidationException::withMessages(['Field ' . $key . ' cannot be null!']);
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

            $product_variations[] =  array_replace($item, [
                'product_id' => $result->id,
                'ins' => $result->ins,
                'name' => $name,
            ]);
        }
        ProductVariation::insert($product_variations);

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

        $data = $input['data'];
        $data['taxrate'] = numberClean($data['taxrate']);
        $result = $product->update($data);

        // create or update product variations
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            foreach ($item as $key => $val) {
                if ($key == 'image' && !empty($val)) $item[$key] = $this->uploadFile($val);
                if (in_array($key, ['price', 'purchase_price', 'disrate', 'qty', 'alert'])) {
                    if ($key != 'disrate' && !$val) throw ValidationException::withMessages(['Field ' . $key . ' cannot be null!']);
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

            if (empty($item['image'])) $item['image'] = 'example.png';
            $item = array_replace($item, [
                'product_id' => $product->id,
                'ins' => $product->ins,
                'name' => $item['variation_name'],
            ]);
            $new_item = ProductVariation::firstOrNew(['id' => $item['v_id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->v_id, $new_item->variation_name);
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

    /*
* Upload logo image
*/
    public function uploadFile($file)
    {
        $file_name = time() . $file->getClientOriginalName();

        $this->storage->put($this->file_path . $file_name, file_get_contents($file->getRealPath()));

        return $file_name;
    }

    /*
    * remove logo or favicon icon
    */
    public function removePicture(Product $product, $type)
    {
        if ($product->type && $this->storage->exists($this->file_path . $product->type)) 
            $this->storage->delete($this->file_path . $product->type);
        
        if ($product->update([$type => null])) return true;
            
        throw new GeneralException(trans('exceptions.backend.settings.update_error'));
    }
}
