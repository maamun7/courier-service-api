<?php namespace App\Repositories\Api\Product;

interface ProductRepository
{
    public function getProductList($per_page = 20);
    public function findProduct($id);
    public function postStoreProduct($inputs, $id = null);
}
