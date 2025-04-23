<?php


namespace App\Http\Controllers\AuthUsers;

use App\Exceptions\Auth\CredentialInvalid;
use App\Exceptions\Auth\ExpiredCode;
use App\Exceptions\User\NotFoundUser;
use App\Http\Controllers\Controller; // Agrega esta línea
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(name="Authentication", 
 * description="Endpoints de autenticación de usuarios")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registra un nuevo usuario",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "role", "password"},
     *             @OA\Property(property="username", type="string", example="usuario123"),
     *             @OA\Property(property="email", type="string", example="usuario@example.com"),
     *             @OA\Property(property="role", type="string", enum={"admin", "user"}, example="user"),
     *             @OA\Property(property="password", type="string", format="password", example="securePassword123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario registrado correctamente"),
     *     @OA\Response(response=422, description="Error en validación de datos")
     * )
     */
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:5|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'role' => 'required|string|in:admin,user',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        User::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'role' => $request->get('role'),
            'password' => bcrypt($request->get('password'))
        ]);

        return response()->json(['message' => 'User created successfully'], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Inicia sesión y obtiene un token JWT",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="usuario123"),
     *             @OA\Property(property="password", type="string", format="password", example="securePassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Autenticación exitosa, devuelve el token JWT"),
     *     @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:5|max:50',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only(['username', 'password']);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            return response()->json([
                'token' => $token,
                'expires_in' => JWTAuth::factory()->getTTL()
            ], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token', $e], 500);
        }
    }

/**
 * Refresca el token JWT expirado utilizando el token actual enviado en el encabezado Authorization.
 *
 * @OA\Post(
 *     path="/api/refresh-token",
 *     summary="Refrescar token JWT",
 *     description="Genera un nuevo token JWT usando el token actual (incluso si ha expirado, siempre que esté dentro del tiempo de refresh permitido).",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Token renovado exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJh..."),
 *             @OA\Property(property="expires_in", type="integer", example=60)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Token no válido o ya expirado",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Token no válido o ya expirado")
 *         )
 *     )
 * )
 *
 * @return JsonResponse JSON con el nuevo token y su tiempo de expiración
 */

    public function refreshToken(): JsonResponse
    {
        $newToken = JWTAuth::parseToken()->refresh();
        return new JsonResponse([
            'token' => $newToken,
            'expires_in' => JWTAuth::factory()->getTTL()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Obtiene los datos del usuario autenticado",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Datos del usuario autenticado"),
     *     @OA\Response(response=401, description="Token inválido o expirado")
     * )
     */
    public function getUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            return response()->json($user, 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error'], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cierra sesión e invalida el token JWT",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Cierre de sesión exitoso")
     * )
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/send-code-email",
     *     summary="Envía un código de verificación para el cambio de contraseña",
     *     description="Genera un código de verificación que se enviará al correo electrónico del usuario para realizar el cambio de contraseña.",
     *     operationId="sendEmailPasswordChange",
     *     tags={"ChangePassword"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Los datos necesarios para el cambio de contraseña",
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@ejemplo.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Código de verificación enviado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Se envio el codigo de verificación revise su correo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El campo email es obligatorio y debe ser una dirección de correo válida")
     *         )
     *     )
     * )
     */
    public function sednEmailPasswordChange(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|string']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            throw new NotFoundUser;
        }
        $code = rand(100000, 999999);
        $user->password_reset_code = $code;
        $user->code_expired = Carbon::now()->addMinutes(10);
        $user->save();
        Mail::to($user->email)->send(new VerificationCodeMail($code));
        return new JsonResponse(['data' => 'Se envio el codigo de verificación revise su correo']);
    }

    /**
     * @OA\Post(
     *     path="/api/change-password",
     *     summary="Cambia la contraseña del usuario utilizando un código de verificación",
     *     description="Permite al usuario cambiar su contraseña mediante un código de verificación enviado por correo electrónico.",
     *     operationId="changePassword",
     *     tags={"ChangePassword"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para cambiar la contraseña",
     *         @OA\JsonContent(
     *             required={"email", "code", "password_new"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@ejemplo.com"),
     *             @OA\Property(property="code", type="string", example="123456"),
     *             @OA\Property(property="password_new", type="string", example="NuevaContraseña123", description="Contraseña nueva con al menos 8 caracteres"),
     *             @OA\Property(property="password_new_confirmation", type="string", example="NuevaContraseña123", description="Confirmar contraseña nueva con al menos 8 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña cambiada con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="string", example="Su contraseña se cambio")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="La contraseña debe tener al menos 8 caracteres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado o código de verificación incorrecto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciales inválidas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=410,
     *         description="Código de verificación expirado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El código de verificación ha expirado")
     *         )
     *     )
     * )
     */

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|string',
            'code' => 'required|string|min:1|max:6',
            'password_new' => 'required|min:8|confirmed',
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->password_reset_code !== $request->code) {
            throw new CredentialInvalid;
        }
        if ($user->code_expires_at && Carbon::now()->gt($user->code_expires_at)) {
            throw new ExpiredCode;
        }
        $user->update([
            'password' => Hash::make($request->password_new),
            'password_reset_code' => null,
            'code_expired' => null
        ]);
        return new JsonResponse(['data' => 'Su contraseña se cambio']);
    }
}
