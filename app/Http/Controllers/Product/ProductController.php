<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Service\PDF\StorePDF;
use App\Http\Service\PDF\UpdatePDF;
use App\Http\Service\PDF\DeletePDF;
use App\Http\Controllers\Controller;
use App\Http\Service\Image\SaveImage;
use App\Http\Service\Image\DeleteImage;
use App\Exceptions\Product\ProductExists;
use App\Exceptions\Product\NotFoundProduct;
use App\Http\utils\Product\FindProductExists;
use App\Http\Requests\Product\ValidateProductRequest;
use App\Models\Pdf;

class ProductController extends Controller
{
    use
        StorePDF,
        UpdatePDF,
        DeletePDF,
        SaveImage,
        DeleteImage,
        FindProductExists,
        ValidateProductRequest;

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Registrar un nuevo producto",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "characteristics", "benefits", "compatibility", "price", "stock", "pdf", "subcategory_id", "image"},
     *             @OA\Property(property="name", type="string", example="Fertilizante Orgánico"),
     *             @OA\Property(property="characteristics", type="string", example="Mejora la calidad del suelo"),
     *             @OA\Property(
     *                 property="benefits",
     *                 type="array",
     *                 @OA\Items(type="string", example="Aumenta la producción")
     *             ),
     *             @OA\Property(property="compatibility", type="string", example="Compatible con cultivos de frutas"),
     *             @OA\Property(property="price", type="number", format="float", example=49.99),
     *             @OA\Property(property="stock", type="integer", example=100),
     *              @OA\Property(property="subcategory_id", type="array", @OA\Items(type="integer"), example={1,2}),
     *             @OA\Property(property="image", type="string", format="binary", description="Imagen en formato base64")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Producto registrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="El producto ya existe",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El producto ya existe en la base de datos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo name es requerido")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function storeProduct(Request $request): JsonResponse
    {
        $productExists = $this->findProductExists($request->name);
        if ($productExists) {
            throw new ProductExists;
        }
        $this->validateProducRequest($request);

        $benefits = implode('益', $request->benefits);

        DB::transaction(function () use ($request, $benefits) {
            $pdfId = $this->storePDF($request->pdf); 

            // Crear el producto
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'characteristics' => $request->characteristics,
                'benefits' => $benefits,
                'compatibility' => $request->compatibility,
                'price' => (float)$request->price,
                'stock' => $request->stock,
                'pdf_id' => $pdfId,
                'category_id' => $request->category_id,
            ]);

            // Asociar las subcategorías
            $product->subCategories()->attach($request->subcategory_id);

            // Guardar la imagen (si existe)
            if ($request->has('image')) {
                $image = $this->saveImageBase64($request->image, 'products');
                $product->image()->create([
                    'url' => $image
                ]);
            } else {
                // Crear un registro de imagen vacío para que tenga un ID
                $product->image()->create([
                    'url' => null
                ]);
            }
        });

        return new JsonResponse(['data' => 'Producto registrado']);
    }


    /**
     * @OA\Put(
     *     path="/api/products/{productId}",
     *     summary="Actualizar un producto",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         description="ID del producto a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Fertilizante Orgánico"),
     *             @OA\Property(property="description", type="string", example="Descripción actualizada"),
     *             @OA\Property(property="characteristics", type="string", example="Mejora la calidad del suelo"),
     *             @OA\Property(
     *                 property="benefits",
     *                 type="array",
     *                 @OA\Items(type="string", example="Aumenta la producción")
     *             ),
     *             @OA\Property(property="compatibility", type="string", example="Compatible con cultivos de frutas"),
     *             @OA\Property(property="price", type="number", format="float", example=49.99),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="category_id", type="integer", example=2),
     *             @OA\Property(property="subcategory_id", type="array", @OA\Items(type="integer"), example={1, 2}),
     *             @OA\Property(
     *                 property="image",
     *                 type="object",
     *                 @OA\Property(property="url", type="string", example="data:image/png;base64,...")
     *             ),
     *             @OA\Property(
     *                 property="pdf",
     *                 type="object",
     *                 @OA\Property(property="url", type="string", example="data:application/pdf;base64,...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto actualizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo name es requerido")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
       public function updateProduct(int $productId, Request $request)
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new NotFoundProduct();
        }
    
        $this->validatePartialProductRequest($request);
    
        $data = [];
    
        // Función que limpia los campos: quita espacios y verifica si realmente tiene contenido
        $clean = function ($value) {
            return isset($value) && trim($value) !== '';
        };
    
        if ($clean($request->name)) {
            $data['name'] = trim($request->name);
        }
        
        if ($clean($request->description)) {
            $data['description'] = trim($request->description);
        }
    
        if ($clean($request->characteristics)) {
            $data['characteristics'] = trim($request->characteristics);
        }
    
        if ($request->filled('benefits') && is_array($request->benefits) && count($request->benefits) > 0) {
            $data['benefits'] = implode('益', $request->benefits);
        }
    
        if ($clean($request->compatibility)) {
            $data['compatibility'] = trim($request->compatibility);
        }
    
        if ($request->filled('price')) {
            $data['price'] = (float)$request->price;
        }
    
        if ($request->filled('stock')) {
            $data['stock'] = $request->stock;
            $data['status'] = $request->stock == 0 ? false : true;
        }
    
        if ($request->filled('category_id')) {
            $data['category_id'] = $request->category_id;
        }
    
        if (!empty($data)) {
            $product->update($data);
        }
    
        if ($request->has('subcategory_id')) {
            $subcategoryIds = array_unique($request->subcategory_id);
            $product->subCategories()->sync($subcategoryIds);
        }
    
        if ($request->has('image') && isset($request->image['url'])) {
            $imageUrl = $this->saveImage($request->image['url'], 'products');
        
            if ($product->image) {
                
                $this->deleteImage($product->image->url);
                
                $product->image()->update([
                    'url' => $imageUrl,
                ]);
            } else {
                $product->image()->create([
                    'url' => $imageUrl,
                ]);
            }
        }
    
        if ($request->has('pdf')) {
            $this->updatePDF($product, $request->pdf['url'] ?? null);
        }
    
        return response()->json(['message' => 'Producto actualizado exitosamente'], 200);
    }
    /**
     * @OA\Delete(
     *     path="/api/products/{nameProduct}",
     *     summary="Eliminar un producto",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="nameProduct",
     *         in="path",
     *         required=true,
     *         description="Nombre del producto a eliminar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Producto eliminado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
        public function deleteProduct(int $id): JsonResponse
        {
            DB::transaction(function () use ($id) {
                $product = Product::find($id);
    
                if ($product) {
                    if ($product->image) {
                        $this->deleteImage($product->image->url);
                        $product->image()->delete();
                    }
    
                    $pdf = $product->pdf;
    
                    $product->delete();
    
                    if ($pdf) {
                        $this->deletePDF($pdf->url);
                        $pdf->delete();
                    }
    
                } else {
                    $pdf = Pdf::find($id);
    
                    if ($pdf) {
                        $this->deletePDF($pdf->url);
                        $pdf->delete();
                    } else {
                        throw new \Exception('Producto no encontrado');
                    }
                }
            });
    
            return new JsonResponse(['data' => 'Producto eliminado correctamente']);
        }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Obtener lista de productos",
     *     description="Devuelve una lista paginada de productos, con opción de filtrado por nombre y subcategoría. 
     *     Si el usuario está autenticado mediante un token en el encabezado Authorization, verá todos los productos. 
     *     Si no está autenticado, solo se devolverán los productos activos.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="query",
     *         required=false,
     *         description="Buscar productos por nombre (coincidencia parcial).",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="subcategory",
     *         in="query",
     *         required=false,
     *         description="Filtrar productos por subcategoría.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Cantidad máxima de productos por página (por defecto 10).",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filtrar productos por categoria.",
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Lista de productos paginada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tomate"),
     *                 @OA\Property(property="price", type="number", format="float", example=2.50),
     *                 @OA\Property(property="stock", type="integer", example=10),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="categories", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Alimentos"),
     *                     @OA\Property(property="sub_categories", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Verduras")
     *                     ))
     *                 )),
     *                 @OA\Property(property="image", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="https://example.com/image.jpg")
     *                 )
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total", type="integer", example=100),
     *             @OA\Property(property="last_page", type="integer", example=10),
     *             @OA\Property(property="next_page", type="string", nullable=true, example="http://api.example.com/products?page=2"),
     *             @OA\Property(property="prev_page", type="string", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function getAllProducts(Request $request)
    {
        $nameProduct = $request->query('product');
        $subcategory = $request->query('subcategory');
        $category = $request->query('category');
        $limit = $request->query('limit');

        $user = auth('api')->user();
        $products = Product::select('id', 'name', 'description', 'characteristics', 'benefits', 'compatibility', 'price', 'stock', 'status')
            ->with([
                'subCategories.category:id,name', 
                'image:id,imageble_id,url',
            ])
            ->when($nameProduct, function ($query) use ($nameProduct) {
                $query->where('name', 'like', "%{$nameProduct}%");
            })
            ->when($subcategory, function ($query) use ($subcategory) {
                $query->whereHas('subCategories', function ($subQuery) use ($subcategory) {
                    $subQuery->where('name', $subcategory);
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->whereHas('subCategories.category', function ($query) use ($category) {
                    $query->where('name', $category);
                });
            })
            ->when(is_null($user), function ($query) {
                $query->where('status', true);
            })
            ->paginate($limit);

        $products->getCollection()->transform(function (Product $product) {
            $category = null; 

           
            if ($product->subCategories->isNotEmpty()) {
                $category = $product->subCategories->first()->category; 
            }

            // Asocia la categoría al producto
            $product->setAttribute('category', [
                'id' => $category->id ?? null,
                'name' => $category->name ?? 'Sin categoría',
                'sub_categories' => $product->subCategories->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name
                    ];
                })
            ]);

            // Agregar beneficios procesados
            $product->setAttribute('benefits', explode('益', $product->benefits));
            $product->unsetRelation('subCategories');

            return $product;
        });

        return new JsonResponse([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'total' => $products->total(),
            'last_page' => $products->lastPage(),
            'next_page' => $products->nextPageUrl(),
            'prev_page' => $products->previousPageUrl()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{nameProduct}",
     *     summary="Obtener un producto por su nombre",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="nameProduct",
     *         in="path",
     *         required=true,
     *         description="Nombre del producto a consultar",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del producto",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tomate"),
     *                 @OA\Property(property="characteristics", type="string", example="Color rojo, tamaño mediano"),
     *                 @OA\Property(property="benefits", type="array", @OA\Items(type="string", example="Rico en vitaminas")),
     *                 @OA\Property(property="compatibility", type="string", example="Compatible con otros vegetales"),
     *                 @OA\Property(property="stock", type="integer", example=50),
     *                 @OA\Property(property="price", type="number", format="float", example=2.50),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="pdf_id", type="integer", example=5),
     *                 @OA\Property(property="subcategories", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Verduras")
     *                 )),
     *                 @OA\Property(property="pdf", type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="url", type="string", example="https://example.com/document.pdf")
     *                 ),
     *                 @OA\Property(property="image", type="object",
     *                     @OA\Property(property="id", type="integer", example=20),
     *                     @OA\Property(property="url", type="string", example="https://example.com/image.jpg")
     *                 )
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
    public function getProduct(int $productId): JsonResponse 
    {
        $product = Product::select(
                'id',
                'name',
                'description',
                'characteristics',
                'benefits',
                'compatibility',
                'stock',
                'price',
                'status',
                'category_id',
                'pdf_id'
            )
            ->with([
                'category:id,name',
                'subCategories:id,name',
                'pdf:id,url',
                'image:id,imageble_id,url'
            ])
            ->where('id', $productId) 
            ->first();
    
        if (!$product) {
            return new JsonResponse(['data' => []]);
        }
    
        $benefits = explode('益', $product->benefits);
        $product->benefits = $benefits;
    
        $formattedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'characteristics' => $product->characteristics,
            'benefits' => $product->benefits,
            'compatibility' => $product->compatibility,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'sub_categories' => $product->subCategories->map(function ($subcategory) {
                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name
                    ];
                })
            ],
            'image' => [
                'id' => $product->image->id ?? null,
                'url' => $product->image->url ?? null
            ],
            'pdf' => $product->pdf ? [
                'id' => $product->pdf->id,
                'url' => $product->pdf->url
            ] : null,
    
            'selected_subcategory_ids' => $product->subCategories->pluck('id')
        ];
    
        return new JsonResponse(['data' => [$formattedProduct]]);
    }
}
