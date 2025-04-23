<?php

namespace App\Http\Controllers\Blog;

use App\Exceptions\Blog\NotFoundBlog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\ValidateBlogRequest;
use App\Http\Service\Image\DeleteImage;
use App\Http\Service\Image\SaveImage;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use 
    SaveImage, 
    DeleteImage,
    ValidateBlogRequest;
    
    /**
 * @OA\Post(
 *     path="/api/blogs",
 *     summary="Registrar un nuevo blog",
 *     tags={"Blogs"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "description", "category_id", "image"},
 *             @OA\Property(property="title", type="string", example="Título del blog"),
 *             @OA\Property(property="description", type="string", example="Descripción del blog"),
 *             @OA\Property(property="category_id", type="integer", example=1),
 *             @OA\Property(property="image", type="string", format="base64", example="data:image/png;base64,iVBORw0KGgoAAAANS...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Blog registrado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Blog registrado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="El título es requerido")),
 *                 @OA\Property(property="description", type="array", @OA\Items(type="string", example="La descripción es requerida"))
 *             )
 *         )
 *     )
 * )
 */
    public function storeBlog(Request $request): JsonResponse
    {

        $this->validateBlogRequest($request);

        $blog = Blog::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'date' => now()
        ]);

        $image = $this->saveImage($request->image, 'blogs');
        $blog->image()->create([
            'url' => $image
        ]);
        return new JsonResponse(['data' => 'Blog registrado']);
    }

    /**
 * @OA\Put(
 *     path="/api/blogs/{idBlog}",
 *     summary="Actualizar un blog existente",
 *     tags={"Blogs"},
 *     @OA\Parameter(
 *         name="idBlog",
 *         in="path",
 *         required=true,
 *         description="ID del blog a actualizar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "description", "category_id", "image"},
 *             @OA\Property(property="title", type="string", example="Nuevo título del blog"),
 *             @OA\Property(property="description", type="string", example="Nueva descripción del blog"),
 *             @OA\Property(property="category_id", type="integer", example=2),
 *             @OA\Property(property="image", type="string", format="base64", example="data:image/png;base64,iVBORw0KGgoAAAANS...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Blog actualizado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Blog actualizado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Blog no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No se encontró el blog solicitado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Error de validación"),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="title", type="array", @OA\Items(type="string", example="El título es requerido")),
 *                 @OA\Property(property="description", type="array", @OA\Items(type="string", example="La descripción es requerida"))
 *             )
 *         )
 *     )
 * )
 */
    public function updateBlog(Request $request, int $idBlog): JsonResponse
    {
        $this->validateBlogRequest($request);
        $blog = Blog::find($idBlog);
        if (!$blog) {
            throw new NotFoundBlog;
        }
        $image = $this->saveImage($request->image, 'blogs');
        $this->deleteImage($blog->image->url);
        $blog->update([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id
        ]);
        $blog->image()->update([
            'url' => $image
        ]);
        return new JsonResponse(['data' => 'Blog actulizado']);
    }

    /**
 * @OA\Delete(
 *     path="/api/blogs/{idBlog}",
 *     summary="Eliminar un blog",
 *     tags={"Blogs"},
 *     @OA\Parameter(
 *         name="idBlog",
 *         in="path",
 *         required=true,
 *         description="ID del blog a eliminar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Blog eliminado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="string", example="Blog eliminado")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Blog no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No se encontró el blog solicitado")
 *         )
 *     )
 * )
 */
    public function deleteBlog(int $idBlog): JsonResponse
    {
        $blog = Blog::find($idBlog);
        if(!$blog){
            throw new NotFoundBlog;
        }
        $this->deleteImage($blog->image->url);
        $blog->delete();
        return new JsonResponse(['data' => 'Blog eliminado']);
    }

    /**
 * @OA\Get(
 *     path="/api/blogs",
 *     summary="Obtener todos los blogs",
 *     tags={"Blogs"},
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         description="Cantidad de blogs por página",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Parameter(
 *         name="category",
 *         in="query",
 *         required=false,
 *         description="Filtrar blogs por nombre de categoría",
 *         @OA\Schema(type="string", example="Tecnología")
 *     ),
 *     @OA\Parameter(
 *         name="title",
 *         in="query",
 *         required=false,
 *         description="Filtrar blogs por nombre deL titulo",
 *         @OA\Schema(type="string", example="Nuevas Tecnologías Llegaron")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de blogs paginados",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="Blog sobre tecnología"),
 *                     @OA\Property(property="description", type="string", example="Descripción del blog"),
 *                     @OA\Property(property="category", type="object",
 *                         @OA\Property(property="id", type="integer", example=2),
 *                         @OA\Property(property="name", type="string", example="Tecnología")
 *                     ),
 *                     @OA\Property(property="image", type="object",
 *                         @OA\Property(property="url", type="string", example="https://example.com/image.jpg"),
 *                         @OA\Property(property="imageble_id", type="integer", example=1)
 *                     )
 *                 )
 *             ),
 *             @OA\Property(property="current_page", type="integer", example=1),
 *             @OA\Property(property="total", type="integer", example=50),
 *             @OA\Property(property="last_page", type="integer", example=5),
 *             @OA\Property(property="next_page", type="string", nullable=true, example="https://api.example.com/api/blogs?page=2"),
 *             @OA\Property(property="prev_page", type="string", nullable=true, example=null)
 *         )
 *     )
 * )
 */
    public function getAllBlogs(Request $request): JsonResponse
    {
        $limit = $request->query('limit');
        $category = $request->query('category');
        $title = $request->query('title');
        $blogs = Blog::select('id', 'title', 'description', 'date', 'category_id')
        ->with([
            'category:id,name',
            'image:url,imageble_id'
        ])
        ->when($category, function ($query) use($category){
            $query->whereHas('category', function($subquery) use($category){
                $subquery->where('name', $category);
            });
        })
        ->when($title, function ($query) use ($title){
            $query->where('title', 'like', "%{$title}%");
        })
        ->paginate($limit);
        return new JsonResponse([
            'data' => $blogs->items(),
            'current_page' => $blogs->currentPage(),
            'total' => $blogs->total(),
            'last_page' => $blogs->lastPage(),
            'next_page' => $blogs->nextPageUrl(),
            'prev_page' => $blogs->previousPageUrl()
        ]);;
    }

    /**
 * @OA\Get(
 *     path="/api/blogs/{blogId}",
 *     summary="Obtener un blog por ID",
 *     description="Retorna los detalles de un blog específico, incluyendo su categoría.",
 *     tags={"Blogs"},
 *     @OA\Parameter(
 *         name="blogId",
 *         in="path",
 *         required=true,
 *         description="ID del blog a obtener",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Blog encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="title", type="string", example="Título del blog"),
 *                 @OA\Property(property="description", type="string", example="Descripción del blog"),
 *                 @OA\Property(property="category_id", type="integer", example=2),
 *                 @OA\Property(property="category", type="object",
 *                     @OA\Property(property="id", type="integer", example=2),
 *                     @OA\Property(property="name", type="string", example="Tecnología")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Blog no encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Blog no encontrado")
 *         )
 *     )
 * )
 */
    public function getBlog(int $blogId): JsonResponse
    {
        $blog = Blog::select(
            'id',
            'title',
            'description',
            'date',
            'category_id')
        ->with([
            'category:id,name',
            'image:id,imageble_id,url'
            ])
        ->find($blogId);
        if(!$blog){
            throw new NotFoundBlog;
        }
        return new JsonResponse(['data' => $blog]);
    }
}
