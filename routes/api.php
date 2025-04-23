<?php


use App\Http\Controllers\Customer\CustomerController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\AuthUsers\AuthController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Controllers\AboutUsHome\AboutUsHomeController;
use App\Http\Controllers\AboutUs\AboutUsController;
use App\Http\Controllers\Testimonies\TestimoniesController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Subcategory\SubcategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Servics\ServiceController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Policy\PolicyController;
use Illuminate\Http\Request;
use App\Http\Controllers\InfoContact\InfoContactController;
use App\Http\Controllers\Pdf\PdfController;
use App\Http\Controllers\Promotion\PromotionController;
use Illuminate\Support\Facades\Route;

//Public Routes

//AuthUser
Route::post('register', [AuthController::class, 'registerUser']);
Route::post('login', [AuthController::class, 'loginUser']);
Route::post('send-code-email', [AuthController::class, 'sednEmailPasswordChange']);
Route::post('change-password', [AuthController::class, 'changePassword']);

//Aboout_Us_Home
Route::get('/about-us-home', [AboutUsHomeController::class, 'getAboutUsHome']); // Obtener AboutUs con imágen

//Testimonies
Route::get('testimonies', [TestimoniesController::class, 'getAllTestimonies']); // Obtener testimoni
Route::get('testimonies/{testimonieId}', [TestimoniesController::class, 'getTestimonie']); //Obtener testimonio por Id

//About_Us
Route::get('about-us', [AboutUsController::class, 'getAboutUs']); // Obtener AboutUs con imágenes

//Categories
Route::get('categories', [CategoryController::class, 'getAllCategories']); // Obtener categorías

//Sub_Categories
Route::get('categories/{nameCategory}/subcategories', [SubcategoryController::class, 'getAllSubcategories']); // Obtener subcategorías

//Product
Route::get('products', [ProductController::class, 'getAllProducts']); // Obtener todos los productos
Route::get('products/{nameProduct}', [ProductController::class, 'getProduct']); // Obtener un producto

//Service:
Route::get('/services', [ServiceController::class, 'getServices']);
Route::get('/services/{id}', [ServiceController::class, 'getServiceById']);

//Blog
Route::get('blogs', [BlogController::class, 'getAllBlogs']); // Obtener blogs
Route::get('blogs/{blogId}', [BlogController::class, 'getBlog']);

//Policies
Route::get('/policies', [PolicyController::class, 'getPolicy']); // Obtener policies

//Info_Contact
Route::get('/info-contact', [InfoContactController::class, 'getInfoContact']); // Obtener Info Contact

// Customer (Endpoints Públicos)
Route::get('customers', [CustomerController::class, 'getAllCustomers']); // Obtener todos los clientes
Route::get('customers/{id}', [CustomerController::class, 'getCustomer']); // Obtener un cliente por ID
Route::post('customers', [CustomerController::class, 'storeCustomer']);

//Promotion
Route::get('promotions', [PromotionController::class, 'getAllPromotions']);
Route::get('promotions/{promotionId}', [PromotionController::class, 'getPromotion']);
//Private Routes
Route::middleware(IsUserAuth::class)->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('logout', 'logout');
        Route::get('user', 'getUser');
        // Route::post('customers', [CustomerController::class, 'storeCustomer']);
    });


    Route::middleware(IsAdmin::class)->group(function () {
        Route::controller(AuthController::class)->group(function () {

            //About_Us_Home
            Route::put('/about-us-home/{idAboutUsHome}', [AboutUsHomeController::class, 'updateAboutUsHome']); // Actualizar About Us
            Route::post('/about-us-home/image', [AboutUsHomeController::class, 'updateImageToAboutUsHome']); // Agregar Imagen

            //Testimonies
            Route::post('testimonies', [TestimoniesController::class, 'storeTestimonies']); // Crear testimonio
            Route::put('testimonies/{testimonieId}', [TestimoniesController::class, 'updateTestimonies']); // Actualizar testimonio
            Route::delete('testimonies/{testimonieId}', [TestimoniesController::class, 'deleteTestimonie']); // Eliminar testimonio
            
            //About_Us
            Route::put('/about-us/{idAboutUs}', [AboutUsController::class, 'updateAboutUs']); // Actualizar About Us
            Route::post('/about-us/image', [AboutUsController::class, 'updateImageToAboutUs']); // Agregar Imagen
            Route::post('/about-us/add-value/{idValue}', [AboutUsController::class, 'addValueAboutUs']); // Agregar un valor
            Route::put('/about-us/update-value/{idValue}', [AboutUsController::class, 'updateValueAboutUs']); // Actualizar un valor
            Route::delete('/about-us/delete-value/{idValue}', [AboutUsController::class, 'deleteValueAboutUs']); // Eliminar un valor

            //Categories
            Route::post('categories', [CategoryController::class, 'storeCategory']); // Crear categoría
            Route::put('categories/{id}', [CategoryController::class, 'updateCategory']); // Actualizar categoría
            Route::delete('categories/{id}', [CategoryController::class, 'deleteCategory']); // Eliminar categoría

            //Sub-Categories
            Route::post('categories/{idCategory}/subcategories', [SubcategoryController::class, 'storeSubcategory']); // Crear subcategoría
            Route::delete('subcategories/{nameSubcategories}', [SubcategoryController::class, 'deleteSubcategory']); // Eliminar subcategoría

            //Product
            Route::post('products', [ProductController::class, 'storeProduct']); // Crear producto
            Route::put('products/{nameProduct}', [ProductController::class, 'updateProduct']); // Actualizar producto
            Route::delete('products/{nameProduct}', [ProductController::class, 'deleteProduct']); // Eliminar producto
            Route::put('products/{nameProduct}/pdf', [PdfController::class, 'pdfUpdateProduct']); //Guarda pdf en el producto
        
            //Service
            Route::post('/services', [ServiceController::class, 'createService']);
            Route::put('/services/{idServices}', [ServiceController::class, 'updateServiceById']);
            Route::delete('/services/{idServices}', [ServiceController::class, 'deleteService']);
            
            //Blog
            Route::post('blogs', [BlogController::class, 'storeBlog']); // Crear blog
            Route::put('blogs/{idBlog}', [BlogController::class, 'updateBlog']); // Actualizar blog
            Route::delete('blogs/{idBlog}', [BlogController::class, 'deleteBlog']); // Eliminar blog
        
            //Policies
            Route::put('/policies/{idPolicies}', [PolicyController::class, 'updatePolicy']);
        
            //Info_Contact
            Route::put('/info-contact/{idInfoContact}', [InfoContactController::class, 'updateInfoContact']); // Actualizar Info Contact

            // Customer 
            Route::put('customers/{id}', [CustomerController::class, 'update']);
            Route::delete('customers/{id}', [CustomerController::class, 'destroy']);

            //Promotion
            Route::post('promotions', [PromotionController::class, 'storePromotion']);
            Route::put('promotions/{promotionId}', [PromotionController::class, 'updatePromotion']);
            Route::delete('promotions/{promotionId}', [PromotionController::class, 'deletePromotion']);
        });
    });
});