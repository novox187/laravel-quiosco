<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRegistroRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function register(EmployeeRegistroRequest $request)
    {
        $data = $request->validated();

        $employee = Employee::create($data);

        return [
            'token' => $employee->createToken('token')->plainTextToken,
            'employee' => $employee,
        ];
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $employee = Employee::where('email', $data['email'])->first();

        if (!$employee || !Hash::check($data['password'], $employee->password)) {
            return response([
                'errors' => ['El email o la contraseña son incorrectos'],
            ], 422);
        }

        $token = $employee->createToken('token')->plainTextToken;

        return array_merge(
            $employee->toArray(),
            ['token' => $token]
        );
    }

    public function session(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ['user' => null];
    }
}
