<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\BusinessSection;
use App\Models\ProductCategory;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['businessSection', 'category', 'shop', 'creator', 'updater']);

        // Filters
        if ($request->has('business_section_id')) {
            $query->where('business_section_id', $request->business_section_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->has('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_production_item')) {
            $query->where('is_production_item', $request->boolean('is_production_item'));
        }

        if ($request->has('track_inventory')) {
            $query->where('track_inventory', $request->boolean('track_inventory'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 15));

        // Get filter data for the view
        $businessSections = BusinessSection::where('is_active', true)
            ->orderBy('section_name')
            ->get(['id', 'section_code', 'section_name', 'section_type']);

        $categories = ProductCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get(['id', 'category_code', 'category_name', 'business_section_id']);

        $shops = Shop::where('is_active', true)
            ->orderBy('shop_name')
            ->get(['id', 'shop_code', 'shop_name', 'business_section_id']);

        // Get stats for the dashboard
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Product::where('track_inventory', true)
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->count(),
            'featured' => Product::where('is_featured', true)->count(),
            'production' => Product::where('is_production_item', true)->count(),
        ];

        // Return Blade view
        return view('ktvtc.cafeteria.product.index', [
            'products' => $products,
            'businessSections' => $businessSections,
            'categories' => $categories,
            'shops' => $shops,
            'stats' => $stats,
            'filters' => $request->only([
                'search', 'business_section_id', 'category_id', 'shop_id',
                'product_type', 'is_active', 'is_production_item', 'track_inventory'
            ])
        ]);
    }

    // API Index - For AJAX calls
    public function apiIndex(Request $request)
    {
        $query = Product::with(['businessSection', 'category', 'shop', 'creator', 'updater']);

        // Filters
        if ($request->has('business_section_id')) {
            $query->where('business_section_id', $request->business_section_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->has('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_production_item')) {
            $query->where('is_production_item', $request->boolean('is_production_item'));
        }

        if ($request->has('track_inventory')) {
            $query->where('track_inventory', $request->boolean('track_inventory'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    // Get product types for filter
    public function productTypes()
    {
        $types = [
            'food' => 'Food',
            'beverage' => 'Beverage',
            'gift' => 'Gift',
            'raw_material' => 'Raw Material',
            'stationery' => 'Stationery',
            'uniform' => 'Uniform',
            'other' => 'Other'
        ];

        return response()->json($types);
    }

    // Get units for product form
    public function units()
    {
        $units = [
            'piece' => 'Piece',
            'plate' => 'Plate',
            'bowl' => 'Bowl',
            'cup' => 'Cup',
            'bottle' => 'Bottle',
            'packet' => 'Packet',
            'kg' => 'Kilogram (kg)',
            'gram' => 'Gram (g)',
            'liter' => 'Liter (L)',
            'dozen' => 'Dozen'
        ];

        return response()->json($units);
    }

    // Get products by section for dropdown
    public function bySection($sectionId)
    {
        $products = Product::where('business_section_id', $sectionId)
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get(['id', 'product_code', 'product_name', 'unit']);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        // First validate all fields except image
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|unique:products|max:50',
            'product_name' => 'required|string|max:255',
            'business_section_id' => 'required|exists:business_sections,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'product_type' => 'required|in:food,beverage,gift,raw_material,stationery,uniform,other',
            'unit' => 'required|in:piece,plate,bowl,cup,bottle,packet,kg,gram,liter,dozen',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'track_inventory' => 'boolean',
            'is_production_item' => 'boolean',
            'recipe_details' => 'nullable|json',
            'shop_id' => 'nullable|exists:shops,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
            'existing_image' => 'nullable|string|max:255' // For keeping existing image when editing
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle image validation separately if a file is uploaded
        if ($request->hasFile('image')) {
            $imageValidator = Validator::make($request->all(), [
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB = 2048KB
            ]);

            if ($imageValidator->fails()) {
                return response()->json(['errors' => $imageValidator->errors()], 422);
            }
        }

        $data = $request->except(['image', 'existing_image']); // Exclude file fields initially
        $data['slug'] = Str::slug($data['product_name']);
        $data['created_by'] = auth()->id();

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('public/products', $imageName);
            $data['image'] = str_replace('public/', '', $imagePath); // Store as 'products/filename.jpg'
        } elseif ($request->has('existing_image') && !empty($request->existing_image)) {
            // Keep existing image (for when editing without changing image)
            $data['image'] = $request->existing_image;
        } else {
            // No image provided
            $data['image'] = null;
        }

        // Set default values
        $data['current_stock'] = $data['current_stock'] ?? 0;
        $data['track_inventory'] = $data['track_inventory'] ?? true;
        $data['is_active'] = $data['is_active'] ?? true;

        $product = Product::create($data);

        // If track_inventory is true, create inventory stock record
      // In ProductController@store and @update methods, remove this section:

// If track_inventory is true, create inventory stock record
if ($product->track_inventory && $product->shop_id) {
    DB::table('inventory_stocks')->insert([
        'product_id' => $product->id,
        'shop_id' => $product->shop_id,
        'current_stock' => $product->current_stock,
        'average_unit_cost' => $product->cost_price,
        'stock_value' => $product->current_stock * $product->cost_price,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

// And replace with nothing (it will be handled by the model observer)

        return response()->json($product->load(['businessSection', 'category', 'shop', 'creator']), 201);
    }

    public function show($id)
    {
        $product = Product::with([
            'businessSection',
            'category',
            'shop',
            'creator',
            'updater',
            'inventoryStocks',
            'recipe'
        ])->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // First validate all fields except image
        $validator = Validator::make($request->all(), [
            'product_code' => 'sometimes|string|unique:products,product_code,' . $id . '|max:50',
            'product_name' => 'sometimes|string|max:255',
            'business_section_id' => 'sometimes|exists:business_sections,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'product_type' => 'sometimes|in:food,beverage,gift,raw_material,stationery,uniform,other',
            'unit' => 'sometimes|in:piece,plate,bowl,cup,bottle,packet,kg,gram,liter,dozen',
            'selling_price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'track_inventory' => 'boolean',
            'is_production_item' => 'boolean',
            'recipe_details' => 'nullable|json',
            'shop_id' => 'nullable|exists:shops,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
            'existing_image' => 'nullable|string|max:255' // For keeping existing image
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle image validation separately if a file is uploaded
        if ($request->hasFile('image')) {
            $imageValidator = Validator::make($request->all(), [
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB = 2048KB
            ]);

            if ($imageValidator->fails()) {
                return response()->json(['errors' => $imageValidator->errors()], 422);
            }
        }

        $data = $request->except(['image', 'existing_image']); // Exclude file fields initially

        if ($request->has('product_name')) {
            $data['slug'] = Str::slug($data['product_name']);
        }

        $data['updated_by'] = auth()->id();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('public/products', $imageName);
            $data['image'] = str_replace('public/', '', $imagePath);
        } elseif ($request->has('existing_image') && !empty($request->existing_image)) {
            // Keep existing image
            $data['image'] = $request->existing_image;
        } elseif ($request->has('remove_image') && $request->remove_image == 'true') {
            // Remove image (when user clears it)
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }
            $data['image'] = null;
        } else {
            // Keep current image if no changes
            $data['image'] = $product->image;
        }

        $product->update($data);

        // Update inventory stock if stock changed
        if ($product->track_inventory && $product->shop_id && $request->has('current_stock')) {
            DB::table('inventory_stocks')
                ->where('product_id', $product->id)
                ->where('shop_id', $product->shop_id)
                ->update([
                    'current_stock' => $product->current_stock,
                    'average_unit_cost' => $product->cost_price,
                    'stock_value' => $product->current_stock * $product->cost_price,
                    'updated_at' => now()
                ]);
        }

        return response()->json($product->fresh()->load(['businessSection', 'category', 'shop', 'creator', 'updater']));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete image if exists
        if ($product->image && Storage::exists('public/' . $product->image)) {
            Storage::delete('public/' . $product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return response()->json(['message' => 'Product restored successfully']);
    }

    public function updateStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
            'quantity' => 'required|numeric',
            'movement_type' => 'required|in:adjustment_in,adjustment_out',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get current stock
        $stock = DB::table('inventory_stocks')
            ->where('product_id', $product->id)
            ->where('shop_id', $request->shop_id)
            ->first();

        $previousStock = $stock ? $stock->current_stock : 0;
        $newStock = $previousStock + $request->quantity;

        // Create inventory movement
        $movementNumber = 'MOV-' . date('Ymd') . '-' . strtoupper(uniqid());

        $movement = DB::table('inventory_movements')->insertGetId([
            'movement_number' => $movementNumber,
            'product_id' => $product->id,
            'shop_id' => $request->shop_id,
            'movement_type' => $request->movement_type,
            'quantity' => abs($request->quantity),
            'unit' => $product->unit,
            'unit_cost' => $product->cost_price,
            'total_cost' => abs($request->quantity) * $product->cost_price,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reason' => $request->reason,
            'adjustment_category' => 'stock_take',
            'recorded_by' => auth()->id(),
            'movement_date' => now(),
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update inventory stock
        if ($stock) {
            DB::table('inventory_stocks')
                ->where('product_id', $product->id)
                ->where('shop_id', $request->shop_id)
                ->update([
                    'current_stock' => $newStock,
                    'available_stock' => $newStock - ($stock->reserved_stock ?? 0),
                    'stock_value' => $newStock * $product->cost_price,
                    'last_movement_at' => now(),
                    'last_movement_id' => $movement,
                    'last_adjusted_date' => now(),
                    'updated_at' => now()
                ]);
        } else {
            DB::table('inventory_stocks')->insert([
                'product_id' => $product->id,
                'shop_id' => $request->shop_id,
                'current_stock' => $newStock,
                'available_stock' => $newStock,
                'average_unit_cost' => $product->cost_price,
                'stock_value' => $newStock * $product->cost_price,
                'last_movement_at' => now(),
                'last_movement_id' => $movement,
                'last_adjusted_date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Update product current stock if this is the primary shop
        if ($product->shop_id == $request->shop_id) {
            $product->current_stock = $newStock;
            $product->save();
        }

        return response()->json([
            'message' => 'Stock updated successfully',
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'movement_number' => $movementNumber
        ]);
    }
}
