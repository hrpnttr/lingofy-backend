<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// Root Hello check
$router->get('/', 'HelloController@hello');

$router->group(['prefix' => 'api'], function () use ($router) {

    // Users CRUD
    $router->post('users', 'UserController@create');
    $router->get('users', 'UserController@all');
    $router->get('users/by-email', 'UserController@byEmail');
    $router->get('users/{id}', 'UserController@byId');
    $router->patch('users/{id}', 'UserController@update');
    $router->delete('users/{id}', 'UserController@delete');

    // Classes
    $router->get('classes', 'ClassesController@list');
    $router->get('classes/{id}', 'ClassesController@get');

    // Courses
    $router->post('courses', 'CourseController@create');
    $router->get('courses', 'CourseController@list');
    $router->get('courses/{id}', 'CourseController@get');
    $router->patch('courses/{id}', 'CourseController@update');
    $router->delete('courses/{id}', 'CourseController@delete');

    // Quizzes
    $router->get('quizzes', 'QuizzesController@list');
    $router->get('quizzes/by-id', 'QuizzesController@byId');
    $router->get('quizzes/{id}', 'QuizzesController@get');

    // Quiz Results (Add Quizzes)
    $router->get('add-quizzes', 'QuizResultController@list');
    $router->get('add-quizzes/{id}', 'QuizResultController@get');
    $router->post('add-quizzes', 'QuizResultController@create');

    // Student Courses (Scourses)
    $router->post('scourses', 'ScoursesController@create');
    $router->get('scourses', 'ScoursesController@all');
    $router->get('scourses/{studentId}', 'ScoursesController@byStudentId');

    // Students & Auth & Password Reset
    $router->post('students', 'StudentsController@create');
    $router->get('students', 'StudentsController@all');
    $router->get('students/by-email', 'StudentsController@byEmail'); // note: POST /by-email was in Java, let's allow both POST and GET to be safe!
    $router->post('students/by-email', 'StudentsController@byEmail');
    $router->get('students/me', 'StudentsAuthController@me');
    $router->get('students/{id}', 'StudentsController@byId');
    $router->post('students/login', 'StudentsAuthController@login');
    $router->post('students/forgot-password', 'PasswordResetController@forgotPassword');
    $router->post('students/reset-password', 'PasswordResetController@resetPassword');

});
