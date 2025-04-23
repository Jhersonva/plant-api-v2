<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\Customer\NotFoundCustomer;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/customers",
     *     summary="Obtener lista de clientes",
     *     description="Retorna una lista paginada de clientes, con opción de filtrar por email y estado activo.",
     *     operationId="getAllCustomers",
     *     tags={"Customers"},
     * 
     *     @OA\Parameter(
     *         name="customer",
     *         in="query",
     *         description="Buscar por email del cliente",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtrar por estado (1 = activo, 0 = inactivo)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Cantidad de registros por página (paginación)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de clientes",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="active", type="boolean")
     *             )),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="next_page", type="string", nullable=true),
     *             @OA\Property(property="prev_page", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function getAllCustomers(Request $request): JsonResponse
    {
        $nameCustomer = $request->query('customer');
        $active = $request->query('active');
        $limit = $request->query('limit', 10);

        $customers = Customer::select('id', 'email', 'active')
            ->when(
                $nameCustomer,
                fn($query) =>
                $query->where('email', 'like', "%{$nameCustomer}%")
            )
            ->when(
                isset($active),
                fn($query) =>
                $query->where('active', $active)
            )
            ->orderByDesc('id')
            ->paginate($limit);


        return response()->json([
            'data'         => $customers->items(),
            'current_page' => $customers->currentPage(),
            'total'        => $customers->total(),
            'last_page'    => $customers->lastPage(),
            'next_page'    => $customers->nextPageUrl(),
            'prev_page'    => $customers->previousPageUrl()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/customers/{id}",
     *     summary="Obtener un cliente por ID",
     *     description="Retorna la información de un cliente específico por su ID.",
     *     operationId="getCustomer",
     *     tags={"Customers"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del cliente",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Datos del cliente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="active", type="boolean")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Cliente no encontrado"
     *     )
     * )
     */
    public function getCustomer(int $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            throw new NotFoundCustomer;
        }
        return response()->json(['data' => $customer]);
    }

    /**
     * @OA\Post(
     *     path="/api/customers",
     *     summary="Crear un nuevo cliente",
     *     description="Crea un cliente nuevo con email y estado activo.",
     *     operationId="storeCustomer",
     *     tags={"Customers"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "active"},
     *             @OA\Property(property="email", type="string", format="email", example="cliente@example.com"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Cliente creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", format="email", example="cliente@example.com"),
     *                 @OA\Property(property="active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function storeCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'  => 'required|email|unique:customers,email',
            'active' => 'required|boolean'
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer created successfully',
            'data'    => $customer
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/customers/{id}",
     *     summary="Actualizar un cliente",
     *     description="Actualiza los datos de un cliente existente por su ID.",
     *     operationId="updateCustomer",
     *     tags={"Customers"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del cliente a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "active"},
     *             @OA\Property(property="email", type="string", format="email", example="cliente@nuevo.com"),
     *             @OA\Property(property="active", type="boolean", example=false)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cliente actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", example="cliente@nuevo.com"),
     *                 @OA\Property(property="active", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Cliente no encontrado"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundCustomer;
        }

        $validated = $request->validate([
            'email'  => 'required|email|unique:customers,email,' . $id,
            'active' => 'required|boolean'
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully',
            'data'    => $customer
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Eliminar un cliente",
     *     description="Elimina un cliente por su ID.",
     *     operationId="deleteCustomer",
     *     tags={"Customers"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del cliente a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cliente eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Cliente no encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            throw new NotFoundCustomer;
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
