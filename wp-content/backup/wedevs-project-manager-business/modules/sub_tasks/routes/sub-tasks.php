<?php

use WeDevs\PM_Pro\Core\Router\Router;
use WeDevs\PM\Core\Permissions\Access_Project;
use WeDevs\PM\Core\Permissions\Create_Task;

$router = Router::singleton();

$router->get( 'tasks/{task_id}/sub-tasks', 'WeDevs\PM_Pro\Modules\sub_tasks\src\Controllers\Sub_Tasks_Controller@index' )
    ->permission( ['WeDevs\PM\Core\Permissions\Access_Project'] );

$router->get( 'tasks/{task_id}/sub-tasks/{sub_task_id}', 'WeDevs\PM_Pro\Modules\sub_tasks\src\Controllers\Sub_Tasks_Controller@show' )
    ->permission( ['WeDevs\PM\Core\Permissions\Access_Project'] );

$router->post( 'tasks/{task_id}/sub-tasks', 'WeDevs\PM_Pro\Modules\sub_tasks\src\Controllers\Sub_Tasks_Controller@store' )
    ->permission( ['WeDevs\PM_Pro\Modules\sub_tasks\Permissions\Create_Sub_Task'] )
    ->validator( 'WeDevs\PM_Pro\Modules\sub_tasks\src\Validators\Create_Sub_Task' );


$router->post( 'tasks/{task_id}/sub-tasks/{sub_task_id}/update', 'WeDevs\PM_Pro\Modules\sub_tasks\src\Controllers\Sub_Tasks_Controller@update' )
    ->permission( ['WeDevs\PM_Pro\Modules\sub_tasks\Permissions\Edit_Sub_Task'] );

$router->post( 'tasks/{task_id}/sub-tasks/{sub_task_id}/delete', 'WeDevs\PM_Pro\Modules\sub_tasks\src\Controllers\Sub_Tasks_Controller@destroy' )
    ->permission( ['WeDevs\PM_Pro\Modules\sub_tasks\Permissions\Edit_Sub_Task'] );

$router->post( 'tasks/{task_id}/sub-tasks/{sub_task_id}/make-task', 'WeDevs\PM_Pro\Modules\sub_tasks\src\Controllers\Sub_Tasks_Controller@subtask_to_task' )
    ->permission( ['WeDevs\PM_Pro\Modules\sub_tasks\Permissions\Edit_Sub_Task'] );


$router->post( 'sub-tasks/sorting', 'WeDevs\PM_Pro\Modules\sub_tasks\src\Controllers\Sub_Tasks_Controller@sorting' )
    ->permission( ['WeDevs\PM\Core\Permissions\Create_Task'] );

