<?php

namespace App\Http\Controllers;

class HelloController extends Controller
{
    public function hello()
    {
        return response("Hello from Spring Boot + MongoDB!");
    }
}
