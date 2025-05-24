<?php

Route::group(['prefix' => 'orders', 'middleware' => 'IsDriver', 'namespace' => 'WebService\Orders'], function () {
    Route::get('list', 'OrderController@index')->name('api.driver.orders.index');
    Route::get('list/{id}', 'OrderController@show')->name('api.driver.orders.show');
    Route::post('update-order-by-driver/{id}', 'OrderController@updateOrderByDriver')->name('api.driver.orders.update_order_by_driver');

    Route::group(['prefix' => 'status'], function () {
        Route::get('index', 'OrderStatusController@index')->name('api.driver.orders_statuses.index');
        Route::post('update/{id}', 'OrderController@updateOrderStatus')->name('api.driver.orders_statuses.update');
    });
});

